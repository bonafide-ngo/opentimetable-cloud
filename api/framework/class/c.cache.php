<?

/**
 * Cache Class
 */
class Cache {


    // Constants
    const CC_MAX_VALIDITY   = 2592000; // Max 30 days - hard rule
    const CC_EXPIRY         = 'expiry';
    const CC_DATA           = 'data';

    // Properties
    static $mMemCacheD      = null;
    static $mObGzHandler    = false;
    static $mPath           = PATH_CACHE;
    static $mValidity       = Cache::CC_MAX_VALIDITY;

    /**
     * Initialise Cache
     *
     * @param array $pParams
     * @return void
     */
    public static function Initialise(array $pParams = array()) {
        self::$mValidity    = array_key_exists('validity', $pParams)        ? $pParams['validity']      : self::$mValidity;
        self::$mPath        = array_key_exists('path', $pParams)            ? $pParams['path']          : self::$mPath;
        self::$mObGzHandler = array_key_exists('ob_gzhandler', $pParams)    ? $pParams['ob_gzhandler']  : self::$mObGzHandler;

        // Enforce cahce validity max limit
        self::$mValidity = self::$mValidity > Cache::CC_MAX_VALIDITY ? Cache::CC_MAX_VALIDITY : self::$mValidity;

        // Set MemCacheD
        if (array_key_exists('memcached', $pParams) && $pParams['memcached']) {
            // Connect to MemCacheD 
            self::$mMemCacheD = new Memcached();
            self::$mMemCacheD->setOptions(array(
                // N.B. Compatible settings for ExtStore
                Memcached::OPT_COMPRESSION => true,
                Memcached::OPT_SERIALIZER => Memcached::SERIALIZER_PHP
            ));
            if (self::$mMemCacheD->addServers($pParams['memcached']))
                Log::Debug(__FILE__, __METHOD__, __LINE__, ["MemCacheD connection success", $pParams['memcached']]);
            else {
                self::$mMemCacheD = null;
                Log::Error(__FILE__, __METHOD__, __LINE__, ["MemCacheD connection failed", $pParams['memcached']], true);
            }
        } else
            Log::Debug(__FILE__, __METHOD__, __LINE__, $pParams);
    }

    /**
     * Send headers
     *
     * @param string $pContentType
     * @return void
     */
    public static function SendHeaders(string $pContentType = 'application/json; charset=utf-8') {
        // No caching for API responses
        header("Cache-Control: no-cache");

        // Set content type
        if ($pContentType)
            header("Content-type: $pContentType");

        // Attempt compressing output when LSWS or Apache not configured for dynamic compression
        if (self::$mObGzHandler)
            ob_start('ob_gzhandler');
    }

    /**
     * Minify
     *
     * @param string $pString
     * @return string
     */
    public static function Minify(string $pString): ?string {
        return preg_replace('/\s\s+/', ' ', $pString);
    }

    /**
     * Get the key for the cache
     *
     * @param array $pInput
     * @return string
     */
    public static function GetKey(mixed $pInput): string {
        $vInput = (array)$pInput;
        ksort($vInput);
        return Crypto::GetHash($vInput);
    }

    /**
     * Set data into cache
     *
     * @param mixed $pInput
     * @param mixed $pData
     * @param integer $pValidity
     * @param mixed $pForceFilesystem
     * @return boolean
     */
    public static function Set(mixed $pKey, mixed $pData, int $pValidity = 0, bool $pForceFilesystem = false): bool {
        if (!$pData)
            return false;

        $vOutcome = false;
        $vKey = self::GetKey($pKey);

        // Validity must be between 1 and self::$mValidity
        $vValidity = $pValidity ? min($pValidity, self::$mValidity) : self::$mValidity;
        $vExpiry = NOW + $vValidity;

        // Set cache
        $vCache = array();
        // Set the expiry
        $vCache[self::CC_EXPIRY] = $vExpiry;
        // Set data
        $vCache[self::CC_DATA] = $pData;

        // Serialize cache
        $vCache = serialize($vCache);

        if (self::$mMemCacheD && !$pForceFilesystem) {
            $vOutcome = self::$mMemCacheD->set($vKey, $vCache, $vValidity);
        } else {
            // Get cache filepath
            $vFilepath = self::$mPath . $vKey;
            if (is_dir(self::$mPath)) {
                // Save cache file
                $vFile = fopen($vFilepath, "w+");
                fwrite($vFile, $vCache);
                fclose($vFile);

                $vOutcome = true;
            }
        }

        if ($vOutcome) {
            Log::Debug(__FILE__, __METHOD__, __LINE__, ["Cache store success", $pKey, $vKey, $vValidity . ' s', strlen($vCache) . ' B']);
            return true;
        } else {
            Log::Error(__FILE__, __METHOD__, __LINE__, ["Cache store failed", $pKey, $vKey, $vValidity . ' s', strlen($vCache) . ' B'], true);
            return false;
        }
    }

