<?php
namespace Asticode\Toolbox;

use Composer\Script\Event;
use RuntimeException;

class ExtendedComposer
{

    public static function askValue(
        Event $oEvent,
        $sLabel,
        $sDefault = null,
        $bMandatory = false
    ) {
        // Create question
        $sQuestion = sprintf(
            '%s%s: ',
            $sLabel,
            !is_null($sDefault) ? sprintf(' [%s]', $sDefault) : ''
        );

        // Return
        return self::ask(
            $oEvent,
            $sQuestion,
            $sDefault,
            $bMandatory
        );
    }

    public static function ask(
        Event $oEvent,
        $sQuestion,
        $sDefault = null,
        $bMandatory = false
    ) {
        // Get validator
        if ($bMandatory) {
            $fValidator = function ($sValue) {
                if ($sValue !== '') {
                    return $sValue;
                } else {
                    throw new RuntimeException('Value can\'t be blank');
                }
            };
        } else {
            $fValidator = function ($sValue) {
                return $sValue;
            };
        }

        // Ask and validate
        return $oEvent->getIO()->askAndValidate(
            $sQuestion,
            $fValidator,
            false,
            $sDefault
        );
    }

}
