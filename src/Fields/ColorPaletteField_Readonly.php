<?php

namespace Heyday\ColorPalette\Fields;


use SilverStripe\Forms\LookupField;
use SilverStripe\View\Requirements;


class ColorPaletteField_Readonly extends LookupField
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
}
