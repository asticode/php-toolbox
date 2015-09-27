<?php
namespace Asticode\Toolbox;

class ExtendedShell
{
    public static function exec($sCommand, $iTimeout = 0, $iUsleep = 100000)
    {
        // Initialize
        $aOutputArray = [];
        $sOutputContentPath = tempnam(sys_get_temp_dir(), 'asticode_shell_');
        $sErrorContentPath = tempnam(sys_get_temp_dir(), 'asticode_shell_');

        // Update command
        if (preg_match('/\>/', $sCommand) === 0) {
            $sCommand .= sprintf(
                ' 1>%s',
                $sOutputContentPath
            );
        }
        $sCommand .= sprintf(
            ' 2>%s & echo $! 2>&1',
            $sErrorContentPath
        );

        // Execute
        exec($sCommand, $aOutputArray);

        // Record start time
        $iStartTime = microtime(true);

        // Command is valid
        if (!isset($aOutputArray[0])) {
            // Return
            return self::execReturn($sOutputContentPath, $sErrorContentPath, sprintf(
                'No process ID found for Command %s with message %s',
                $sCommand,
                implode("\n", $aOutputArray)
            ));
        }

        // Get process ID
        $sProcessID = $aOutputArray[0];

        // Check end of the process
        do {
            // Initialize
            $bTerminate = true;

            // Check for timeout
            if ($iTimeout > 0 and (microtime(true) - $iStartTime) > $iTimeout) {
                // Kill process
                exec(sprintf(
                    'kill -9 %s',
                    $sProcessID
                ));

                // Return
                return self::execReturn($sOutputContentPath, $sErrorContentPath, sprintf(
                    'Command %s has timed out (timeout: %s | processId: %s)',
                    $sCommand,
                    $iTimeout,
                    $sProcessID
                ));
            }

            // Check process
            $aOutputArray = [];
            exec(sprintf(
                'ps -p %s 2>&1',
                $sProcessID

            ), $aOutputArray);

            // Process is still running
            if (count($aOutputArray) > 1) {
                $bTerminate = false;
            }

            // Sleep
            usleep($iUsleep);
        } while (!$bTerminate);
        
        // Return
        return self::execReturn($sOutputContentPath, $sErrorContentPath);
    }

    private static function execReturn($sOutputContentPath, $sErrorContentPath, $sAdditionnalErrorMessage = '')
    {
        // Get output
        $sOutputContent = file_get_contents($sOutputContentPath);
        $sErrorContent = file_get_contents($sErrorContentPath);

        // Remove temp files
        exec(sprintf(
            "rm '%s' '%s'",
            $sOutputContentPath,
            $sErrorContentPath
        ));

        // Explode output
        $sOutputArray = explode("\n", $sOutputContent);
        $sErrorArray = explode("\n", $sErrorContent);

        // Add additionnal error message
        if ($sAdditionnalErrorMessage !== '') {
            $sErrorContent[] = $sAdditionnalErrorMessage;
        }

        // Clean outputs

        // Return
        return [
            ExtendedArray::clean($sOutputArray),
            ExtendedArray::clean($sErrorArray),
        ];
    }
}
