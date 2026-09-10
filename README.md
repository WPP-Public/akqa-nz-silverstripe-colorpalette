# SilverStripe Color Palette Field

Provides a colour field for SilverStripe CMS that lets editors choose from a defined palette of colours. Optionally, a free-form [Iris](https://github.com/Automattic/Iris) picker can be enabled so any hex colour can be chosen in addition to the palette.

Works in standard CMS forms and with [Elemental](https://github.com/silverstripe/silverstripe-elemental) elements (including React-rendered / inline-editable fields for palette-only mode, and non-inline Element edit forms when the picker is enabled).

## Installation (with composer)

```bash
composer require heyday/silverstripe-colorpalette
```

## Example

![Color Palette Example](resources/example.png?raw=true)

## Features

* Fixed colour palette (radio swatches)
* Optional free-form Iris colour picker via `setAllowPicker(true)`
* Grouped palettes
* Elemental-compatible (see notes below)

## Usage

### Regular palette

Stores the **array key** as the field value (e.g. `White` / `Black`). Use this when the value is a semantic name (CSS class, theme key, etc.).

```php
use Heyday\ColorPalette\Fields\ColorPaletteField;

$fields->addFieldToTab(
    'Root.Main',
    ColorPaletteField::create(
        'BackgroundColor',
        'Background Color',
        [
            'White' => '#fff',
            'Black' => '#000',
        ]
    )
);
```

### Palette with free-form picker

Call `setAllowPicker(true)` to show the Iris picker beside the swatches. Editors can click a swatch **or** pick any hex colour.

When the picker is enabled, use **hex strings as both keys and values** so the stored DB value is always a colour:

```php
use Heyday\ColorPalette\Fields\ColorPaletteField;

$fields->addFieldToTab(
    'Root.Main',
    ColorPaletteField::create(
        'BackgroundColour',
        'Background colour',
        [
            '#FFFFFF' => '#FFFFFF',
            '#000000' => '#000000',
            '#1976D2' => '#1976D2',
        ]
    )->setAllowPicker(true)
);
```

Or configure a project-wide default palette and use the helper:

```yaml
# app/_config/colourpicker.yml
Heyday\ColorPalette\Fields\ColorPaletteField:
  default_picker_colors:
    '#FFFFFF': '#FFFFFF'
    '#000000': '#000000'
    '#1976D2': '#1976D2'
```

```php
ColorPaletteField::createWithPicker('BackgroundColour', 'Background colour');
```

You can override the Iris swatch list (defaults to the palette source colours):

```php
$field->setPickerColors([
    '#000000',
    '#1976D2',
    '#FFFFFF',
]);
```

### Grouped palette

```php
use Heyday\ColorPalette\Fields\GroupedColorPaletteField;

$fields->addFieldToTab(
    'Root.Main',
    GroupedColorPaletteField::create(
        'BackgroundColor',
        'Background Color',
        [
            'Primary Palette' => [
                'White' => '#fff',
                'Black' => '#000',
            ],
            'Secondary Palette' => [
                'Blue' => 'blue',
                'Red' => 'red',
            ],
        ]
    )
);
```

### Elemental elements

```php
use DNADesign\Elemental\Models\BaseElement;
use Heyday\ColorPalette\Fields\ColorPaletteField;

class ElementHero extends BaseElement
{
    private static array $db = [
        'BackgroundColour' => 'Varchar(7)',
    ];

    // Prefer false when using setAllowPicker(true) so the PHP field template
    // (palette + Iris) is used in the element edit form.
    private static bool $inline_editable = false;

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->replaceField(
            'BackgroundColour',
            ColorPaletteField::create(
                'BackgroundColour',
                'Background colour',
                [
                    '#FFFFFF' => '#FFFFFF',
                    '#303E5C' => '#303E5C',
                ]
            )->setAllowPicker(true)
        );

        return $fields;
    }
}
```

Notes:

* **Palette only** (`setAllowPicker` left false) works with inline-editable Elemental fields via the React `OptionsetField` UI.
* **With picker** (`setAllowPicker(true)`), use `inline_editable = false` (or edit via the full element form) so the PHP template and Iris picker are available. React inline forms fall back to a text input enhanced with swatches/Iris via the module JS.

## License

SilverStripe Color Palette Field is licensed under an [MIT license](http://heyday.mit-license.org/)
