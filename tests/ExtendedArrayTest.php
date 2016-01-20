<?php
namespace Asticode\Toolbox\Tests;

use Asticode\Toolbox\ExtendedArray;
use PHPUnit_Framework_TestCase;

class ExtendedArrayTest extends PHPUnit_Framework_TestCase
{

    public function testGet()
    {
        // Initialize
        $aInput = [
            'level1' => [
                'level1_1' => 'value1_1',
                'level1_2' => 'value1_2',
            ],
            'level2' => [
                'level2_1' => 'value2_1',
                'level2_2' => 'value2_2',
            ],
        ];

        // Assert
        $this->assertEquals($aInput['level1'], ExtendedArray::get($aInput, 'level1'));
        $this->assertEquals($aInput['level1'], ExtendedArray::get($aInput, ['level1']));
        $this->assertEquals('value2_2', ExtendedArray::get($aInput, ['level2', 'level2_2']));
    }

    public function testExtendWithDefaultValues()
    {
        // Initialize
        $aInput = [
            'key1' => [
                'key1_1' => 'value1_1',
            ]
        ];
        $aDefaultValues = [
            'key1' => [
                'key1_1' => 'troll',
                'key1_2' => 'value1_2',
            ],
            'key2' => 'value2',
        ];

        // Assert
        $this->assertEquals([
            'key1' => [
                'key1_1' => 'value1_1',
                'key1_2' => 'value1_2',
            ],
            'key2' => 'value2',
        ], ExtendedArray::extendWithDefaultValues($aInput, $aDefaultValues));
    }

    public function testCheckRequiredKeys()
    {
        // Initialize
        $aInput = [
            'key' => 'value',
        ];

        // Assert
        $this->setExpectedException('RuntimeException');
        ExtendedArray::checkRequiredKeys($aInput, ['key']);
        ExtendedArray::checkRequiredKeys($aInput, ['troll']);
    }

    public function testGetSpecificKeys()
    {
        // Initialize
        $aInput = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        // Assert
        $this->assertEquals('key1', ExtendedArray::getFirstKey($aInput));
        $this->assertEquals('key3', ExtendedArray::getLastKey($aInput));
        $this->assertEquals('value1', ExtendedArray::getFirstValue($aInput));
        $this->assertEquals('value3', ExtendedArray::getLastValue($aInput));
    }

    public function testClean()
    {
        // Initialize
        $aInput = [
            'key1' => 'value1',
            'key2' => ' ',
            'key3' => 'value3',
        ];

        // Assert
        $this->assertEquals(2, count(ExtendedArray::clean($aInput)));
    }
}
