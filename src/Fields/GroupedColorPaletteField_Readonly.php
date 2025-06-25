<?php

namespace Heyday\ColorPalette\Fields;


use InvalidArgumentException;
use SilverStripe\Forms\LookupField;
use SilverStripe\View\Requirements;


class GroupedColorPaletteField_Readonly extends LookupField
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
     * Gets the label of the group that the color appears in
     * @return string
     */
    public function getSelectedGroup()
    {
        $source = $this->getSource();

        if ($source) {
            foreach ($source as $groupName => $values) {
                if (is_array($values)) {
                    if (array_key_exists($this->value, $values)) {
                        return $groupName;
                    }
                } else {
                    throw new InvalidArgumentException('To use GroupedColorPaletteField you need to pass in an array of array\'s');
                }
            }
        }
    }
}
