<?php
namespace Asticode\Toolbox;

use ReflectionClass;

class ExtendedUnitTesting
{
    public static function callMethod($oObject, $sMethodName, array $aArgs = []) {
        $class = new ReflectionClass($oObject);
        $method = $class->getMethod($sMethodName);
        $method->setAccessible(true);
        return $method->invokeArgs($oObject, $aArgs);
    }
}
