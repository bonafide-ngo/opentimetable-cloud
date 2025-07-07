<?

/**
 * Security Class
 */
class Security {

    const IPBLOCKLIST = 'IPBLOCKLIST';
    const IPBLOCK = 'IPBLOCK';
    const IPBLOCK_VALIDITY = 172800; // 48h
    const POSTRAW = 'request';

    protected static $mIpBlocklist = [];
    protected static $mDecodeInputUTF8 = false;

    /**
     * Initialize Security
     *
     * @param array $pParams
     * @return void
     */
    public static function Initialise(array $pParams = array()) {
        self::$mIpBlocklist         = array_key_exists('ip_blocklist', $pParams)        ? $pParams['ip_blocklist']      : self::$mIpBlocklist;
        self::$mDecodeInputUTF8     = array_key_exists('utf8_decode_input', $pParams)   ? $pParams['utf8_decode_input'] : self::$mDecodeInputUTF8;

        // Fix GLOBAL hack
        self::UnRegisterGlobals();

        // Sanitize all inputs
        self::SanitizeAllInputs();
    }

    /**
     * Initialise the ip blocklist
     *
     * @return void
     */
    public static function InitIpBlocklist() {
        // Get cache
        $vBlocklist = \Cache::Get([__CLASS__, self::IPBLOCKLIST]);
        // Merge blocklists
        self::$mIpBlocklist = array_merge((array) self::$mIpBlocklist, $vBlocklist ? (array) $vBlocklist : []);
    }

    /**
     * Fetch the remote ip blocklist
     * Called by cron job
     *
     * @param boolean $pGetLast
     * @return array
     */
    public static function FetchIpBlocklist(bool $pGetLast = false): array {
        // Set url
        $vUrl = $pGetLast ? \Util::ParseString(APP_IP_BLOCKLIST_URL_GETLAST, [APP_IP_BLOCKLIST_VALIDITY]) : APP_IP_BLOCKLIST_URL_GETALL;
        // Fetch blocklist
        $vBlocklist = file_get_contents($vUrl);

        // Attempt retry
        if (!$vBlocklist) {
            // Set initial backoff
            $vBackoff = 3;
            // Set safety timeout
            $vSafetyTimeout = NOW + ini_get('max_execution_time');
            // Test retry till timeout
            while (!$vBlocklist && time() + $vBackoff < $vSafetyTimeout) {
                // Let's wait and try again
                sleep($vBackoff);
                // Fetch blocklist
                $vBlocklist = file_get_contents($vUrl);
                // Extend backoff
                $vBackoff = pow($vBackoff, 2);
            }
        }

        if (!$vBlocklist) {
            // Something is wrong, let's report it
            // The cache contingency will cover for the missed fetch
            Log::Error(__FILE__, __METHOD__, __LINE__, ['Error fetching the remote IP blocklist', $vUrl], true);
            return [];
        }
        // Parse blocklist into array
        $vBlocklist = explode("\n", $vBlocklist);

        // Set cache for contingency
        \Cache::Set([__CLASS__, self::IPBLOCKLIST], $vBlocklist);
        return $vBlocklist;
    }

    /**
     * Query information about the given ip
     * https://www.blocklist.de/en/api.html
     *
     * @param string $pIp
     * @return mixed
     */
    public static function QueryIpInformation(string $pIp, string $pFormat = 'text'): mixed {
        // Fetch remote blocklist
        $vUrl = \Util::ParseString(APP_IP_BLOCKLIST_URL_INFO, [APP_IP_BLOCKLIST_SERVER_ID, APP_IP_BLOCKLIST_SERVER_KEY, $pIp, $pFormat]);
        return file_get_contents($vUrl);
    }

    /**
     * Query blocklists about the given ip
     * https://mxtoolbox.com/user/api
     *
     * @param string $pIp
     * @return mixed
     */
    public static function QueryIpBlocklists(string $pIp): mixed {
        // Set url
        $vUrl = \Util::ParseString(APP_MXTOOLBOX_URL, [$pIp]);
        // Set authentication
        // https: //stackoverflow.com/questions/30628361/php-basic-auth-file-get-contents
        $vHeaders = stream_context_create([
            "http" => [
                "header" => "Authorization: " . APP_MXTOOLBOX_KEY
            ]
        ]);
        // Fetch blocklists
        return json_decode(file_get_contents($vUrl, false, $vHeaders));
    }

