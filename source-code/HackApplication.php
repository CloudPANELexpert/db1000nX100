<?php

class HackApplication
{
    private $log = '',
            $instantLog = false,
            $readChildProcessOutput = false,
            $readChildProcessOutputBrokenLine,
            $process,
            $processPGid,
            $pipes,
            $wasLaunched = false,
            $launchFailed = false,
            $currentCountry = '',
            $netnsName,
            $stat = false,
            $showInfoMessages;

    const   targetStatsInitial = [
            'attacking'          => 0,
            'requests_attempted' => 0,
            'requests_sent'      => 0,
            'responses_received' => 0,
            'bytes_sent'         => 0
        ];

    public function __construct($netnsName)
    {
        $this->netnsName = $netnsName;
        $this->showInfoMessages = SelfUpdate::isDevelopmentVersion();
    }

    public function processLaunch()
    {
        if ($this->launchFailed) {
            return -1;
        }

        if ($this->wasLaunched) {
            return true;
        }

        $command = "export GOMAXPROCS=1 ;   sleep 1 ;   "
				 . "ip netns exec {$this->netnsName}   nice -n 10   "
				 . "/sbin/runuser -p -u hack-app -g hack-app   --   "
                 . __DIR__ . "/DB1000N/db1000n  -prometheus_on=false  " . static::getCmdArgsForConfig() . '   '
                 . "--log-format json   2>&1";

        $this->log($command);
        $descriptorSpec = array(
            0 => array("pipe", "r"),  // stdin
            1 => array("pipe", "w"),  // stdout
            2 => array("pipe", "a")   // stderr
        );
        $this->process = proc_open($command, $descriptorSpec, $this->pipes);
        $this->processPGid = procChangePGid($this->process, $log);
        $this->log($log);
        if ($this->processPGid === false) {
            $this->terminate(true);
            $this->log('Command failed: ' . $command);
            $this->launchFailed = true;
            return -1;
        }

        stream_set_blocking($this->pipes[1], false);
        $this->wasLaunched = true;
        return true;
    }

    private function log($message, $noLineEnd = false)
    {
        $message .= $noLineEnd  ?  '' : "\n";
        $this->log .= $message;
        if ($this->instantLog) {
            echo $message;
        }
    }

    public function clearLog()
    {
        $this->log = '';
    }

    public function setReadChildProcessOutput($state)
    {
        $this->readChildProcessOutput = $state;
    }

    public function pumpLog() : string
    {
        $ret = $this->log;

        if (!$this->readChildProcessOutput) {
            goto retu;
        }

        //------------------- read db1000n stdout -------------------

        $output = streamReadLines($this->pipes[1], 0.1);
        // --- Split lines
        $linesArray = mbSplitLines($output);
        // --- Remove empty lines
        $linesArray = mbRemoveEmptyLinesFromArray($linesArray);

        foreach ($linesArray as $line) {
            $lineObj = json_decode($line);
            if (is_object($lineObj)) {
                $this->readChildProcessOutputBrokenLine = 0;

                if (
                        $lineObj->level === 'info'
                    &&  $lineObj->msg   === 'location info'
                ) {
                    $this->currentCountry = $lineObj->country;
                }
                //-----------------------------------------------------
                else if (
                        $lineObj->level === 'info'
                    &&  $lineObj->msg   === 'stats'
                ) {
                    if (isset($lineObj->targets)) {
                        $targets = get_object_vars($lineObj->targets);
                        ksort($targets);
                        $lineObj->targets = $targets;
                        $this->stat = $lineObj;
                    }
                }
                //-----------------------------------------------------
                else if (
                        $lineObj->level === 'info'
                    &&  $lineObj->msg   === 'attacking'
                ) {
                    // Do nothing
                }
                //-----------------------------------------------------
                else if ($this->showInfoMessages) {
                    $termColor = Term::clear;
                    if ($lineObj->level === 'info') {
                        $termColor = Term::gray;
                    }
                    foreach (mbSplitLines(print_r($lineObj, true)) as $line) {
                        $ret .= $termColor . $line . Term::clear . "\n";
                    }
                }
                //-----------------------------------------------------
                else if ($lineObj->level !== 'info') {
                    $ret .= $line . "\n";
                }

            } else {

                $this->readChildProcessOutputBrokenLine++;
                if ($this->readChildProcessOutputBrokenLine < 10) {
                    return '';
                } else {
                    $ret .= $line . "\n";
                }

            }
        }

        retu:
        $this->log = '';
        return mbRTrim($ret);
    }

