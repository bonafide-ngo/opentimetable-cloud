<?

/**
 * Lang Class
 */
class Lang {

    protected static $mPath             = 'lang/';
    protected static $mDefault          = 'en';
    protected static $mSelected         = null;
    protected static $mLanguages        = array('en' => 'english-in-english');
    protected static $mDictionary       = array();

    /**
     * Initilize Lang
     * 
     * @param <type> $pPath
     * @param <type> $pLanguages
     * @param <type> $mDefaultLanguage
     */
    public static function Initialise(array $pParams = array()) {
        self::$mPath        = array_key_exists('path', $pParams)       ? $pParams['path']         : self::$mPath;
        self::$mDefault     = array_key_exists('default', $pParams)    ? $pParams['default']      : self::$mDefault;
        self::$mSelected    = array_key_exists('selected', $pParams)   ? $pParams['selected']     : self::$mSelected;
        self::$mLanguages   = array_key_exists('languages', $pParams)  ? $pParams['languages']    : self::$mLanguages;

        // Sanitize and Validate data
        self::$mDefault     = strtolower(self::$mDefault);
        self::$mLanguages   = array_map('strtolower', self::$mLanguages);
        self::$mSelected    = self::SanitizeLanguage(self::$mSelected);

        // Load Dictionary
        self::LoadDictionary();

        // Set PHP locale
        setLocale(LC_ALL, self::$mSelected);
    }

    /**
     * 
     * @param string $pKey
     * @param boolean $pStripSlashes
     * @param boolean $pJoin
     * @return string
     */
    public static function Get(string $pKey, bool $pStripSlashes = true, bool $pJoin = false, ?string $pLang = null): ?string {
        $vNodes = explode('.', $pKey);
        $vNode = self::$mDictionary[$pLang ? $pLang : self::GetSelected()];

        // Loop & find
        foreach ($vNodes as $vValue) {
            if (isset($vNode[$vValue]))
                $vNode = $vNode[$vValue];
            else {
                $vNode = null;
                break;
            }
        }

        if ($pJoin)
            $vNode = join("\n", $vNode);

        return $vNode ? ($pStripSlashes ? stripcslashes($vNode) : $vNode) : $vNode;
    }

    /**
     * 
     * @return void
     */
    public static function LoadDictionary() {
        // source : https://api.drupal.org/api/drupal/includes%21bootstrap.inc/function/drupal_array_merge_deep_array/7.x
        function array_merge_deep() {
            $vArgs = func_get_args();
            return array_merge_deep_array($vArgs);
        }

        function array_merge_deep_array($pArrays) {
            $vResult = array();
            foreach ($pArrays as $vArray) {
                foreach ($vArray as $vKey => $vValue) {
                    // Renumber integer keys as array_merge_recursive() does.
                    // PHP automatically converts array keys that are integer strings (e.g., '1') to integers.
                    if (is_integer($vKey)) {
                        $vResult[intval($vKey)] = $vValue;
                    } elseif (isset($vResult[$vKey]) && is_array($vResult[$vKey]) && is_array($vValue)) {
                        $vResult[$vKey] = array_merge_deep_array(array(
                            $vResult[$vKey],
                            $vValue,
                        ));
                    } else {
                        $vResult[$vKey] = $vValue;
                    }
                }
            }
            return $vResult;
        }

        // Get default lang
        $vDefaultBase = (array) json_decode(file_get_contents(PATH_LANG . self::$mDefault . '.base.json'), true);
        $vDefaultInstance = (array) json_decode(file_get_contents(PATH_LANG . self::$mDefault . '.instance.json'), true);
        self::$mDictionary[self::$mDefault] = array_merge_deep($vDefaultBase, $vDefaultInstance);

        // Get target lang
        foreach (self::$mLanguages as $vLangIso => $vLangLabel) {
            if (self::$mDefault != $vLangIso) {
                // Get target lang and merge
                $vTargetBase = (array) json_decode(file_get_contents(PATH_LANG . $vLangIso . '.base.json'), true);
                $vTargetInstance = (array) json_decode(file_get_contents(PATH_LANG . $vLangIso . '.instance.json'), true);
                self::$mDictionary[$vLangIso] = array_merge_deep(self::$mDictionary[self::$mDefault], $vTargetBase, $vTargetInstance);
            }
        }
    }

    /**
     *
     * @return string
     */
    public static function GetDefault(): string {
        return self::$mDefault;
    }

    /**
     *
     * @return string
     */
    public static function GetSelected(): ?string {
        return self::$mSelected;
    }

    /**
     *
     * @return void
     */
    public static function SetSelected(string $pSelected) {
        self::$mSelected = self::SanitizeLanguage($pSelected);
    }

    /**
     *
     * @return array
     */
    public static function GetLanguages(): array {
        return self::$mLanguages;
    }

    /**
     *
     * @param string $vLanguage
     * @return string
     */
    public static function SanitizeLanguage(?string $pLanguage): string {
        $vLanguage = strtolower($pLanguage);

        // Validate
        if (array_key_exists($vLanguage, self::$mLanguages))
            return $vLanguage;
        else
            return self::GuessLanguage();
    }

    /**
     *
     * @return string
     */
    public static function GuessLanguage(): string {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
            return self::$mDefault;

        foreach (self::$mLanguages as $vLangIso => $vLangLabel) {
            if (preg_match("/\$vLangIso\b/i", $_SERVER['HTTP_ACCEPT_LANGUAGE']))
                return $vLangIso;
        }

        return self::$mDefault;
    }

    /**
     * Format time according to the locale
     * Matching JS frm.label.formatTime
     *
     * @param mixed $pSeconds
     * @param boolean $pToLowerCase
     * @return void
     */
    public static function FormatTime(mixed $pSeconds = 0, bool $pToLowerCase = false) {
        // Cast
        $pSeconds = intval($pSeconds);
        // Get the locale
        $vLocale = localeconv();

        if ($pSeconds / 60 < 1)
            // Seconds
            $pOutput = number_format(intval($pSeconds), 0, $vLocale['decimal_point'], $vLocale['thousands_sep']) . ' ' . \Lang::Get('static.seconds');
        else if ($pSeconds / 60 / 60 < 1)
            // Minutes
            $pOutput = number_format(intval($pSeconds / 60), 0, $vLocale['decimal_point'], $vLocale['thousands_sep']) . ' ' . \Lang::Get('static.minutes');
        else if ($pSeconds / 60 / 60 / 24 < 1)
            // Hours
            $pOutput = number_format(intval($pSeconds / 60 / 60), 0, $vLocale['decimal_point'], $vLocale['thousands_sep']) . ' ' . \Lang::Get('static.hours');
        else
            // Days
            $pOutput = number_format(intval($pSeconds / 60 / 60 / 60), 0, $vLocale['decimal_point'], $vLocale['thousands_sep']) . ' ' . \Lang::Get('static.days');

        return $pToLowerCase ? strtolower($pOutput) : $pOutput;
    }
}
