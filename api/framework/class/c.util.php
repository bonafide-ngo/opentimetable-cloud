<?

/**
 * Utility Class
 */
class Util {

    protected static $mReverseProxy     = null;
    protected static $mURL              = '';
    protected static $mConfig           = null;
    protected static $mSessionName      = '';

    /**
     * Initialize Util
     *
     * @param array $pParams
     * @return void
     */
    public static function Initialise(array $pParams = array()) {
        self::$mReverseProxy        = array_key_exists('reverse_proxy', $pParams)       ? $pParams['reverse_proxy']     : self::$mReverseProxy;
        self::$mURL                 = array_key_exists('url', $pParams)                 ? $pParams['url']               : self::$mURL;
        self::$mSessionName         = array_key_exists('session_name', $pParams)        ? $pParams['session_name']      : self::$mSessionName;
        // Cannot log because the Log object has not been initialized yet
    }

    /**
     * Start a session
     *
     * @param string|null $pSID
     * @return void
     */
    public static function StartSession(?string $pSID = null) {
        // Set Session Name
        session_name($pSID ? $pSID : self::$mSessionName);
        // Start Session
        session_start();
    }

    /**
     * Clear parameters from Session
     *
     * @param array $pKeys
     * @param null|string $pClass
     * @return void
     */
    public static function ClearSession(array $pKeys = array(), ?string $pClass = null) {
        // Get Session index
        $vIndex = $pClass ? $pClass : __CLASS__;

        if (empty($pKeys))
            unset($_SESSION[$vIndex]);
        else {
            foreach ($pKeys as $pKey)
                unset($_SESSION[$vIndex][$pKey]);
        }
    }

    /**
     * Check a parameter is in Session
     *
     * @param string $pKey
     * @param null|string $pClass
     * @return boolean
     */
    public static function IsInSession(string $pKey, ?string $pClass = null): bool {
        // Get Session index
        $vIndex = $pClass ? $pClass : __CLASS__;

        if (isset($_SESSION[$vIndex][$pKey]))
            return true;
        else
            return false;
    }

    /**
     * Get a parameter from Session
     *
     * @param string $pKey
     * @param null|string $pClass
     * @return mixed
     */
    public static function GetFromSession(string $pKey, ?string $pClass = null): mixed {
        // Get Session index
        $vIndex = $pClass ? $pClass : __CLASS__;

        if (self::IsInSession($pKey, $pClass))
            return $_SESSION[$vIndex][$pKey];
        else
            return false;
    }

    /**
     * Set in Session
     *
     * @param string $pKey
     * @param mixed $pValue
     * @param null|string $pClass
     * @return void
     */
    public static function SetInSession(string $pKey, mixed $pValue, ?string $pClass = null) {
        // Get Session index
        $vIndex = $pClass ? $pClass : __CLASS__;

        $_SESSION[$vIndex][$pKey] = $pValue;
    }

    /**
     * Unset from Session
     *
     * @param string $pKey
     * @param null|string $pClass
     * @return void
     */
    public static function UnsetFromSession(string $pKey, ?string $pClass = null) {
        // Get Session index
        $vIndex = $pClass ? $pClass : __CLASS__;

        $_SESSION[$vIndex][$pKey] = null;
        unset($_SESSION[$vIndex][$pKey]);
    }

    /**
     * Get and validate IP address
     *
     * @return string
     */
    public static function GetIP(): ?string {
        $vHeaders = apache_request_headers();
        switch (self::$mReverseProxy) {
            case 'X-Forwarded-For':
                // Get first IP forwarded for
                $vIPs = explode(',', $vHeaders[self::$mReverseProxy]);
                $vIP = trim($vIPs[0]);
                break;
            case 'true-client-ip':
                $vIP = trim($vHeaders[self::$mReverseProxy]);
                break;
            default:
                $vIP = $_SERVER['REMOTE_ADDR'];
                break;
        }

        return ip2long($vIP) ? $vIP : (DEBUG ? '127.0.0.1' : null);
    }

