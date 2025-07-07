<?

/**
 * Benchmark Class
 */
class Benchmark {

    protected static $mTimer = [];
    protected static $mMemory = [];

    /**
     * 
     * @param integer $pMemory
     * @return string
     */
    public static function FormatMemory(int $pMemory): string {
        if ($pMemory < 1024)
            $vMemory = $pMemory . ' B';
        elseif ($pMemory < 1048576)
            $vMemory = round($pMemory / 1024) . ' KB';
        elseif ($pMemory < 1073741824)
            $vMemory = round($pMemory / 1024 / 1024, 1) . ' MB';
        else
            $vMemory = round($pMemory / 1024 / 1024 / 1024, 1) . ' GB';

        return $vMemory;
    }

    /**
     * 
     * @return float
     */
    public static function Start(): ?float {
        if (!DEBUG)
            return null;

        list($vUsec, $vSec) = explode(' ', microtime());
        return self::$mTimer['start'] = (float)($vUsec) + (float)($vSec);
    }

    /**
     * 
     * @return float
     */
    public static function GetStart(): ?float {
        if (!DEBUG)
            return null;

        if (isset(self::$mTimer['start']))
            return self::$mTimer['start'];
        else
            return self::Start();
    }

    /**
     * 
     * @return float
     */
    public static function Stop(): ?float {
        if (!DEBUG)
            return null;

        list($vUsec, $vSec) = explode(' ', microtime());
        return self::$mTimer['stop'] = (float)($vUsec) + (float)($vSec);
    }

    /**
     * 
     * @return float
     */
    public static function GetStop(): ?float {
        if (!DEBUG)
            return null;

        if (isset(self::$mTimer['stop']))
            return self::$mTimer['stop'];
        else
            return self::Stop();
    }

    /**
     * 
     * @return float
     */
    public static function Lap(): ?float {
        if (!DEBUG)
            return null;

        list($vUsec, $vSec) = explode(' ', microtime());
        return self::$mTimer['lap'] = (float)($vUsec) + (float)($vSec);
    }

    /**
     * 
     * @return float
     */
    public static function GetLap(): ?float {
        if (!DEBUG)
            return null;

        if (isset(self::$mTimer['lap']))
            return self::$mTimer['lap'];
        else
            return self::Lap();
    }

    /**
     * 
     * @return float
     */
    public static function DiffLap(): ?float {
        if (!DEBUG)
            return null;

        $vChrono_Start  = self::GetLap();

        list($vUsec, $vSec) = explode(' ', microtime());
        $vChrono_End = (float)($vUsec) + (float)($vSec);

        return $vChrono_End - $vChrono_Start;
    }

    /**
     * 
     * @param integer $pTimeout
     * @return boolean
     */
    public static function IsOvertime(int $pTimeout = null): ?bool {
        if (!DEBUG)
            return null;

        $vTimeout = $pTimeout ? $pTimeout : round(ini_get('max_execution_time') * 0.9);

        if (self::Stop() - self::GetStart() > $vTimeout)
            return true;
        else
            return false;
    }

    /**
     * Undocumented function
     *
     * @param boolean $pIncludeScript
     * @return string
     */
    public static function Stats($pIncludeScript = true): string {
        $vLog = '';

        // Script
        if ($pIncludeScript) {
            self::$mMemory['MemScript_Current'] = memory_get_usage(false);
            self::$mMemory['MemScript_Limit'] = substr(ini_get('memory_limit'), 0, -1) * 1048576; // Bytes
            self::$mMemory['MemScript_CurrentPercent'] = round(self::$mMemory['MemScript_Current'] * 100 / self::$mMemory['MemScript_Limit']);
            self::$mMemory['MemScript_Peak'] = memory_get_peak_usage(false);
            self::$mMemory['MemScript_PeakPercent'] = round(self::$mMemory['MemScript_Peak'] * 100 / self::$mMemory['MemScript_Limit']);

            $vLog .=      'Script Time: ' . round(self::GetStop() - self::GetStart(), 3) . 's';
            $vLog .= NL . 'Script Memory, Current: ' . self::FormatMemory(self::$mMemory['MemScript_Current']) . ' (' . self::$mMemory['MemScript_CurrentPercent'] . '%)';
            $vLog .= NL . 'Script Memory, Peak: ' . self::FormatMemory(self::$mMemory['MemScript_Peak']) . ' (' . self::$mMemory['MemScript_PeakPercent'] . '%)';
            $vLog .= NL . 'Script Memory, Limit: ' . self::FormatMemory(self::$mMemory['MemScript_Limit']);
        }

        // System
        self::$mMemory['MemSystem_Current'] = memory_get_usage(true);
        self::$mMemory['MemSystem_Peak'] = memory_get_peak_usage(true);

        $vLog .= ($pIncludeScript ? NL : '') . 'System Memory, Current: ' . self::FormatMemory(self::$mMemory['MemSystem_Current']);
        $vLog .= NL . 'System Memory, Peak: ' . self::FormatMemory(self::$mMemory['MemSystem_Peak']);

        // Cache
        $vCacheStats = Cache::Stats();
        if ($vCacheStats) {
            self::$mMemory['MemCacheD_MemoryPercent'] = round($vCacheStats['bytes'] * 100 / $vCacheStats['limit_maxbytes']);
            self::$mMemory['MemCacheD_ExtStorePercent'] = round($vCacheStats['extstore_bytes_used'] * 100 / $vCacheStats['extstore_limit_maxbytes']);

            $vLog .= NL . 'MemCacheD Memory, Current: ' . self::FormatMemory($vCacheStats['bytes']) . ' (' . self::$mMemory['MemCacheD_MemoryPercent'] . '%)';
            $vLog .= NL . 'MemCacheD Memory, Limit: ' . self::FormatMemory($vCacheStats['limit_maxbytes']);

            $vLog .= NL . 'MemCacheD ExtStore, Current: ' . self::FormatMemory($vCacheStats['extstore_bytes_used']) . ' (' . self::$mMemory['MemCacheD_ExtStorePercent'] . '%)';
            $vLog .= NL . 'MemCacheD ExtStore, Limit: ' . self::FormatMemory($vCacheStats['extstore_limit_maxbytes']);
        }

        return $vLog;
    }

    /**
     * Undocumented function
     *
     * @param integer $pByteIn
     * @param integer $pByteOut
     * @param string|null $pMethod
     * @return void
     */
    public static function Log(int $pByteIn = 0, int $pByteOut = 0, string $pMethod = null) {
        if (DEBUG)
            Log::Debug(__FILE__, __METHOD__, __LINE__, self::Stats());

        $vIP = Util::GetIP();
        if (!$vIP)
            return;

        // Do not log server-to-server traffic in production
        if (!DEBUG && $vIP == $_SERVER['SERVER_ADDR'])
            return;

        // Do not log traffic for event-source requests
        if (Util::GetAccept() == 'text/event-stream')
            return;

        // Set bindings
        $vBindVars = [
            'trf_ip' => $vIP,
            'trf_session' => Crypto::GetHash(session_id(), false, 'md5'),
            'trf_byte_in' => $pByteIn,
            'trf_byte_out' => $pByteOut,
            'trf_ms' => intval(round((self::GetStop() - self::GetStart()) * 1000)),
            'trf_method' => $pMethod ? mb_substr($pMethod, 0, 128) : null // Limit trf_method to VARCHAR(128)
        ];
        \OSQL::_Query(PATH_SQL_FRAMEWORK, 'insert_traffic', $vBindVars);
    }
}
