<?php
namespace Asticode\Toolbox;

use Composer\Script\Event;
use Exception;
use RuntimeException;

class ExtendedComposer
{

    public static function askString(
        Event $oEvent,
        $sLabel,
        $sDefault = null,
        $bMandatory = false
    ) {
        return self::ask(
            $oEvent,
            self::formatQuestion($sLabel, $sDefault),
            $sDefault,
            $bMandatory
        );
    }

    public static function askBinaryPath(
        Event $oEvent,
        $sLabel,
        $sBinaryName,
        $sCheckCommand,
        $sDefault = null,
        $bGetRealDefaultValue = true,
        $bMandatory = false
    ) {
        // Get real default value
        $aOutput = [];
        if ($bGetRealDefaultValue) {
            try {
                ExtendedShell::exec(sprintf(
                    'which %s',
                    $sBinaryName
                ), $aOutput);
            } catch (Exception $oException) {
                $aOutput = [];
            }
        }
        $sDefault = isset($aOutput[0]) ? trim($aOutput[0]) : $sDefault;

        // Return
        return self::ask(
            $oEvent,
            self::formatQuestion($sLabel, $sDefault),
            $sDefault,
            $bMandatory,
            function ($sValue) use ($sCheckCommand) {
                // Initialize
                $aOutput = [];

                // Update check command
                $sCheckCommand = sprintf($sCheckCommand, $sValue);

                // Exec
                try {
                    ExtendedShell::exec($sCheckCommand, $aOutput);
                } catch (\Exception $oException) {
                    throw new RuntimeException(sprintf(
                        "Invalid path %s with error %s",
                        $sValue,
                        implode(', ', ExtendedArray::clean($aOutput))
                    ));
                }

                // Return
                return $sValue;
            }
        );
    }

    public static function ask(
        Event $oEvent,
        $sQuestion,
        $sDefault = null,
        $bMandatory = false,
        $fValidatorCustom = null
    ) {
        // Get validator
        if ($bMandatory) {
            $fValidator = function ($sValue) use ($fValidatorCustom) {
                if ($sValue !== '' and !is_null($sValue)) {
                    if (is_null($fValidatorCustom)) {
                        return $sValue;
                    } else {
                        return call_user_func_array($fValidatorCustom, [$sValue]);
                    }
                } else {
                    throw new RuntimeException('Value can\'t be blank');
                }
            };
        } else {
            $fValidator = function ($sValue) use ($fValidatorCustom) {
                $sValue = is_null($sValue) ? '' : $sValue;
                if (is_null($fValidatorCustom)) {
                    return $sValue;
                } else {
                    return call_user_func_array($fValidatorCustom, [$sValue]);
                }
            };
        }

        // Return
        return $oEvent->getIO()->askAndValidate(
            $sQuestion,
            $fValidator,
            null,
            $sDefault
        );
    }

    private static function formatQuestion($sLabel, $sDefault)
    {
        return sprintf(
            '%s%s: ',
            $sLabel,
            !is_null($sDefault) ? sprintf(' [%s]', $sDefault) : ''
        );
    }

}
