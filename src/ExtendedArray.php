<?php
namespace Asticode\Toolbox;

use RuntimeException;

class ExtendedArray
{
    public static function get(array $aInput, $aKeys)
    {
        if (!is_array($aKeys)) {
            if (!isset($aInput[$aKeys])) {
                return null;
            } else {
                return $aInput[$aKeys];
            }
        } else {
            $value = $aInput;
            foreach ($aKeys as $key) {
                if (!isset($value[$key])) {
                    return null;
                } else {
                    $value = $value[$key];
                }
            }
            return $value;
        }
    }

    public static function extendWithDefaultValues(array $aInput, array $aDefaultValues)
    {
        self::extendItem($aInput, $aDefaultValues);
        return $aInput;
    }

    private static function extendItem(array &$arrayExtended, array $arrayUsedToExtend)
    {
        foreach ($arrayUsedToExtend as $key => $value) {
            if (!is_array($value)) {
                if (!array_key_exists($key, $arrayExtended)) {
                    $arrayExtended[$key] = $value;
                }
            } else {
                if (!array_key_exists($key, $arrayExtended)) {
                    $arrayExtended[$key] = [];
                }
                self::extendItem($arrayExtended[$key], $value);
            }
        }
    }

    public static function checkRequiredKeys(array $aInput, array $aRequiredKeys)
    {
        // Loop through required keys
        foreach ($aRequiredKeys as $sRequiredKey) {
            if (!isset($aInput[$sRequiredKey])) {
                throw new RuntimeException(sprintf(
                    'Missing key %s in available keys %s',
                    $sRequiredKey,
                    implode(', ', array_keys($aInput))
                ));
            }
        }
    }

    public static function getFirstKey(array $aInput)
    {
        reset($aInput);
        return key($aInput);
    }

    public static function getLastKey(array $aInput)
    {
        end($aInput);
        return key($aInput);
    }

    public static function getFirstValue(array $aInput)
    {
        return array_shift($aInput);
    }

    public static function getLastValue(array $aInput)
    {
        return array_pop($aInput);
    }
}
