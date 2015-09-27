<?php
namespace Asticode\Toolbox;

use RuntimeException;

class ExtendedShell
{
    public static function exec(
        $sCommand,
        array &$aOutput = null,
        $iTimeout = 0,
        $bThrowException = true,
        $iSigkillDelay = 1,
        $iUSleep = 100000
    ) {
        // Create paths
        $aPaths = [
            'output' => tempnam(sys_get_temp_dir(), 'asticode_shell_'),
            'exit_status' => tempnam(sys_get_temp_dir(), 'asticode_shell_'),
        ];

        // Create asticode command
        $sAsticodeCommand = sprintf(
            '(%1$s %2$s 2>%3$s ; echo $? 1>%4$s 2>&1) & echo $! 2>&1',
            $sCommand,
            // TODO change preg_match because of echo « a>b »
            preg_match('/\>/', $sCommand) === 0 ? '1>&2' : '',
            $aPaths['output'],
            $aPaths['exit_status']
        );

        // Execute
        $aOutputCommand = [];
        exec($sAsticodeCommand, $aOutputCommand);

        // Record start time
        $iStartTime = microtime(true);

        // Process ID is valid
        if (isset($aOutputCommand[0])) {
            // Get process ID
            $sProcessID = $aOutputCommand[0];

            // Check end of the process
            $sErrorMessage = '';
            do {
                // Check for timeout
                if ($iTimeout > 0 and (microtime(true) - $iStartTime) > $iTimeout) {
                    // Kill process
                    // TODO do not wait x seconds if SIGTERM was ok quickly
                    exec(sprintf(
                        'kill -s SIGTERM %1$s & sleep %2$s && kill -s SIGKILL %1$s',
                        $sProcessID,
                        $iSigkillDelay
                    ));

                    // Update error message
                    $sErrorMessage = sprintf(
                        'Command has timed out (timeout: %s | processId: %s)',
                        $iTimeout,
                        $sProcessID
                    );

                    // Update terminate
                    $bTerminate = true;
                } else {
                    // Update terminate
                    $bTerminate = self::isProcessDone($sProcessID);

                    // Sleep
                    usleep($iUSleep);
                }
            } while (!$bTerminate);

            // Return
            return self::processResult($sCommand, $aPaths, $aOutput, $bThrowException, $sErrorMessage);
        } else {
            // Return
            return self::processResult($sCommand, $aPaths, $aOutput, $bThrowException, 'No process ID found');
        }
    }

    private static function isProcessDone($sProcessId)
    {
        // Check process
        $aOutput = [];
        exec(sprintf(
            'ps -p %s 2>&1',
            $sProcessId

        ), $aOutput);

        // Return
        return count($aOutput) <= 1;
    }

    private static function processResult(
        $sCommand,
        array $aPaths,
        array &$aOutput,
        $bThrowException,
        $sErrorMessage = ''
    ) {
        // Get return status
        $sExitStatus = trim(file_get_contents($aPaths['exit_status']));
        if ($sExitStatus === '0') {
            $iExitStatus = 0;
        } elseif (intval($sExitStatus) === 0) {
            $iExitStatus = 64;
        } else {
            $iExitStatus = intval($sExitStatus);
        }

        // Inject error message
        if ($sErrorMessage !== '') {
            $aStdErr[] = $sErrorMessage;
            $iExitStatus = 64;
        }

        // Get output
        if (!is_null($aOutput)) {
            // Get output
            $aOutput = file($aPaths['output']);
        }

        // Remove temp files
        exec(sprintf(
            "rm '%s'",
            implode("' '", $aPaths)
        ));

        // Throw exception
        if ($bThrowException and $iExitStatus !== 0) {
            throw new RuntimeException(sprintf(
                'Invalid return status "%s" for command "%s"',
                $iExitStatus,
                $sCommand
            ));
        }

        // Return
        return $iExitStatus;
    }
}
