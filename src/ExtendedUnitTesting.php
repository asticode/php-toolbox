<?php
namespace Asticode\Toolbox;

use ReflectionClass;

class ExtendedUnitTesting
{
    public static function callMethod($sClassName, $sMethodName, array $aArgs = [], $oObject = null) {
        $oReflectionClass = new ReflectionClass($sClassName);
        $oReflectionMethod = $oReflectionClass->getMethod($sMethodName);
        $oReflectionMethod->setAccessible(true);
        return $oReflectionMethod->invokeArgs($oObject, $aArgs);
    }
}
