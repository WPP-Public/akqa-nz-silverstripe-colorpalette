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

    public function testAllowPickerRendersTextInput(): void
    {
        $field = ColorPaletteField::create(
            'BackgroundColour',
            'Background Colour',
            [
                '#FFFFFF' => '#FFFFFF',
                '#000000' => '#000000',
            ],
            '#1976D2'
        )->setAllowPicker(true);

        $html = $this->convertToSingleLine($field->forTemplate());

        $this->assertStringContainsString('colorpalette--allow-picker', $html);
        $this->assertStringContainsString('js-color-picker', $html);
        $this->assertStringContainsString('name="BackgroundColour"', $html);
        $this->assertStringContainsString('value="#1976D2"', $html);
        $this->assertStringContainsString('colorpalette__swatch', $html);
        $this->assertTrue($field->getAllowPicker());
        $this->assertSame('TextField', $field->getSchemaComponent());
    }

    public function testAllowPickerAcceptsCustomValue(): void
    {
        $field = ColorPaletteField::create(
            'BackgroundColour',
            'Background Colour',
            [
                '#FFFFFF' => '#FFFFFF',
                '#000000' => '#000000',
            ],
            '#1976D2'
        )->setAllowPicker(true);

        $this->assertContains('#1976D2', $field->getValidValues());
        $this->assertTrue($field->validate()->isValid());
    }
}

