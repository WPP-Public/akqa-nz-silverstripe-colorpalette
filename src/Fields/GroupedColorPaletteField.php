<?php

namespace Heyday\ColorPalette\Fields;


use InvalidArgumentException;
use SilverStripe\Forms\GroupedDropdownField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;


/**
 * Class GroupedColorPaletteField
 */
class GroupedColorPaletteField extends GroupedDropdownField
{
    /**
     * @param array $properties
     * @throws InvalidArgumentException
     * @return HTMLText
     */
    public function Field($properties = [])
    {
        Requirements::css('heyday/silverstripe-colorpalette:css/ColorPaletteField.css');

        $source = $this->getSource();

        $odd = 0;
        $fieldExtraClass = $this->extraClass();
        $groups = [];

        if ($source) {
            foreach ($source as $name => $values) {
                if (is_array($values)) {
                    $options = [];

                    foreach ($values as $value => $color) {
                        $itemID = $this->ID() . '_' . preg_replace('/[^a-zA-Z0-9]/', '', $value);
                        $odd = ($odd + 1) % 2;
                        $extraClass = $odd ? 'odd' : 'even';
                        $extraClass .= ' val' . preg_replace('/[^a-zA-Z0-9\-\_]/', '_', $value);

                        $options[] = new ArrayData([
                            'ID' => $itemID,
                            'Class' => $extraClass,
                            'Name' => $this->name,
                            'Value' => $value,
                            'Title' => $color,
                            'isChecked' => $value == $this->value,
                            'isDisabled' => $this->disabled || in_array($value, $this->disabledItems),
                        ]);
                    }

                    $groups[] = new ArrayData(
                        [
                            'ID' => $this->ID() . '_' . preg_replace('/[^a-zA-Z0-9]/', '', $name),
                            'extraClass' => $fieldExtraClass,
                            'Name' => $name,
                            'Options' => new ArrayList($options)
                        ]
                    );

                } else {
                    throw new InvalidArgumentException('To use GroupedColorPaletteField you need to pass in an array of array\'s');
                }
            }
        }

        $properties = array_merge(
            $properties,
            [
                'Groups' => new ArrayList($groups)
            ]
        );

        return $this->customise($properties)->renderWith(
            $this->getTemplates()
        );
    }

    /**
     * @return string
     */
    public function Type()
    {
        return 'groupedcolorpalette colorpalette';
    }

    /**
     * Gets a readonly version of the field
     * @return GroupedColorPaletteField_Readonly
     */
    public function performReadonlyTransformation()
    {
        // Source and values are DataObject sets.
        $field = $this->castedCopy(GroupedColorPaletteField_Readonly::class);
        $field->setSource($this->getSource());
        $field->setReadonly(true);

        return $field;
    }
}
