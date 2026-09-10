<?php

namespace Heyday\ColorPalette\Tests\Fields;

use Heyday\ColorPalette\Fields\GroupedColorPaletteField;
use Heyday\ColorPalette\Fields\GroupedColorPaletteField_Readonly;
use InvalidArgumentException;
use SilverStripe\Dev\SapphireTest;

class GroupedColorPaletteFieldTest extends SapphireTest
{
    use SingleLineStringTrait;

    /**
     * @return array<string, array<string, string>>
     */
    private function groupedSource(): array
    {
        return [
            'Primary Palette' => [
                'White' => '#fff',
                'Black' => '#000',
            ],
            'Secondary Palette' => [
                'Blue' => 'blue',
                'Red' => 'red',
            ],
        ];
    }

    public function testFieldRendersGroupedPalettes(): void
    {
        $field = GroupedColorPaletteField::create(
            'BackgroundColor',
            'Background Color',
            $this->groupedSource()
        );

        $html = $this->convertToSingleLine($field->forTemplate());

        $this->assertStringContainsString('<h4>Primary Palette</h4>', $html);
        $this->assertStringContainsString('<h4>Secondary Palette</h4>', $html);
        $this->assertStringContainsString('value="White"', $html);
        $this->assertStringContainsString('value="Black"', $html);
        $this->assertStringContainsString('value="Blue"', $html);
        $this->assertStringContainsString('value="Red"', $html);
        $this->assertStringContainsString('style="background: #fff"', $html);
        $this->assertStringContainsString('style="background: blue"', $html);
        $this->assertStringContainsString('name="BackgroundColor"', $html);
        $this->assertStringContainsString('type="radio"', $html);
    }

    public function testSelectedValueIsChecked(): void
    {
        $field = GroupedColorPaletteField::create(
            'BackgroundColor',
            'Background Color',
            $this->groupedSource(),
            'Blue'
        );

        $html = $this->convertToSingleLine($field->forTemplate());

        $this->assertMatchesRegularExpression(
            '/id="BackgroundColor_Blue"[^>]*checked/',
            $html
        );
    }

    public function testTypeIncludesColorpaletteClasses(): void
    {
        $field = GroupedColorPaletteField::create(
            'BackgroundColor',
            'Background Color',
            $this->groupedSource()
        );

        $this->assertSame('groupedcolorpalette colorpalette', $field->Type());
    }

    public function testReadOnlyField(): void
    {
        $field = GroupedColorPaletteField::create(
            'BackgroundColor',
            'Background Color',
            $this->groupedSource()
        )->setValue('Blue')->performReadonlyTransformation();

        $this->assertInstanceOf(GroupedColorPaletteField_Readonly::class, $field);
        $this->assertTrue($field->isReadonly());

        $html = $this->convertToSingleLine($field->forTemplate());
        $this->assertStringContainsString('readonly', $html);
        $this->assertStringContainsString('value="Blue"', $html);
        $this->assertStringContainsString('style="background: blue"', $html);
    }

    public function testFlatSourceThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("To use GroupedColorPaletteField you need to pass in an array of array's");

        $field = GroupedColorPaletteField::create(
            'BackgroundColor',
            'Background Color',
            [
                'White' => '#fff',
                'Black' => '#000',
            ]
        );

        $field->forTemplate();
    }
}