    public function getStatisticsBadge() : ?string
    {
        global $LOG_WIDTH, $LOG_PADDING_LEFT;
        $columnWidth = 10;
        $ret = '';

        if (!$this->stat  ||  !$this->stat->targets  ||  !count($this->stat->targets)) {
            return null;
        }

        //------- calculate the longest target name
        $targetNameMaxLength = 0;
        foreach ($this->stat->targets as $targetName => $targetStat) {
            $targetNameMaxLength = max($targetNameMaxLength, mb_strlen($targetName));
        }
        $targetNamePaddedLineLength = $LOG_WIDTH - $LOG_PADDING_LEFT - $columnWidth * 4;

        //------- Title rows
        $ret .= str_pad('Targets statistic', $targetNamePaddedLineLength);
        $columnNames = [
            'Requests',
            'Requests',
            'Responses',
            'MiB'
        ];
        foreach ($columnNames as $columnName) {
            $columnNamePadded = mb_substr($columnName, 0, $columnWidth);
            $ret .= str_pad($columnNamePadded, $columnWidth, ' ', STR_PAD_LEFT);
        }
        $ret .= "\n";
        $ret .= str_pad("", $targetNamePaddedLineLength);
        $columnNames = [
            'attempted',
            'sent',
            'received',
            'sent'
        ];
        foreach ($columnNames as $columnName) {
            $columnNamePadded = mb_substr($columnName, 0, $columnWidth);
            $ret .= str_pad($columnNamePadded, $columnWidth, ' ', STR_PAD_LEFT);
        }
        $ret .= "\n\n";

        //------- Content rows

        $this->stat->db1000nx100 = new stdClass();
        $this->stat->db1000nx100->totalHttpRequests = 0;
        $this->stat->db1000nx100->totalHttpResponses = 0;
        foreach ($this->stat->targets as $targetName => $targetStat) {
            $targetNameCut = mb_substr($targetName, 0, $targetNamePaddedLineLength - 2);
            $mibSent = roundLarge($targetStat->bytes_sent / 1024 / 1024);
            $ret .= str_pad($targetNameCut, $targetNamePaddedLineLength);
            $ret .= str_pad($targetStat->requests_attempted, $columnWidth, ' ', STR_PAD_LEFT);
            $ret .= str_pad($targetStat->requests_sent,      $columnWidth, ' ', STR_PAD_LEFT);
            $ret .= str_pad($targetStat->responses_received, $columnWidth, ' ', STR_PAD_LEFT);
            $ret .= str_pad($mibSent,                        $columnWidth, ' ', STR_PAD_LEFT);
            $ret .= "\n";

            $pattern = '#^https?:\/\/#';
            if (preg_match($pattern, $targetName, $matches)) {
                $this->stat->db1000nx100->totalHttpRequests  += $targetStat->requests_attempted;
                $this->stat->db1000nx100->totalHttpResponses += $targetStat->responses_received;                
            }
        }

        //------- Total row

        $ret .= "\n";
        $totalMiBSent = roundLarge($this->stat->total->bytes_sent / 1024 / 1024);
        $ret .= str_pad('Total', $targetNamePaddedLineLength);
        $ret .= str_pad($this->stat->total->requests_attempted, $columnWidth, ' ', STR_PAD_LEFT);
        $ret .= str_pad($this->stat->total->requests_sent,      $columnWidth, ' ', STR_PAD_LEFT);
        $ret .= str_pad($this->stat->total->responses_received, $columnWidth, ' ', STR_PAD_LEFT);
        $ret .= str_pad($totalMiBSent,                          $columnWidth, ' ', STR_PAD_LEFT);
        $ret .= "\n";

        return $ret;
    }

    // Should be called after getLog()
    public function getEfficiencyLevel()
    {

        if (!$this->stat  ||  !$this->stat->targets  ||  !count($this->stat->targets)) {
            return null;
        }

        $requests = $this->stat->db1000nx100->totalHttpRequests;
        $responses = $this->stat->db1000nx100->totalHttpResponses;

        if (! $requests) {
            return null;
        }

        $averageResponseRate = $responses * 100 / $requests;
        return roundLarge($averageResponseRate);
    }

    // Should be called after getLog()
    public function getCurrentCountry()
    {
        return $this->currentCountry;
    }

    public function isAlive()
    {
        return isProcAlive($this->process);
    }

    // Only first call of this function return real value, next calls return -1
    public function getExitCode()
    {
        $processStatus = proc_get_status($this->process);
        return $processStatus['exitcode'];
    }

    public function terminate($hasError = false)
    {
        global $LOG_BADGE_WIDTH;

        if ($this->processPGid) {
            $this->log(str_repeat(' ', $LOG_BADGE_WIDTH + 3) . "db1000n SIGTERM PGID -{$this->processPGid}");
            @posix_kill(0 - $this->processPGid, SIGTERM);
        }
        @proc_terminate($this->process);
    }

    public function getProcess()
    {
        return $this->process;
    }

    // ----------------------  Static part of the class ----------------------

    private static $configUrl,
                   $localConfigPath,
                   $useLocalConfig;


    public static function constructStatic()
    {
        global $TEMP_DIR;
        static::$configUrl = 'https://raw.githubusercontent.com/db1000n-coordinators/LoadTestConfig/main/config.v0.7.json';
        static::$localConfigPath = $TEMP_DIR . '/db1000n-config.json';
        static::$useLocalConfig = false;
    }

    private static function loadConfig()
    {
        $config = httpGet(static::$configUrl);
        if ($config !== false) {
            MainLog::log("Config file for db1000n downloaded from " . static::$configUrl);
            file_put_contents_secure(static::$localConfigPath, $config);
			chmod(static::$localConfigPath, changeLinuxPermissions(0, 'rw', 'r', 'r'));
        } else {
            MainLog::log("Failed to downloaded config file for db1000n");
        }
    }

    private static function getCmdArgsForConfig()
    {
        if (! static::$useLocalConfig) {
            return '';
        }

        return ' -c "' . static::$localConfigPath . '" ';
    }
    
    public static function newIteration()
    {
        @unlink(static::$localConfigPath);
        static::loadConfig();
        if (file_exists(static::$localConfigPath)) {
            static::$useLocalConfig = true;
        } else {
            static::$useLocalConfig = false;
        }
    }
}

HackApplication::constructStatic();