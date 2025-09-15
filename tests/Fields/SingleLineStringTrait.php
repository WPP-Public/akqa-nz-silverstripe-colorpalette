<?php

namespace Heyday\ColorPalette\Tests\Fields;

trait SingleLineStringTrait
{
    protected function convertToSingleLine(string $value): string
    {
        return preg_replace('/\s+/', ' ', str_replace(["\r\n", "\r", "\n"], '', $value));
    }

}
