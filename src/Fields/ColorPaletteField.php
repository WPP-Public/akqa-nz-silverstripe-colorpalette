<?php

namespace Heyday\ColorPalette\Fields;

use SilverStripe\Forms\FormField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Model\ArrayData;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\View\Requirements;

/**
 * Color palette field with an optional free-form Iris colour picker.
 *
 * By default this behaves as a fixed palette (radio swatches). Call
 * {@link setAllowPicker(true)} to also show a text input backed by Iris, so
 * editors can pick any hex colour in addition to the palette.
 *
 * When the picker is enabled, prefer hex strings as both the source keys and
 * values (e.g. `'#FFFFFF' => '#FFFFFF'`) so the stored value is always a colour.
 */
class ColorPaletteField extends OptionsetField
{
    /**
     * Optional default palette for projects that configure colours in YAML.
     *
     * @var array<string, string>
     */
    private static array $default_picker_colors = [];

    /**
     * When true, show a free-form Iris colour picker alongside the palette.
     */
    protected bool $allowPicker = false;

    /**
     * Hex colours passed to Iris as swatches. Null derives them from the source.
     *
     * @var array<int, string>|null
     */
    protected ?array $pickerColors = null;

    /**
     * Enable or disable the free-form colour picker.
     */
    public function setAllowPicker(bool $allow): static
    {
        $this->allowPicker = $allow;

        return $this;
    }

    public function getAllowPicker(): bool
    {
        return $this->allowPicker;
    }

