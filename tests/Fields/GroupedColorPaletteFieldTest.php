<?php

namespace Heyday\ColorPalette\Tests\Fields;

use Heyday\ColorPalette\Fields\GroupedColorPaletteField;
use SilverStripe\Dev\SapphireTest;

class GroupedColorPaletteFieldTest extends SapphireTest
{
    use SingleLineStringTrait;

    public function testField(): void
    {
        $field = GroupedColorPaletteField::create(
            'BackgroundColor',
            'Background Color',
            [
                'Primary Palette' => [
                    'White' => '#fff',
                    'Black' => '#000'
                ],
                'Secondary Palette' => [
                    'Blue' => 'blue',
                    'Red' => 'red'
                ]
            ]
        );

        $this->assertSame(
            ' <h4>Primary Palette</h4> <ul > <li class="odd valWhite"> <input id="BackgroundColor_White" class="radio" name="BackgroundColor" type="radio" value="White" /> <label for="BackgroundColor_White" style="background: #fff"></label> </li> <li class="even valBlack"> <input id="BackgroundColor_Black" class="radio" name="BackgroundColor" type="radio" value="Black" /> <label for="BackgroundColor_Black" style="background: #000"></label> </li> </ul> <h4>Secondary Palette</h4> <ul > <li class="odd valBlue"> <input id="BackgroundColor_Blue" class="radio" name="BackgroundColor" type="radio" value="Blue" /> <label for="BackgroundColor_Blue" style="background: blue"></label> </li> <li class="even valRed"> <input id="BackgroundColor_Red" class="radio" name="BackgroundColor" type="radio" value="Red" /> <label for="BackgroundColor_Red" style="background: red"></label> </li> </ul>',
            $this->convertToSingleLine($field->forTemplate())
        );
    }

    public function testReadOnlyField(): void
    {
        $field = GroupedColorPaletteField::create(
            'BackgroundColor',
            'Background Color',
            [
                'Primary Palette' => [
                    'White' => '#fff',
                    'Black' => '#000'
                ],
                'Secondary Palette' => [
                    'Blue' => 'blue',
                    'Red' => 'red'
                ]
            ]
        )->setValue('Blue')->performReadonlyTransformation();

        $this->assertSame(
            '<h4>Secondary Palette</h4><ul name="BackgroundColor" class="lookup readonly " id="BackgroundColor" readonly="readonly"> <li class=""> <input name="BackgroundColor" type="hidden" value="Blue" /> <label for="BackgroundColor" style="background: blue"></label> </li></ul>',
            $this->convertToSingleLine($field->forTemplate())
        );
    }
}

