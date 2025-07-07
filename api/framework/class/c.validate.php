<?
class ValidateException extends Exception {
    // Redefine the exception so message isn't optional
    public function __construct($label = 'static.exception-validation', $code = -32098, Throwable $previous = null) {
        // make sure everything is assigned properly
        parent::__construct(\Lang::Get($label), $code, $previous);
    }
}

class Validate {
    const PATTERN_OTC       = '/^\d{6}$/';
    const PATTERN_NAME      = '/^(.*){1,64}+$/';
    const PATTERN_MOBILE    = '/^\+[0-9]{7,16}$/';
    const PATTERN_RESERVED  = '/top\s*\-?secret.*chat/i';
    const PATTERN_SHA1      = '/\b[A-Fa-f0-9]{40}\b/';
    const PATTERN_SHA256    = '/\b[A-Fa-f0-9]{64}\b/';
    const PATTERN_SHA512    = '/\b[A-Fa-f0-9]{128}\b/';
    const PATTERN_LAT       = '/^(-?([1-8]?\d(\.\d+)?|90(\.0+)?))$/';
    const PATTERN_LONG      = '/^(-?((1[0-7]\d|[1-9]?\d)(\.\d+)?|180(\.0+)?))$/';
    // https://stackoverflow.com/questions/5601647/html5-email-input-pattern-attribute#answer-36379040
    const PATTERN_EMAIL     = '/^[^@\s]+@[^@\s]+\.[^@\s]+$/';

    // Properties
    protected static $mImageTypes = array(IMG_JPG, IMG_JPEG);
    protected static $mSettingCodes = array();

    /**
     * Initialise Validate
     *
     * @param array $pParams
     * @return void
     */
    public static function Initialise(array $pParams = array()) {
        self::$mImageTypes = array_key_exists('image_types', $pParams) ? $pParams['image_types'] : self::$mImageTypes;
        self::$mSettingCodes = array_key_exists('setting_codes', $pParams) ? $pParams['setting_codes'] : self::$mSettingCodes;

        Log::Debug(__FILE__, __METHOD__, __LINE__, $pParams);
    }

    /**
     * Enforce a null parameter
     *
     * @param mixed $pInput
     */
    public static function EnforceNull(mixed &$pInput) {
        if (is_array($pInput))
            $pInput = empty($pInput) ? null : $pInput;
        else
            $pInput = $pInput === null ? null : (strlen($pInput) ? $pInput : null);
    }

    /**
     * Enforce a positive integer
     *
     * @param int $pInput
     */
    public static function EnforceAbs(int &$pInput) {
        $pInput = abs($pInput);
    }

    /**
     * Enforce a float
     *
     * @param string|null $pInput
     * @return void
     */
    public static function EnforceFloat(?string &$pInput) {
        $pInput = floatval($pInput);
    }

    /**
     * Validate if exists
     *
     * @param string|null $pInput
     * @return boolean
     */
    public static function Exist(?string $pInput): bool {
        return isset($pInput) && strlen($pInput) > 0 ? true : false;
    }

    /**
     * Validate against a pattern
     *
     * @param string|null $pInput
     * @param string $pPattern
     * @return boolean
     */
    public static function Pattern(?string $pInput, string $pPattern): bool {
        if (!$pInput)
            return false;
        else
            return preg_match($pPattern, $pInput) ? true : false;
    }

    /**
     * Validate against a numeric representation
     *
     * @param string|null $pInput
     * @return boolean
     */
    public static function Numeric(?string $pInput): bool {
        return !$pInput || is_numeric($pInput) === true ? true : false;
    }

    /**
     * Validate email
     *
     * @param string|null $pInput
     * @param integer $pMaxlength
     * @return boolean
     */
    public static function Email(?string $pInput, int $pMaxlength = 512): bool {
        if (
            \Validate::Pattern($pInput, \Validate::PATTERN_EMAIL)
            // DB contraint
            && \Validate::Maxlength($pInput, $pMaxlength)
        )
            return true;
        else
            return false;
    }

    /**
     * Validate against a min length
     *
     * @param string|null $pInput
     * @param integer $pMaxlength
     * @return boolean
     */
    public static function Minlength(?string $pInput, int $pMaxlength): bool {
        if (is_array($pInput)) {
            // Validate each element of the array
            foreach ($pInput as $vElement) {
                if (strlen($vElement) < $pMaxlength)
                    return false;
            }
        } elseif (strlen($pInput) < $pMaxlength)
            return false;

        return true;
    }

    /**
     * Validate against a max length
     *
     * @param string|null $pInput
     * @param integer $pMaxlength
     * @return boolean
     */
    public static function Maxlength(?string $pInput, int $pMaxlength): bool {
        if (is_array($pInput)) {
            // Validate each element of the array
            foreach ($pInput as $vElement) {
                if (strlen($vElement) > $pMaxlength)
                    return false;
            }
        } elseif (strlen($pInput) > $pMaxlength)
            return false;

        return true;
    }

    /**
     * Validate against codes
     *
     * @param string $pCode
     * @return boolean
     */
    public static function SettingCode(string $pCode): bool {
        // Check for code, case sensitive
        return in_array($pCode, self::$mSettingCodes);
    }


