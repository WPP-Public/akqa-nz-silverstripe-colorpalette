<?php

namespace Heyday\ColorPalette\Tests\Fields;

use Heyday\ColorPalette\Fields\ColorPaletteField;
use SilverStripe\Dev\SapphireTest;

class ColorPaletteFieldTest extends SapphireTest
{
    use SingleLineStringTrait;

    public function testField(): void
    {
        $field = ColorPaletteField::create(
            'BackgroundColor',
            'Background Color',
            [
                'White' => '#fff',
                'Black' => '#000'
            ]
        );

        $this->assertSame(
            '<ul class="colorpalette" id="BackgroundColor" role="listbox"> <li class="odd valWhite"> <input id="BackgroundColor_White" class="radio" name="BackgroundColor" type="radio" value="White" /> <label for="BackgroundColor_White" style="background: #fff"></label> </li> <li class="even valBlack"> <input id="BackgroundColor_Black" class="radio" name="BackgroundColor" type="radio" value="Black" /> <label for="BackgroundColor_Black" style="background: #000"></label> </li> </ul>',
            $this->convertToSingleLine($field->forTemplate())
        );
    }

    public function testReadOnlyField(): void
    {
        $field = ColorPaletteField::create(
            'BackgroundColor',
            'Background Color',
            [
                'White' => '#fff',
                'Black' => '#000'
            ]
        )->performReadonlyTransformation();

        $this->assertSame(
            '<ul name="BackgroundColor" class="lookup readonly " id="BackgroundColor" readonly="readonly"> <li> <input name="BackgroundColor" type="hidden" value="" /> <label for="BackgroundColor" style="background: <i>(none)</i>"></label> </li></ul>',
            $this->convertToSingleLine($field->forTemplate())
        );
    }
}