    /**
     * Get data from cache
     * 
     * @param mixed $pKey
     * @param mixed $pForceFilesystem
     * @return mixed 
     */
    public static function Get(mixed $pKey, bool $pForceFilesystem = false): mixed {
        $vCache = null;
        $vKey = self::GetKey($pKey);

        if (self::$mMemCacheD && !$pForceFilesystem) {
            // Get cache
            $vCache = unserialize(self::$mMemCacheD->get($vKey));
        } else {
            // Get cache filepath
            $vFilepath = self::$mPath . $vKey;

            if (is_file($vFilepath))
                $vCache = unserialize(file_get_contents($vFilepath));
        }

        if (!$vCache) {
            Log::Debug(__FILE__, __METHOD__, __LINE__, ["Cache not found", $vKey]);
            return null;
        }

        // Cross check expiry
        if ($vCache[self::CC_EXPIRY] < NOW) {
            Log::Debug(__FILE__, __METHOD__, __LINE__, ["Cache expired", $vKey]);
            self::Delete($vKey);
            return null;
        }

        // Return data
        $vValidity = $vCache[self::CC_EXPIRY] - NOW;
        Log::Debug(__FILE__, __METHOD__, __LINE__, ["Cache found", $vKey, $vValidity . ' s']);

        return $vCache[self::CC_DATA];
    }

    /**
     * Delete a cache entry
     * 
     * @param mixed $pInput
     * @param mixed $pForceFilesystem
     * @return mixed 
     */
    public static function Delete(mixed $pKey, bool $pForceFilesystem = false): mixed {
        $vOutcome = false;
        $vKey = self::GetKey($pKey);

        if (self::$mMemCacheD && !$pForceFilesystem) {
            // Get cache key
            $vOutcome = self::$mMemCacheD->delete($vKey);
        } else {
            // Get cache filepath
            $vFilepath = self::$mPath . $vKey;

            $vOutcome = Util::SafeUnlink($vFilepath) ? true : false;
        }

        if ($vOutcome)
            Log::Debug(__FILE__, __METHOD__, __LINE__, ["Cache deleted: $vKey"]);

        return $vOutcome;
    }

    /**
     * Flush all cache entries
     * 
     * @param mixed $pIsFilesystem
     * @return void 
     */
    public static function Flush(bool $pIsFilesystem = false) {
        if (self::$mMemCacheD && !$pIsFilesystem)
            self::$mMemCacheD->flush();
        else {
            $vFilenames = scandir(self::$mPath, SCANDIR_SORT_NONE);
            if (!empty($vFilenames))
                foreach ($vFilenames as $vFilename) {
                    if (!in_array($vFilename, array('.', '..')))
                        unlink(self::$mPath . $vFilename);
                }
        }

        Log::Debug(__FILE__, __METHOD__, __LINE__, 'Cache flushed');
    }

    /**
     * Clean expired cache entries
     * 
     * @return void 
     */
    public static function Cleanup() {
        // MemCacheD self cleanups
        if (!self::$mMemCacheD) {
            $vFilenames = scandir(self::$mPath, SCANDIR_SORT_NONE);
            if (!empty($vFilenames))
                foreach ($vFilenames as $vFilename) {
                    // Get cache filepath
                    $vFilepath = self::$mPath . $vFilename;

                    if (is_file($vFilename)) {
                        $vCache = file_get_contents($vFilepath);
                        $vCache = unserialize($vCache);

                        if ($vCache[self::CC_EXPIRY] < NOW)
                            // Validity expired
                            unlink($vFilepath);
                    }
                }

            Log::Debug(__FILE__, __METHOD__, __LINE__, 'Cache cleanedup');
        }
    }

    /**
     * Fetch stats
     * 
     * @return mixed
     */
    public static function Stats(): mixed {
        if (!self::$mMemCacheD)
            return false;

        $vStats = self::$mMemCacheD->getStats();
        return $vStats ? $vStats[MEMCACHED[0][0] . ':' . MEMCACHED[0][1]] : false;
    }
}
