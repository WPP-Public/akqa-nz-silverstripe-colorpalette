<?php

namespace Heyday\ColorPalette\Fields;

use SilverStripe\Forms\OptionsetField;
use SilverStripe\View\Requirements;

/**
 * Class ColorPaletteField
 */
class ColorPaletteField extends OptionsetField
{
    /**
     * @param array $properties
     * @return HTMLText
     */
    public function Field($properties = [])
    {
        Requirements::css('heyday/silverstripe-colorpalette:css/ColorPaletteField.css');

        return parent::Field($properties);
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
}

