<?php
namespace Asticode\Toolbox;

class ExtendedString
{
    public static function addTab($sInput, $numberOfTabs = 1, $numberOfSpacesPerTab = 4)
    {
        // Compute number of spaces.
        $numberOfSpaces = $numberOfSpacesPerTab * $numberOfTabs;

        // Add spaces.
        for ($a = 0; $a < $numberOfSpaces; $a++) {
            $sInput .= ' ';
        }
        
        // Return
        return $sInput;
    }

    public static function toCamelCase($sInput, $sSeparator = '_', $capitalizeFirstCharacter = false)
    {
        $sInput = str_replace(' ', '', ucwords(str_replace($sSeparator, ' ', $sInput)));
        if (!$capitalizeFirstCharacter) {
            $sInput[0] = strtolower($sInput[0]);
        }
        return trim($sInput);
    }

    public static function toSnakeCase($sInput, $sSeparator = '_', $replaceUppercaseLetters = false)
    {
        $sInput = preg_replace('/[\s]+/', $sSeparator, $sInput);
        if ($replaceUppercaseLetters === true) {
            for ($index = 0; $index < strlen($sInput); $index++) {
                if (ctype_upper($sInput[$index]) === true) {
                    $sInput[$index] = strtolower($sInput[$index]);
                    $sInput = substr_replace($sInput, $sSeparator, $index, 0);
                    $index++;
                }
            }
        }
        $sInput = preg_replace(sprintf('/[%s]+/', self::pregQuote($sSeparator)), $sSeparator, $sInput);
        return trim(strtolower($sInput));
    }

    public static function pregQuote($sInput)
    {
        return preg_replace('/\//', '\/', preg_quote($sInput));
    }

    public static function escape(
        $sInput,
        $sEscapedString,
        $sReplacePattern = '\\%s',
        $sNoEscapeIfStringIsFoundBefore = ''
    ) {
        // Get pattern
        $sPattern = sprintf(
            '/%s%s/',
            $sNoEscapeIfStringIsFoundBefore !== '' ? sprintf(
                '(?<!%s)',
                self::pregQuote($sNoEscapeIfStringIsFoundBefore)
            ) : '',
            self::pregQuote($sEscapedString)
        );

        // Replace
        return preg_replace($sPattern, sprintf(
            $sReplacePattern,
            $sEscapedString
        ), $sInput);
    }
}