    /**
     * Override the Iris swatch list (defaults to the palette source colours).
     *
     * @param array<int, string> $colors
     */
    public function setPickerColors(array $colors): static
    {
        $this->pickerColors = array_values($colors);

        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function getPickerColors(): array
    {
        if ($this->pickerColors !== null) {
            return $this->pickerColors;
        }

        return array_values(array_map('strval', $this->getSource() ?: []));
    }

    /**
     * Create a palette field with the free-form picker enabled, using
     * `default_picker_colors` from config when no source is provided.
     *
     * @param array<string, string>|null $source
     */
    public static function createWithPicker(
        string $name,
        ?string $title = null,
        ?array $source = null,
        mixed $value = null
    ): static {
        if ($source === null) {
            $source = static::config()->get('default_picker_colors') ?: [];
        }

        return static::create($name, $title, $source, $value)->setAllowPicker(true);
    }

    /**
     * @param array $properties
     * @return \SilverStripe\ORM\FieldType\DBHTMLText
     */
    public function Field($properties = [])
    {
        Requirements::css('heyday/silverstripe-colorpalette:css/ColorPaletteField.css');

        if ($this->getAllowPicker()) {
            Requirements::javascript('heyday/silverstripe-colorpalette:js/lib/color-picker.min.js');
            Requirements::css('heyday/silverstripe-colorpalette:css/ColorPaletteField.css');
            $this->addExtraClass('colorpalette--allow-picker');

            $pickerValue = $this->getPickerDisplayValue();
            $properties = array_merge($properties, [
                'AllowPicker' => true,
                'PickerValue' => $pickerValue,
                'PickerColorsJSON' => json_encode($this->getPickerColors()),
                'PickerTextColor' => $this->getContrastingTextColor($pickerValue),
                'Options' => $this->getPickerOptions(),
            ]);

            return FormField::Field($properties);
        }

        return parent::Field($properties);
    }

    /**
     * When the picker is enabled, React Elemental forms use a TextField so the
     * free-form value can be edited; palette swatches are injected by JS.
     */
    public function getSchemaComponent()
    {
        if ($this->getAllowPicker()) {
            return 'TextField';
        }

        return parent::getSchemaComponent();
    }

    public function getSchemaDataDefaults()
    {
        $data = parent::getSchemaDataDefaults();

        if ($this->getAllowPicker()) {
            $pickerValue = $this->getPickerDisplayValue();
            $data['component'] = 'TextField';
            $data['schemaType'] = FormField::SCHEMA_DATA_TYPE_TEXT;
            $data['type'] = 'text';
            $data['extraClass'] = trim(
                $data['extraClass'] . ' text colorpalette colorpalette--allow-picker js-color-picker colorpalette__picker-input'
            );
            $data['data']['allowPicker'] = true;
            $data['data']['palette'] = $this->getPickerColors();
            $data['attributes']['data-palette'] = json_encode($this->getPickerColors());
            $data['attributes']['style'] = sprintf(
                'background-color: %s; color: %s;',
                $pickerValue,
                $this->getContrastingTextColor($pickerValue)
            );
            // TextField schema expects a string value, not a singleselect default.
            unset($data['data']['hasEmptyDefault'], $data['data']['emptyString']);
        } else {
            $data['data']['allowPicker'] = false;
        }

        return $data;
    }

    public function getSchemaStateDefaults()
    {
        $data = parent::getSchemaStateDefaults();

        if ($this->getAllowPicker()) {
            $data['value'] = $this->getPickerDisplayValue();
        }

        return $data;
    }

    /**
     * Allow values outside the palette when the free-form picker is enabled.
     */
    public function getValidValues()
    {
        $values = parent::getValidValues();

        if (!$this->getAllowPicker()) {
            return $values;
        }

        $current = $this->getValueForValidation();
        if ($current !== null && $current !== '' && !in_array($current, $values, true)) {
            $values[] = $current;
        }

        // Also accept any source colour (title) in case keys differ from colours.
        foreach ($this->getSource() ?: [] as $color) {
            $color = (string) $color;
            if ($color !== '' && !in_array($color, $values, true)) {
                $values[] = $color;
            }
        }

        return $values;
    }

    /**
     * Gets a readonly version of the field
     * @return ColorPaletteField_Readonly
     */
    public function performReadonlyTransformation()
    {
        // Source and values are DataObject sets.
        $field = $this->castedCopy(ColorPaletteField_Readonly::class);
        $field->setSource($this->getSource());
        $field->setReadonly(true);

        return $field;
    }

    /**
     * Colour shown in the free-form input (resolves palette keys to colours).
     */
    public function getPickerDisplayValue(): string
    {
        $value = (string) $this->getValue();
        if ($value === '') {
            return '#ffffff';
        }

        $source = $this->getSource() ?: [];
        if (array_key_exists($value, $source)) {
            return (string) $source[$value];
        }

        return $value;
    }

    /**
     * Build swatch options for picker mode (buttons, not radios).
     */
    protected function getPickerOptions(): ArrayList
    {
        $options = ArrayList::create();
        $odd = false;
        $current = (string) $this->getValue();
        $display = $this->getPickerDisplayValue();

        foreach ($this->getSourceEmpty() as $value => $title) {
            $odd = !$odd;
            $title = (string) $title;
            $isChecked = $this->isSelectedValue($value, $current)
                || strcasecmp($title, $current) === 0
                || strcasecmp($title, $display) === 0;

            $options->push(new ArrayData([
                'ID' => $this->getOptionID($value),
                'Class' => $this->getOptionClass($value, $odd),
                'Name' => $this->getOptionName(),
                'Value' => $value,
                'Title' => $title,
                'isChecked' => $isChecked,
                'isDisabled' => $this->isDisabledValue($value),
            ]));
        }

        return $options;
    }

    /**
     * Pick black or white text so the hex stays readable on its own background.
     */
    protected function getContrastingTextColor(string $color): string
    {
        $hex = ltrim($color, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return '#000000';
        }

        $c = intval($hex, 16);
        $r = $c >> 16;
        $g = ($c >> 8) & 0xff;
        $b = $c & 0xff;
        $mid = ($r + $g + $b) / 3;

        return ($mid > 127) ? '#000000' : '#ffffff';
    }
}
