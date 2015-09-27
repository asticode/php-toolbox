<?php
namespace Asticode\Toolbox;

use RuntimeException;

class ExtendedShell
{
    public static function exec($sCommand, $iTimeout = 0, $bThrowException = false, $iSigkillDelay = 1)
    {
        // Create paths
        $aPaths = [
            'stdout' => tempnam(sys_get_temp_dir(), 'asticode_shell_'),
            'stderr' => tempnam(sys_get_temp_dir(), 'asticode_shell_'),
            'exit_status' => tempnam(sys_get_temp_dir(), 'asticode_shell_'),
        ];

        // Create asticode command
        $sAsticodeCommand = sprintf(
            '(%1$s %2$s 2>%3$s ; echo $? 1>%4$s 2>&1) & echo $! 2>&1',
            $sCommand,
            preg_match('/\>/', $sCommand) === 0 ? sprintf('1>%s', $aPaths['stdout']) : '',
            $aPaths['stderr'],
            $aPaths['exit_status']
        );

        // Execute
        $aOutputArray = [];
        exec($sAsticodeCommand, $aOutputArray);

        // Record start time
        $iStartTime = microtime(true);

        // Command is valid
        if (!isset($aOutputArray[0])) {
            // Return
            return self::processExec($sCommand, $aPaths, $bThrowException, 'No process ID found');
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
                    'kill -s SIGTERM %1$s & sleep %2$s && kill -s SIGKILL %1$s',
                    $sProcessID,
                    $iSigkillDelay
                ));

                // Return
                return self::processExec($sCommand, $aPaths, $bThrowException, sprintf(
                    'Command has timed out (timeout: %s | processId: %s)',
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
            usleep(100000);
        } while (!$bTerminate);
        
        // Return
        return self::processExec($sCommand, $aPaths, $bThrowException);
    }

    private static function processExec($sCommand, array $aPaths, $bThrowException, $sErrorMessage = '')
    {
        // Get outputs
        $aStdOut = explode("\n", file_get_contents($aPaths['stdout']));
        $aStdErr = explode("\n", file_get_contents($aPaths['stderr']));

        // Get return status
        $sExitStatus = trim(file_get_contents($aPaths['exit_status']));
        if ($sExitStatus === '0') {
            $iExitStatus = 0;
        } elseif (intval($sExitStatus) === 0) {
            $iExitStatus = 128;
        } else {
            $iExitStatus = intval($sExitStatus);
        }

        // Inject error message
        if ($sErrorMessage !== '') {
            $aStdErr[] = $sErrorMessage;
            $iExitStatus = 128;
        }

        // Remove temp files
        exec(sprintf(
            "rm '%s'",
            implode("' '", $aPaths)
        ));

        // Throw exception
        if ($bThrowException and $iExitStatus !== 0) {
            throw new RuntimeException(sprintf(
                'Invalid return status "%s" for command "%s" with stdout "%s" and stderr "%s"',
                $iExitStatus,
                $sCommand,
                implode('/', $aStdOut),
                implode('/', $aStdErr)
            ));
        }

        // Return
        return [
            ExtendedArray::clean($aStdOut),
            ExtendedArray::clean($aStdErr),
            $iExitStatus,
        ];
    }
}
