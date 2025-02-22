<?php

class OpenVpnProvider  /* Model */
{
    private $name,
            $dir,
            $settingsFile,
            $settings,
            $openVpnConfigs,
            $scores;

    const   dockerOvpnRoot = 'put-your-ovpn-files-here';

    public function __construct($name, $dir, $settingsFile)
    {
        $this->name = $name;
        $this->dir = $dir;
        $this->settingsFile = $settingsFile;
        $this->settings = static::parseProviderSettingsFile($settingsFile);
        $this->openVpnConfigs = [];
        $this->scores = [];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDir()
    {
        return $this->dir;
    }

    public function getSetting($settingName)
    {
        return $this->settings[$settingName] ?? null;
    }

    public function addOpenVpnConfig(OpenVpnConfig $ovpnConfig)
    {
        $this->openVpnConfigs[$ovpnConfig->getId()] = $ovpnConfig;
    }

    public function getAllOpenVpnConfigs()
    {
        return $this->openVpnConfigs;
    }

    public function countAllOpenVpnConfigs()
    {
        return count($this->openVpnConfigs);
    }

    public function countBadOpenVpnConfigs()
    {
        $ret = 0;

        foreach ($this->openVpnConfigs as $openVpnConfig) {
            if ($openVpnConfig->isBadConfig()) {
                $ret++;
            }
        }

        return $ret;
    }

    public function countUsedOpenVpnConfigs()
    {
        $ret = 0;

        foreach ($this->openVpnConfigs as $openVpnConfig) {
            if ($openVpnConfig->isInUse()) {
                $ret++;
            }
        }

        return $ret;
    }

    public function getSuccessfulConnectionsCount()
    {
        $ret = 0;
        foreach ($this->openVpnConfigs as $openVpnConfig) {
            $ret += $openVpnConfig->getSuccessfulConnectionsCount();
        }
        return $ret;
    }

    public function getFailedConnectionsCount()
    {
        $ret = 0;
        foreach ($this->openVpnConfigs as $openVpnConfig) {
            $ret += $openVpnConfig->getFailedConnectionsCount();
        }
        return $ret;
    }

    public function getLastSuccessfulConnectionAt()
    {
        $ret = 0;
        foreach ($this->openVpnConfigs as $openVpnConfig) {
            $ret = max($ret, $openVpnConfig->getLastSuccessfulConnectionAt());
        }
        return $ret;
    }

    public function getLastUsedAt()
    {
        $ret = 0;
        foreach ($this->openVpnConfigs as $openVpnConfig) {
            $ret = max($ret, $openVpnConfig->getLastUsedAt());
        }
        return $ret;
    }

    public function isBadProvider()
    {
        global $SCRIPT_STARTED_AT;

        $lastUsedAt = $this->getLastUsedAt();
        $lastSuccessfulConnectionAt = $this->getLastSuccessfulConnectionAt();

        $ret = time() - max($SCRIPT_STARTED_AT, $lastUsedAt) < 12 * 60 * 60
           &&  time() - max($SCRIPT_STARTED_AT, $lastSuccessfulConnectionAt) > 3 * 60 * 60;

        /*if ($this->name ==='piavpn') {
            MainLog::log("isBadProvider=$ret");
        }*/

        return $ret;
    }

    public function getAverageScorePoints()
    {
        $providerScoreSum = 0;
        $configsWithScoreCount = 0;
        $providerFailedConnectionsCount = 0;
        $providerSuccessfulConnectionCount = 0;

        foreach ($this->openVpnConfigs as $openVpnConfig) {
            $score = $openVpnConfig->getAverageScorePoints();
            if ($score) {
                $providerScoreSum += $score;
                $configsWithScoreCount++;
            }
            $providerFailedConnectionsCount += $openVpnConfig->getFailedConnectionsCount();
            $providerSuccessfulConnectionCount += $openVpnConfig->getSuccessfulConnectionsCount();
        }

        $providerScoreAverage = 0;
        if ($configsWithScoreCount) {
            $providerScoreAverage = intdiv($providerScoreSum, $configsWithScoreCount);
        }

        $failedConnectionsBalance = $providerSuccessfulConnectionCount - $providerFailedConnectionsCount;
        if ($failedConnectionsBalance < 0) {
            $providerScoreAverage -= $providerScoreAverage / 100 * abs($failedConnectionsBalance);
            $providerScoreAverage = intRound($providerScoreAverage);
            $providerScoreAverage = max($providerScoreAverage, 0);
        }

        return $providerScoreAverage;
    }
    
    public function getMaxSimultaneousConnections()
    {
        return intval($this->getSetting('max_connections') ?? -1);
    }

    public function getUniqueIPsPool()
    {
        $ret = [];
        foreach ($this->openVpnConfigs as $openVpnConfig) {
            $ret = array_merge($ret, $openVpnConfig->getUniqueIPsPool());
        }
        return array_unique($ret);
    }

    //-------------------------------------------------------------

    const credentialsFileBasename       = 'credentials.txt',
          providerSettingsFileBasename  = 'vpn-provider-config.txt';

    public static $openVpnProviders;

    private static $ovpnFilesList;

    public static function constructStatic()
    {
        Actions::addAction('AfterInitSession', [static::class, 'actionAfterInitSession'], 12);
    }

    public static function actionAfterInitSession()
    {
        global $SESSIONS_COUNT;

        if ($SESSIONS_COUNT !== 1) {
            return;
        }

        MainLog::log("Reading ovpn files. Please, wait ...", 1, 1);

        if (Config::$putYourOvpnFilesHerePath) {

            $searchOvpnInList = searchInFilesList(
                Config::$filesInMediaDir,
                SEARCH_IN_FILES_LIST_MATCH_DIRNAME + SEARCH_IN_FILES_LIST_RETURN_FILES,
                '^' . preg_quote(Config::$putYourOvpnFilesHerePath)
            );

        } else {
            $searchOvpnInList = Config::$filesInMediaDir;
        }
        Config::$filesInMediaDir = [];  // we won't need it anymore, release memory

        static::$ovpnFilesList = searchInFilesList(
            $searchOvpnInList,
            SEARCH_IN_FILES_LIST_MATCH_EXT + SEARCH_IN_FILES_LIST_RETURN_FILES,
            '^ovpn$'
        );

        $ovpnFilesCount = count(static::$ovpnFilesList);
        if (! $ovpnFilesCount) {
            _die("NO *.ovpn files found in Shared Folders\n"
                . "Add a share folder with ovpn files and reboot this virtual machine");
        }

        static::$openVpnProviders = [];
        foreach (static::$ovpnFilesList as $ovpnFile) {
            $everything = static::getEverythingAboutOvpnFile($ovpnFile);
            $providerName = $everything['providerName'];

            $openVpnProvider = static::$openVpnProviders[$providerName] ?? null;
            if (! $openVpnProvider) {
                $openVpnProvider = new OpenVpnProvider($providerName, $everything['providerDir'], $everything['providerSettingsFile']);
                static::$openVpnProviders[$providerName] = $openVpnProvider;
            }

            $openVpnConfig = new OpenVpnConfig($everything['ovpnFile'], $everything['credentialsFile'], $openVpnProvider);
            $openVpnProvider->addOpenVpnConfig($openVpnConfig);
        }
    }

    public static function pickOpenVpnConfig()
    {
        if (rand(0, 2) === 0) {
            // pick from random provider
            shuffle(static::$openVpnProviders);
        } else {
            // pick from best provider
            static::sortProvidersByScorePoints();
        }

        foreach (static::$openVpnProviders as $openVpnProvider) {
            if ($openVpnProvider->isBadProvider()) {
                continue;
            }

            // Check if max_connections reached
            $maxSimultaneousConnections = $openVpnProvider->getMaxSimultaneousConnections();
            if (
                    $maxSimultaneousConnections !== -1
                &&  $openVpnProvider->countUsedOpenVpnConfigs() >= $maxSimultaneousConnections
            ) {
                continue;
            }

            $openVpnConfigs = $openVpnProvider->getAllOpenVpnConfigs();
            shuffle($openVpnConfigs);
            foreach ($openVpnConfigs as $openVpnConfig) {
                if (
                        $openVpnConfig->isInUse()
                    ||  $openVpnConfig->isBadCOnfig()
                ) {
                    continue;
                }

                return $openVpnConfig;
            }
        }

        return -1;
    }

    public static function hasFreeOpenVpnConfig()
    {
        foreach (static::$openVpnProviders as $openVpnProvider) {
            if ($openVpnProvider->isBadProvider()) {
                continue;
            }

            $allOpenVpnConfigsCount = $openVpnProvider->countAllOpenVpnConfigs();
            $usedOpenVpnConfigsCount = $openVpnProvider->countUsedOpenVpnConfigs();
            $badOpenVpnConfigsCount = $openVpnProvider->countBadOpenVpnConfigs();
            $maxSimultaneousConnections = $openVpnProvider->getMaxSimultaneousConnections();

            if ($usedOpenVpnConfigsCount + $badOpenVpnConfigsCount >= $allOpenVpnConfigsCount) {
                continue;
            }

            if (
                    $maxSimultaneousConnections === -1
                ||  $openVpnProvider->countUsedOpenVpnConfigs() < $maxSimultaneousConnections
            ) {
                return true;
            }
        }

        return false;
    }

    private static function getEverythingAboutOvpnFile($ovpnFile)
    {
        $ret = [
            'ovpnFile'             => $ovpnFile,
            'credentialsFile'      => false,
            'providerDir'          => false,
            'providerSettingsFile' => false,
            'providerName'         => false
        ];

        //---

        $credentialsFile = static::findCredentialsFileInDir(mbDirname($ovpnFile));
        if (! $credentialsFile) {
            // Not found in same dir. Check in parent dir
            $credentialsFile = static::findCredentialsFileInDir(mbDirname(mbDirname($ovpnFile)));;
        }
        if (! $credentialsFile) {
            // Not found in parent dir
            $credentialsFile = null;
        }

        $ret['credentialsFile'] = $credentialsFile;

        //---

        $providerDir = mbDirname($ovpnFile);
        $providerSettingsFile = static::findProviderSettingsFileInDir($providerDir);
        if (! $providerSettingsFile) {
            // Provider setting file not found in same dir. Check in parent dir
            $providerSettingsFile = static::findProviderSettingsFileInDir(mbDirname($providerDir));
            if ($providerSettingsFile) {
                // Provider settings file found in parent dir
                $providerDir = mbDirname($providerDir);
            } else {
                // Provider settings file not found
                $grepRegExp = '#^' . mbPregQuote($providerDir) . '.*?\.ovpn$#u';
                $ovpnFilesCountInDir = count(preg_grep($grepRegExp, static::$ovpnFilesList));
                if ($ovpnFilesCountInDir === 1) {
                    // Only one ovpn file in dir. Likely in provider's dir there are separate sub dirs for each ovpn file
                    $providerDir = mbDirname($providerDir);
                }
            }
        }

        $ret['providerDir'] = $providerDir;
        $ret['providerSettingsFile'] = $providerSettingsFile;
        $ret['providerName'] = mbBasename($providerDir);

        return $ret;
    }

    private static function findCredentialsFileInDir($dir)
    {
        $credentialsFileBasenameMutations = [
            static::credentialsFileBasename,
            mbFilename(static::credentialsFileBasename),
            static::credentialsFileBasename . '.' . mbExt(static::credentialsFileBasename)
        ];

        foreach ($credentialsFileBasenameMutations as $credentialsFileBasenameMutation) {
            $path = $dir . '/' . $credentialsFileBasenameMutation;
            if (file_exists($path)) {
                return $path;
            }
        }

        return false;
    }

    private static function findProviderSettingsFileInDir($dir)
    {
        $providerSettingsFileBasenameMutations = [
            static::providerSettingsFileBasename,
            mbFilename(static::providerSettingsFileBasename),
            static::providerSettingsFileBasename . '.' . mbExt(static::providerSettingsFileBasename)
        ];

        foreach ($providerSettingsFileBasenameMutations as $providerSettingsFileBasenameMutation) {
            $path = $dir . '/' . $providerSettingsFileBasenameMutation;
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return false;
    }

    private static function parseProviderSettingsFile($settingsFile)
    {
        $providerSettingsStr = @file_get_contents($settingsFile);
        $settingsRegExp = <<<PhpRegExp
                              #^([^=]+)=(.*)$#um
                              PhpRegExp;
        if (preg_match_all(trim($settingsRegExp), $providerSettingsStr, $matches) < 1) {
            return [];
        }

        $ret = [];
        for ($i = 0; $i < count($matches[0]); $i++) {
            $key   = mbTrim($matches[1][$i]);
            $value = mbTrim($matches[2][$i]);
            $ret[$key] = $value;
        }
        return $ret;
    }

    public static function sortProvidersByScorePoints()
    {
        uasort(static::$openVpnProviders, function($l, $r) {
            return $l->getAverageScorePoints() > $r->getAverageScorePoints()  ?  -1 : 1;
        });
    }
}

OpenVpnProvider::constructStatic();