    /**
     * Set an IP block
     *
     * @param string $pIp
     * @return bool
     */
    public static function SetIpBlock(string $pIp): bool {
        // Set if not in blocklist
        if (!in_array($pIp, self::$mIpBlocklist))
            \Cache::Set([__CLASS__, self::IPBLOCK, $pIp], $pIp, self::IPBLOCK_VALIDITY);

        return true;
    }

    /**
     * Delete an IP block
     *
     * @param string $pIp
     * @return bool
     */
    public static function DeleteIpBlock(string $pIp): bool {
        if (in_array($pIp, self::$mIpBlocklist))
            return false;

        // Delete cache if not in blocklsit
        \Cache::Delete([__CLASS__, self::IPBLOCK, $pIp]);
        return true;
    }

    /**
     * Check if an IP is blocked
     *
     * @param string|null $pIp
     * @return boolean
     */
    public static function IsIpBlocked(?string $pIp = null): bool {
        $vIp = $pIp ? $pIp : Util::GetIP();
        if (!$vIp)
            return true;

        // Check the IP is in the blocklist or soft blocked
        return in_array($vIp, self::$mIpBlocklist) || \Cache::Get([__CLASS__, self::IPBLOCK, $pIp]) ? true : false;
    }

    /**
     * Sanitize all inputs
     *
     * @return void
     */
    public static function SanitizeAllInputs() {
        // Check if raw post exists first
        // Raw post request can be read once only
        // N.B. The Endpoint URL must end with the trailing slash if a folder, else an implicit redirect applies losing the raw post data
        $_POSTRAW = file_get_contents('php://input');
        $_POSTRAW = $_POSTRAW ? [self::POSTRAW => $_POSTRAW] : array();

        if (isset($_GET))
            array_walk($_GET, array('Security', 'SanitizeInput'));
        if (isset($_POST))
            array_walk($_POST, array('Security', 'SanitizeInput'));
        if (isset($_COOKIE))
            array_walk($_COOKIE, array('Security', 'SanitizeInput'));
        if (isset($_POSTRAW))
            array_walk($_POSTRAW, array('Security', 'SanitizeInput'));

        // Avoid DOS attack: $_REQUEST has not to include $_COOKIE
        $_REQUEST = array_merge($_GET, $_POST, $_POSTRAW);
    }

    /**
     * Sanitize input
     *
     * @param mixed $pInput
     * @param string $pKey
     * @param string $pEscapeFunction
     * @return void
     */
    public static function SanitizeInput(mixed &$pInput, string $pKey, string $pEscapeFunction = '') {
        if (ini_get('magic_quotes_runtime')) {
            if (is_array($pInput) || is_object($pInput))
                array_walk($pInput, array('Security', 'SanitizeInput'), $pEscapeFunction);
            else
                $pInput = stripslashes($pInput);
        }

        if (is_array($pInput) || is_object($pInput))
            array_walk($pInput, array('Security', 'SanitizeInput'), $pEscapeFunction);
        else {
            // Cast to string
            $pInput = strval($pInput);

            // Strip HTML tags
            $pInput = strip_tags($pInput);

            // Trim start-end spaces
            $pInput = trim($pInput);

            // Remove duplicate spaces
            $pInput = preg_replace('/\s+/', ' ', $pInput);

            // Escape by function
            if (function_exists($pEscapeFunction))
                $pInput = call_user_func($pEscapeFunction, $pInput);

            if (self::$mDecodeInputUTF8)
                $pInput = utf8_decode($pInput);
        }
    }

    /**
     * Emulate PHP settings register_globals = Off
     * Warning: to be called after session_start()
     *
     * @return void
     */
    public static function UnRegisterGlobals() {
        if (!ini_get('register_globals'))
            return;

        // Check for GLOBAL hack
        if (
            isset($_REQUEST['GLOBALS'])
            || isset($_FILES['GLOBALS'])
        )
            // Silent exit, no screen output
            exit();

        $vInputs = array_merge(
            $_GET,
            $_POST,
            $_COOKIE,
            $_SERVER,
            $_ENV,
            $_FILES,
            (isset($_SESSION) && is_array($_SESSION)) ? $_SESSION : array()
        );

        foreach ($vInputs as $key => $value)
            if (isset($GLOBALS[$key]))
                unset($GLOBALS[$key]);
    }
}