    /**
     * Get and validate the Server IP address
     *
     * @return string
     */
    public static function GetServerIP(): ?string {
        $vIP = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];

        return ip2long($vIP) ? $vIP : (DEBUG ? '127.0.0.1' : null);
    }

    /**
     * Get User Agent
     *
     * @return string
     */
    public static function GetUserAgent(): ?string {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }

    /**
     * Get Accept
     * 
     * @return string
     */
    public static function GetAccept(): ?string {
        return isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null;
    }

    /**
     * Explode case insensitive
     *
     * @param string $pSeparator
     * @param string $pString
     * @return array|boolean
     */
    public static function Iexplode(string $pSeparator, string $pString): array|bool {
        $vInsensitiveSeparator = strtolower($pSeparator);
        $vInsensitiveString = str_ireplace($pSeparator, $vInsensitiveSeparator, $pString);

        return explode($vInsensitiveSeparator, $vInsensitiveString);
    }

    /**
     * Get cookie safely
     *
     * @param string $pCookieConfigPath
     * @return string
     */
    public static function GetCookie(string $pCookieConfigPath): string {
        $vName = \Util::GetConfig($pCookieConfigPath . '.name');
        return isset($_COOKIE[$vName]) ? $_COOKIE[$vName] : '';
    }

    /**
     * Set cookie safely
     *
     * @param string $pCookieConfigPath
     * @param string|null $pValue
     * @param bool $pRemove
     * @param bool $pForceSession
     * @return void
     */
    public static function SetCookie(string $pCookieConfigPath, ?string $pValue = null, bool $pRemove = false, bool $pForceSession = false, array $pOverrideCookieOptions = array()) {
        // Extend cookie options
        $vCookieOptions = \Util::Extend(\Util::GetConfig('cookie.option'), \Util::GetConfig($pCookieConfigPath));
        // Merge override
        $vCookieOptions = array_merge($vCookieOptions, $pOverrideCookieOptions);

        // Set expiry as timestamp for PHP
        if ($pRemove)
            $vCookieOptions['expires'] = 1;
        else if ($pForceSession)
            $vCookieOptions['expires'] = 0;
        else
            $vCookieOptions['expires'] = $vCookieOptions['expires'] ? strtotime("+" . $vCookieOptions['expires'] . " seconds") : 0;

        // setcookie is very strict in PHP, let's clear the option name because unconvetional
        $vCookieName = $vCookieOptions['name'];
        unset($vCookieOptions['name']);

        // Set cookie
        setcookie($vCookieName, is_null($pValue) ? '' : $pValue, $vCookieOptions);
    }

    /**
     * Extend cookies
     *
     * @param array $pCookieConfigPaths
     * @return void
     */
    public static function ExtendCookies(array $pCookieConfigPaths = array()) {
        foreach ($pCookieConfigPaths as $vCookieConfigPath) {
            $vCookieValue = self::GetCookie($vCookieConfigPath);
            if ($vCookieValue)
                // Extend
                self::SetCookie($vCookieConfigPath, $vCookieValue);
        }
    }

    /**
     * Get transmission Protocol
     *
     * @return string
     */
    public static function GetProtocol(): string {
        return !isset($_SERVER['HTTPS'])
            ? 'http://'
            : (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off'
                ? 'http://'
                : 'https://');
    }

    /**
     * Get URL
     *
     * @param string $pURLBase
     * @param array $pParams
     * @param boolean $pHTMLEntities
     * @return string
     */
    public static function CreateURL(string $pURLBase, array $pParams = array(), bool $pHTMLEntities = true): string {
        $vRequests = array();

        // Params
        foreach ($pParams as $vKey => $vValue)
            if (is_array($vValue)) {
                foreach ($vValue as $v2Key => $v2Value)
                    $vRequests[] = urlencode($vKey) . '[' . urlencode($v2Key) . ']=' . urlencode($v2Value);
            } else
                $vRequests[] = urlencode($vKey) . '=' . urlencode($vValue);

        $vURL  = $pURLBase;
        $vURL .= strpos($vURL, '?') === false
            ? '?'
            : (strpos($vURL, '=') === false
                ? ''
                : ($pHTMLEntities ? '&amp;' : '&'));
        $vURL .= !empty($vRequests) ? implode($pHTMLEntities ? '&amp;' : '&', $vRequests) : '';

        return $vURL;
    }

    /**
     * Get URI
     *
     * @return string
     */
    public static function GetURI(): string {
        if (self::$mReverseProxy)
            return self::$mURL;
        else
            return self::GetProtocol() . $_SERVER['HTTP_HOST'] . str_replace('\\', '/', rtrim(dirname($_SERVER['PHP_SELF']), '/\\')) . '/';
    }

    /**
     * Get URL with additional parameters
     *
     * @param array $pParamsIn
     * @param array $pParamsOut
     * @param boolean $pHTMLEntities
     * @return string
     */
    public static function GetURL(array $pParamsIn = array(), array $pParamsOut = array(), bool $pHTMLEntities = true): string {
        $vRequests = array();

        // Build standard Request URI
        foreach ($_REQUEST as $vKey => $vValue) {
            if (
                !array_key_exists($vKey, $pParamsIn)
                && !in_array($vKey, $pParamsOut)
            ) {
                if (is_array($vValue)) {
                    foreach ($vValue as $v2Key => $v2Value)
                        $vRequests[] = urlencode($vKey) . '[' . urlencode($v2Key) . ']=' . urlencode($v2Value);
                } else
                    $vRequests[] = urlencode($vKey) . '=' . urlencode($vValue);
            }
        }

        // Additional Params
        foreach ($pParamsIn as $vKey => $vValue)
            if (is_array($vValue)) {
                foreach ($vValue as $v2Key => $v2Value)
                    $vRequests[] = urlencode($vKey) . '[' . urlencode($v2Key) . ']=' . urlencode($v2Value);
            } else
                $vRequests[] = urlencode($vKey) . '=' . urlencode($vValue);

        if (empty($vRequests))
            return self::GetURI();
        else
            return self::GetURI() . '?' . implode($pHTMLEntities ? '&amp;' : '&', $vRequests);
    }

    /**
     * Redirect
     *
     * @param string $pURI
     * @param array $pParams
     * @return void
     */
    public static function Redirect(string $pURI = null, array $pParams = array()) {
        // Initialize
        $vURI = $pURI ? $pURI : self::GetURI();
        $vRequests = array();

        foreach ($pParams as $vKey => $vValue)
            if (is_array($vValue)) {
                foreach ($vValue as $v2Key => $v2Value)
                    $vRequests[] = urlencode($vKey) . '[' . urlencode($v2Key) . ']=' . urlencode($v2Value);
            } else
                $vRequests[] = urlencode($vKey) . '=' . urlencode($vValue);

        $vURL  = $vURI;
        $vURL .= strpos($vURL, '?') === false
            ? '?'
            : (strpos($vURL, '=') === false
                ? ''
                : '&');
        $vURL .= !empty($vRequests) ? implode('&', $vRequests) : '';

        header('Location: ' . $vURL);
        exit;
    }

    /**
     * Check if a Request has been attempted
     *
     * @param string $pRequestName
     * @param boolean $pRequestValue
     * @return mixed
     */
    public static function IsRequest(string $pRequestName, bool $pRequestValue = false): mixed {
        if (isset($_REQUEST[$pRequestName]))
            return $pRequestValue ? $_REQUEST[$pRequestName] : true;
        else
            return $pRequestValue ? null : false;
    }

    /**
     * Check if is Odd
     *
     * @param integer $pNumber
     * @return boolean
     */
    public static function IsOdd(int $pNumber): bool {
        // Force integer casting
        $vNumber = (int)($pNumber);

        if ($vNumber % 2)
            return true;
        else
            return false;
    }

    /**
     * Cleanup text for HTML rendering
     *
     * @param string $pText
     * @param string $pTagsAllowed
     * @param boolean $pEmbed4Javascript
     * @return string
     */
    public static function RenderHTML(string $pText, string $pTagsAllowed = '', bool $pEmbed4Javascript = false): string {
        $vText = $pText;

        $vText = strip_tags($vText, $pTagsAllowed);
        $vText = stripcslashes($vText);
        $vText = htmlentities($vText);
        $vText = $pEmbed4Javascript ? addslashes($vText) : $vText;

        return $vText;
    }

    /**
     * 
     * Parse a string
     *
     * @param string $pString
     * @param array $pValues
     * @param string $pPreMarker
     * @return string
     */
    public static function ParseString(string $pString, array $pValues = array(), string $pPreMarker = '{', string $pPostMarker = '}'): string {
        if (empty($pValues))
            return $pString;

        foreach ($pValues as $vIndex => $vValue)
            $pString = str_replace($pPreMarker . $vIndex . $pPostMarker, $vValue, $pString);

        return $pString;
    }

    /**
     * Unlink a file safely
     *
     * @param string $pFilepath
     * @return integer
     */
    public static function SafeUnlink(string $pFilepath): int {
        $vDeleteCount = 0;
        // Get the list of files matching the exact or wildcard pattern
        $vFiles = glob($pFilepath);
        if (!empty($vFiles))
            foreach ($vFiles as $vFile) {
                if (is_file($vFile))
                    if (unlink($vFile))
                        $vDeleteCount++;
            }

        return $vDeleteCount;
    }

    /**
     * Run a cURL call
     * N.B. Set $pTimeout = 0 for Fire And Forget 
     *
     * @param string $pURL
     * @param mixed $pPOST
     * @param array $pHeaders
     * @param [type] $pHttpVersion
     * @param integer $pTimeout
     * @return mixed
     */
    public static function cURL(string $pURL, mixed $pPOST = null, array $pHeaders = array(), int $pHttpVersion = CURL_HTTP_VERSION_NONE, int $pTimeout = 3): mixed {
        $cURL = curl_init();
        curl_setopt($cURL, CURLOPT_URL, $pURL);
        curl_setopt($cURL, CURLOPT_HTTPHEADER, $pHeaders);
        // Return, do not output
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);

        if (
            is_array($pPOST)
            && !empty($pPOST)
        ) {
            $vPOST = array();
            foreach ($pPOST as $vKey => $vValue)
                $vPOST[] = urlencode($vKey) . '=' . urlencode($vValue);

            curl_setopt($cURL, CURLOPT_POST, 1);
            curl_setopt($cURL, CURLOPT_POSTFIELDS, implode('&', $vPOST));
        } else if (
            !is_array($pPOST)
            && $pPOST
        ) {
            curl_setopt($cURL, CURLOPT_POST, 1);
            curl_setopt($cURL, CURLOPT_POSTFIELDS, $pPOST);
        }

        // Handle fire and forget
        curl_setopt($cURL, CURLOPT_TIMEOUT, $pTimeout ? $pTimeout : 1);

        // https://curl.se/libcurl/c/CURLOPT_HTTP_VERSION.html
        curl_setopt($cURL, CURLOPT_HTTP_VERSION, $pHttpVersion);

        // Handle verbose output
        $vVerboseHandler =  fopen('php://temp', 'w+');
        curl_setopt($cURL, CURLOPT_VERBOSE, DEBUG);
        curl_setopt($cURL, CURLOPT_STDERR, $vVerboseHandler);

        // Handle response headers
        $vResponseHeaders = array();
        curl_setopt(
            $cURL,
            CURLOPT_HEADERFUNCTION,
            function ($pCurl, $pHeader) use (&$vResponseHeaders) {
                // Split headers into key/value
                $vHeader = explode(':', $pHeader, 2);
                // Ignore invalid headers
                if (count($vHeader) == 2)
                    $vResponseHeaders[strtolower(trim($vHeader[0]))] = trim($vHeader[1]);

                return strlen($pHeader);
            }
        );

        // Run cURL
        $vResult = curl_exec($cURL);

        // Set response
        $vResponse = new \stdClass();
        $vResponse->body = $vResult;
        $vResponse->headers = $vResponseHeaders;

        if ($pTimeout) {
            // Get verbose output
            rewind($vVerboseHandler);
            $vVerboseLog = htmlspecialchars(stream_get_contents($vVerboseHandler));

            if ($vResult)
                // All good, log for debugging only
                \Log::Debug(__FILE__, __METHOD__, __LINE__, ['cURL Response', $vResponse, $vVerboseLog], true);
            else
                // Do not log an error but carry on passign the result to the parent handler 
                Log::Debug(__FILE__, __METHOD__, __LINE__, ['cURL Error[' . curl_errno($cURL) . '] >> ' . curl_error($cURL), $vResponse, $vVerboseLog], true);
        }

        // Close cURL
        curl_close($cURL);

        // Do not throw exception, handle the issue by checking the response
        return $vResponse;
    }

    /**
     * Emulate JQuery.extend to merge objects
     *
     * @param mixed $pItem1
     * @param mixed $pItem2
     * @return mixed
     */
    public static function Extend(mixed $pItem1, mixed $pItem2): mixed {
        $vTypeItem1 = gettype($pItem1);
        $vTypeItem2 = gettype($pItem2);

        if ($vTypeItem1 == $vTypeItem2  && $vTypeItem1 == 'array')
            return array_merge($pItem1, $pItem2);
        else if ($vTypeItem1 == $vTypeItem2 && $vTypeItem1 == 'object')
            return (object)array_merge((array)$pItem1, (array)$pItem2);
        else {
            \Log::Error(__FILE__, __METHOD__, __LINE__, "Invalid type or type mismatch [$vTypeItem1, $vTypeItem2]", true);
            throw new Exception();
        }
    }

    /**
     * Emulate PrintR
     *
     * @param mixed $pData
     * @param boolean $pDie
     * @return void
     */
    public static function PrintR(mixed $pData, bool $pDie = true) {
        echo '<pre>' . print_r($pData, true) . '</pre>';

        if ($pDie)
            die();
    }

    /**
     * Decode accents from HTML to plain text
     *
     * @param string $pHTML
     * @return string
     */
    public static function DecodeAccents(string $pHTML): ?string {
        $vSearch = array(
            '/\&agrave\;/is',
            '/\&egrave\;/is',
            '/\&igrave\;/is',
            '/\&ograve\;/is',
            '/\&ugrave\;/is'
        );

        $vReplace = array(
            'a\'',
            'e\'',
            'i\'',
            'o\'',
            'u\''
        );

        return preg_replace($vSearch, $vReplace, strip_tags($pHTML));
    }

    /**
     * Parse Template
     *
     * @param string $pWrapperPath
     * @param string $pBodyPath
     * @param array $pBodyParams
     * @param string $pDelimiterLeft
     * @return string
     */
    public static function ParseTemplate(string $pWrapperPath, string $pBodyPath, array $pBodyParams, string $pDelimiterLeft = '{', string $pDelimiterRight = '}'): ?string {
        // Initialize
        $vOutput = null;

        if (empty($pBodyParams))
            return $vOutput;

        // Init the bind variables
        $vBindKeys = array();
        $vBindValues = array();

        // Set version
        $vBindKeys[] = $pDelimiterLeft . 'txt_version' . $pDelimiterRight;
        $vBindValues[] = Util::GetConfig('version');

        // Set title
        $vBindKeys[] = $pDelimiterLeft . 'txt_title' . $pDelimiterRight;
        $vBindValues[] = Lang::Get('instance.i-title');

        // Set license
        $vBindKeys[] = $pDelimiterLeft . 'txt_license' . $pDelimiterRight;
        $vBindValues[] = Util::GetConfig('license');

        // Set footer
        $vBindKeys[] = $pDelimiterLeft . 'txt_footer' . $pDelimiterRight;
        $vBindValues[] = Lang::Get('email.footer');

        // Set logo
        $vBindKeys[] = $pDelimiterLeft . 'src_logo' . $pDelimiterRight;
        $vBindValues[] = Util::GetConfig('logo.invariant');

        // Append and override bindings
        foreach ($pBodyParams as $vKey => $vValue) {
            $vBindKeys[] = $pDelimiterLeft . $vKey . $pDelimiterRight;
            $vBindValues[] = $vValue;
        }

        // Parse the body
        if ($pBodyPath) {
            $vOutput = str_replace($vBindKeys, $vBindValues, file_get_contents($pBodyPath));
        }

        // Parse the wrapper
        if ($pWrapperPath) {
            // Append the body
            $vBindKeys[] = $pDelimiterLeft . 'body' . $pDelimiterRight;
            $vBindValues[] = $vOutput;

            $vOutput = str_replace($vBindKeys, $vBindValues, file_get_contents($pWrapperPath));
        }

        return $vOutput;
    }

    /**
     * Load a config file
     *
     * @param string $pPath
     * @return void
     */
    public static function LoadConfig(string $pPath) {
        self::$mConfig = json_decode(file_get_contents($pPath), true);
        if (empty(self::$mConfig))
            Log::Error(__FILE__, __METHOD__, __LINE__, "Missing or invalid configuration at $pPath", true);
    }

    /**
     * Get a config item
     *
     * @param string $pKey
     * @param array $pParams
     * @return mixed
     */
    public static function GetConfig(string $pKey,  array $pParams = array()): mixed {
        $vKeys = explode('.', $pKey);
        $vConfig = self::$mConfig;

        // Loop & find
        foreach ($vKeys as $vKey) {
            if ($vConfig[$vKey])
                $vConfig = !empty($pParams) && is_string($vConfig[$vKey]) ? self::ParseString($vConfig[$vKey], $pParams) : $vConfig[$vKey];
            else {
                $vConfig = null;
                break;
            }
        }

        return $vConfig;
    }

    /**
     * Get a country from an IP address
     *
     * @param string|null $pIP
     * @return object|null
     */
    public static function GetCountry(?string $pIP = null): ?object {
        // Init
        $vIP = $pIP ? $pIP : \Util::GetIP();
        $vCountry = null;

        if (!$vIP)
            return $vCountry;

        // GeoIp2 may trow an exception if IP not found in thier database
        try {
            // Load GeoIp database
            $vGeoIp = new \GeoIp2\Database\Reader(DB_GEOIP);
            // Query IP
            $vQuery = $vGeoIp->country($vIP);
            // Get country
            $vCountry = $vQuery->country;
        } catch (\Throwable $e) {
            // Log the error but DO NOT block code execution
            Log::Error(__FILE__, __METHOD__, __LINE__, $e->getCode() . ': ' . $e->getMessage(), true, false, false);
        } finally {
            return $vCountry;
        }
    }

    /**
     * Emulate in_array case insensitive
     *
     * @param string|null $pNeedle
     * @param array $pHaystack
     * @return boolean
     */
    public static function in_arrayi(?string $pNeedle = null, array $pHaystack = array()): bool {
        return in_array(strtoupper($pNeedle), array_map('strtoupper', $pHaystack));
    }

    /**
     * Flatten a multi-depth object/array
     *
     * @param array $pData
     * @return void
     */
    public static function FlattenArray(array $pData) {
        $vFlattenArray = [];
        array_walk_recursive($pData, function ($pValue) use (&$vFlattenArray) {
            $vFlattenArray[] = $pValue;
        });
        return $vFlattenArray;
    }
}
