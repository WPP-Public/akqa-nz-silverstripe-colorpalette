<?php

namespace Heyday\ColorPalette\Tests\Fields;

use Heyday\ColorPalette\Fields\ColorPaletteField;
use Heyday\ColorPalette\Fields\ColorPaletteField_Readonly;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FormField;

class ColorPaletteFieldTest extends SapphireTest
{
    use SingleLineStringTrait;

    /**
     * @return array<string, string>
     */
    private function namedSource(): array
    {
        return [
            'White' => '#fff',
            'Black' => '#000',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function hexSource(): array
    {
        return [
            '#FFFFFF' => '#FFFFFF',
            '#000000' => '#000000',
            '#1976D2' => '#1976D2',
        ];
    }

    public function testFieldRendersPaletteRadios(): void
    {
        $field = ColorPaletteField::create(
            'BackgroundColor',
            'Background Color',
            $this->namedSource()
        );

        $html = $this->convertToSingleLine($field->forTemplate());

        $this->assertStringContainsString('class="colorpalette"', $html);
        $this->assertStringContainsString('role="listbox"', $html);
        $this->assertStringContainsString('type="radio"', $html);
        $this->assertStringContainsString('name="BackgroundColor"', $html);
        $this->assertStringContainsString('value="White"', $html);
        $this->assertStringContainsString('value="Black"', $html);
        $this->assertStringContainsString('style="background: #fff"', $html);
        $this->assertStringContainsString('style="background: #000"', $html);
        $this->assertStringNotContainsString('colorpalette__swatch', $html);
        $this->assertStringNotContainsString('js-color-picker', $html);
    }

    public function testSelectedValueIsChecked(): void
    {
        $field = ColorPaletteField::create(
            'BackgroundColor',
            'Background Color',
            $this->namedSource(),
            'Black'
        );

        $html = $this->convertToSingleLine($field->forTemplate());

        $this->assertMatchesRegularExpression(
            '/id="BackgroundColor_Black"[^>]*checked/',
            $html
        );
        $this->assertDoesNotMatchRegularExpression(
            '/id="BackgroundColor_White"[^>]*checked/',
            $html
        );
    }

    public function testReadOnlyField(): void
    {
        $field = ColorPaletteField::create(
            'BackgroundColor',
            'Background Color',
            $this->namedSource()
        )->performReadonlyTransformation();

        $this->assertInstanceOf(ColorPaletteField_Readonly::class, $field);
        $this->assertTrue($field->isReadonly());

        $html = $this->convertToSingleLine($field->forTemplate());
        $this->assertStringContainsString('readonly', $html);
        $this->assertStringContainsString('type="hidden"', $html);
    }

    public function testAllowPickerDefaultsToFalse(): void
    {
        $field = ColorPaletteField::create('Colour', 'Colour', $this->hexSource());

        $this->assertFalse($field->getAllowPicker());
        $this->assertSame('OptionsetField', $field->getSchemaComponent());
    }

    public function testSetAllowPickerIsFluent(): void
    {
        $field = ColorPaletteField::create('Colour', 'Colour', $this->hexSource());

        $this->assertSame($field, $field->setAllowPicker(true));
        $this->assertTrue($field->getAllowPicker());

        $field->setAllowPicker(false);
        $this->assertFalse($field->getAllowPicker());
    }

    public function testAllowPickerRendersSwatchesAndTextInput(): void
    {
        $field = ColorPaletteField::create(
            'BackgroundColour',
            'Background Colour',
            $this->hexSource(),
            '#1976D2'
        )->setAllowPicker(true);

        $html = $this->convertToSingleLine($field->forTemplate());

        $this->assertStringContainsString('colorpalette--allow-picker', $html);
        $this->assertStringContainsString('js-color-picker', $html);
        $this->assertStringContainsString('colorpalette__picker-input', $html);
        $this->assertStringContainsString('colorpalette__swatch', $html);
        $this->assertStringContainsString('name="BackgroundColour"', $html);
        $this->assertStringContainsString('value="#1976D2"', $html);
        $this->assertStringContainsString('data-color="#1976D2"', $html);
        $this->assertStringContainsString('type="button"', $html);
        $this->assertStringNotContainsString('type="radio"', $html);
        $this->assertTrue($field->getAllowPicker());
        $this->assertSame('TextField', $field->getSchemaComponent());
    }

    public function testAllowPickerMarksMatchingSwatchSelected(): void
    {
        $field = ColorPaletteField::create(
            'BackgroundColour',
            'Background Colour',
            $this->hexSource(),
            '#000000'
        )->setAllowPicker(true);

        $html = $this->convertToSingleLine($field->forTemplate());

        $this->assertMatchesRegularExpression(
            '/class="[^"]*is-selected[^"]*"[^>]*data-color="#000000"|data-color="#000000"[^>]*class="[^"]*is-selected/',
            $html
        );
    }

    public function testAllowPickerAcceptsCustomValueOutsidePalette(): void
    {
        $field = ColorPaletteField::create(
            'BackgroundColour',
            'Background Colour',
            [
                '#FFFFFF' => '#FFFFFF',
                '#000000' => '#000000',
            ],
            '#FF4081'
        )->setAllowPicker(true);

        $this->assertContains('#FF4081', $field->getValidValues());
        $this->assertTrue($field->validate()->isValid());
        $this->assertSame('#FF4081', $field->getPickerDisplayValue());
    }

    public function testWithoutPickerCustomValueIsNotValid(): void
    {
        $field = ColorPaletteField::create(
            'BackgroundColour',
            'Background Colour',
            [
                '#FFFFFF' => '#FFFFFF',
                '#000000' => '#000000',
            ],
            '#FF4081'
        );

        $this->assertNotContains('#FF4081', $field->getValidValues());
        $this->assertFalse($field->validate()->isValid());
    }

    public function testPickerColorsDefaultToSourceValues(): void
    {
        $field = ColorPaletteField::create('Colour', 'Colour', $this->hexSource())
            ->setAllowPicker(true);

        $this->assertSame(
            ['#FFFFFF', '#000000', '#1976D2'],
            $field->getPickerColors()
        );
    }

    public function testSetPickerColorsOverridesIrisSwatches(): void
    {
        $field = ColorPaletteField::create('Colour', 'Colour', $this->hexSource())
            ->setAllowPicker(true)
            ->setPickerColors(['#111111', '#222222']);

        $this->assertSame(['#111111', '#222222'], $field->getPickerColors());

        $html = $this->convertToSingleLine($field->forTemplate());
        $this->assertTrue(
            str_contains($html, '#111111') && str_contains($html, '#222222'),
            'Picker markup should include custom Iris swatch colours'
        );
    }

    public function testCreateWithPickerEnablesPickerAndUsesConfigDefaults(): void
    {
        ColorPaletteField::config()->set('default_picker_colors', [
            '#ABCDEF' => '#ABCDEF',
            '#123456' => '#123456',
        ]);

        $field = ColorPaletteField::createWithPicker('Accent', 'Accent');

        $this->assertTrue($field->getAllowPicker());
        $this->assertSame(
            [
                '#ABCDEF' => '#ABCDEF',
                '#123456' => '#123456',
            ],
            $field->getSource()
        );
        $this->assertSame('TextField', $field->getSchemaComponent());
    }

    public function testCreateWithPickerAcceptsExplicitSource(): void
    {
        $source = $this->hexSource();
        $field = ColorPaletteField::createWithPicker('Accent', 'Accent', $source, '#1976D2');

        $this->assertTrue($field->getAllowPicker());
        $this->assertSame($source, $field->getSource());
        $this->assertSame('#1976D2', $field->getValue());
    }

    public function testGetPickerDisplayValueResolvesNamedKeys(): void
    {
        $field = ColorPaletteField::create(
            'Colour',
            'Colour',
            $this->namedSource(),
            'White'
        )->setAllowPicker(true);

        $this->assertSame('#fff', $field->getPickerDisplayValue());
    }

    public function testGetPickerDisplayValueFallsBackForEmptyValue(): void
    {
        $field = ColorPaletteField::create(
            'Colour',
            'Colour',
            $this->hexSource(),
            ''
        )->setAllowPicker(true);

        $this->assertSame('#ffffff', $field->getPickerDisplayValue());
    }

    public function testSchemaDataIncludesPickerMetadata(): void
    {
        $field = ColorPaletteField::create(
            'BackgroundColour',
            'Background Colour',
            $this->hexSource(),
            '#1976D2'
        )->setAllowPicker(true);

        $schema = $field->getSchemaData();

        $this->assertSame('TextField', $schema['component']);
        $this->assertSame(FormField::SCHEMA_DATA_TYPE_TEXT, $schema['schemaType']);
        $this->assertTrue($schema['data']['allowPicker']);
        $this->assertSame(
            ['#FFFFFF', '#000000', '#1976D2'],
            $schema['data']['palette']
        );
        $this->assertStringContainsString('colorpalette--allow-picker', $schema['extraClass']);
        $this->assertStringContainsString('js-color-picker', $schema['extraClass']);
        $this->assertArrayHasKey('data-palette', $schema['attributes']);
    }

    public function testSchemaDataWithoutPicker(): void
    {
        $field = ColorPaletteField::create(
            'BackgroundColour',
            'Background Colour',
            $this->hexSource()
        );

        $schema = $field->getSchemaData();

        $this->assertSame('OptionsetField', $schema['component']);
        $this->assertFalse($schema['data']['allowPicker']);
    }

    public function testSchemaStateUsesPickerDisplayValue(): void
    {
        $field = ColorPaletteField::create(
            'Colour',
            'Colour',
            $this->namedSource(),
            'Black'
        )->setAllowPicker(true);

        $state = $field->getSchemaState();

        $this->assertSame('#000', $state['value']);
    }

    public function testContrastingTextColorForLightAndDarkValues(): void
    {
        $light = ColorPaletteField::create(
            'Light',
            'Light',
            ['#FFFFFF' => '#FFFFFF'],
            '#FFFFFF'
        )->setAllowPicker(true);

        $dark = ColorPaletteField::create(
            'Dark',
            'Dark',
            ['#000000' => '#000000'],
            '#000000'
        )->setAllowPicker(true);

        $lightHtml = $this->convertToSingleLine($light->forTemplate());
        $darkHtml = $this->convertToSingleLine($dark->forTemplate());

        $this->assertStringContainsString('color: #000000', $lightHtml);
        $this->assertStringContainsString('color: #ffffff', $darkHtml);
    }

    public function testTypeIsColorpalette(): void
    {
        $field = ColorPaletteField::create('Colour', 'Colour', $this->namedSource());

        $this->assertSame('colorpalette', $field->Type());
    }
}