    /**
     * Validate against a file
     *
     * @param string $pBase64Image
     * @param integer $pMaxBytes
     * @param integer $pMaxWidth
     * @param integer $pMaxHeight
     * @return boolean
     */
    public static function Image(string $pBase64Image, int $pMaxBytes = null, int $pMaxWidth = null, int $pMaxHeight = null): bool {
        // Testing
        // $pBase64Image = "data:image/webp;base64,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";

        $vImageInfo = getimagesize($pBase64Image);
        // Check for image error
        if (!$vImageInfo)
            return false;

        // Check for image type
        if (!in_array($vImageInfo[2], self::$mImageTypes))
            return false;

        // Check for max image size in bytes
        if ($pMaxBytes) {
            // split the string on commas
            // $vData[0] == "data:image/webp;base64"
            // $vData[1] == <actual base64 string>
            $vData = explode(',', $pBase64Image);
            if (strlen(base64_decode($vData[0])) > $pMaxBytes)
                return false;
        }

        // Check for max image width
        if ($pMaxWidth && $vImageInfo[0] > $pMaxWidth)
            return false;

        // Check for max image heigth
        if ($pMaxHeight && $vImageInfo[1] > $pMaxHeight)
            return false;

        return true;
    }

    /**
     * Validate an IP address
     *
     * @param string $pIp
     * @return boolean
     */
    public static function IP(string $pIp): bool {
        return ip2long($pIp) ? true : false;
    }

    /**
     * Validate mobile
     *
     * @param string|null $pInput
     * @param integer $pMaxlength
     * @return boolean
     */
    public static function Mobile(?string $pInput, int $pMaxlength = 16): bool {
        if (
            !\Validate::Pattern($pInput, \Validate::PATTERN_MOBILE)
            // DB contraint
            || !\Validate::Maxlength($pInput, $pMaxlength)
        )
            return false;

        // Quick check
        $vOnlyCountries = (array)Util::GetConfig('intlTelInput.onlyCountries');
        $vExcludeCountries = (array)Util::GetConfig('intlTelInput.excludeCountries');
        if (empty($vOnlyCountries) && empty($vExcludeCountries))
            return true;

        // Strip leading +
        $vInput = str_replace('+', '', $pInput);

        try {
            // Init LibPhoneNumberUtil
            $vLibPhoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
            // Get country code (ie. 353)
            $vCountryCode = $vLibPhoneUtil->extractCountryCode($vInput, $vNationalPhonelNumber);
            // Get region code (ie. IE)
            $vRegionCode = $vLibPhoneUtil->getRegionCodeForCountryCode($vCountryCode);

            \Log::Debug(__FILE__, __METHOD__, __LINE__, array('Validate Mobile Number', $vCountryCode, $vRegionCode), true);
        } catch (\Throwable $e) {
            // Silent trow
            \Log::Error(__FILE__, __METHOD__, __LINE__, array('Validate Mobile Number', $e->getMessage()), true, false, false);
            return false;
        }

        // Check onlyCountries
        if (!\Util::in_arrayi($vRegionCode, $vOnlyCountries))
            return false;

        // Check excludeCountries
        if (\Util::in_arrayi($vRegionCode, $vExcludeCountries))
            return false;

        // N.B. Rember eventual additional carrier validation
        return true;
    }

    /**
     * Validate mobile carrier
     * https://gitea.bonafide.ngo/bonafide.ngo/libphonenumber-for-php/src/branch/master/src/carrier/data/en
     *
     * @param string $pInput
     * @return boolean
     */
    public static function MobileCarrier(string $pInput): bool {
        // Quick check
        $vOnlyCarrierNames = Util::GetConfig('intlTelInput.onlyCarrierNames');
        $vExcludeCarrierNames = Util::GetConfig('intlTelInput.excludeCarrierNames');
        if (empty($vOnlyCarrierNames) && empty($vExcludeCarrierNames))
            return true;

        // Strip leading +
        $vInput = str_replace('+', '', $pInput);

        try {
            // Init LibPhoneNumberUtil
            $vLibPhoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
            // Get country code (ie. 353)
            $vCountryCode = $vLibPhoneUtil->extractCountryCode($vInput, $vNationalPhonelNumber);
            // Get region code (ie. IE)
            $vRegionCode = $vLibPhoneUtil->getRegionCodeForCountryCode($vCountryCode);

            // Init PhoneNumberToCarrierMapper
            $vPhoneNumberToCarrierMapper = \libphonenumber\PhoneNumberToCarrierMapper::getInstance();
            // Parse mobile
            $vPhoneNumber = $vLibPhoneUtil->parse($vInput, $vRegionCode);
            // Get carrier name
            $vCarrierName = $vPhoneNumberToCarrierMapper->getNameForNumber($vPhoneNumber, "en");

            \Log::Debug(__FILE__, __METHOD__, __LINE__, array('Validate Mobile Carrier', $vCountryCode, $vRegionCode, $vPhoneNumber, $vCarrierName), true);
        } catch (\Throwable $e) {
            // Silent trow
            \Log::Error(__FILE__, __METHOD__, __LINE__, array('Validate Mobile Carrier', $e->getMessage()), true, false, false);
            return false;
        }

        // Check onlyCarrierNames
        $vOnlyCarrierNames = Util::GetConfig('intlTelInput.onlyCarrierNames');
        if (!\Util::in_arrayi($vCarrierName, $vOnlyCarrierNames))
            return false;

        // Check excludeCarrierNames
        if (\Util::in_arrayi($vCarrierName, $vExcludeCarrierNames))
            return false;

        // N.B. Rember the mobile validation upfront
        return true;
    }
}
