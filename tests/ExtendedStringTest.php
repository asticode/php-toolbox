<?php
namespace Asticode\Toolbox\Tests;

use Asticode\Toolbox\ExtendedString;
use PHPUnit_Framework_TestCase;

class ExtendedStringTest extends PHPUnit_Framework_TestCase
{

    public function testAddTab()
    {
        // Initialize
        $sInput = 'test';

        // Assert
        $this->assertEquals($sInput . '    ', ExtendedString::addTab($sInput));
    }

    public function testToCamelCase()
    {
        // Initialize
        $sInput = 'test_camel_case';

        // Assert
        $this->assertEquals('testCamelCase', ExtendedString::toCamelCase($sInput));
    }

    public function testToSnakeCase()
    {
        // Initialize
        $sInput = 'TestSnakeCase';

        // Assert
        $this->assertEquals('test_snake_case', ExtendedString::toSnakeCase($sInput));
    }

    public function testEscape()
    {
        // Initialize
        $sInput = '"key":"value"';

        // Assert
        $this->assertEquals('\"key\":\"value\"', ExtendedString::escape($sInput, '"'));
        $this->assertEquals('b"keyb":"valueb"', ExtendedString::escape($sInput, '"', 'b%s', ':'));
    }
}
