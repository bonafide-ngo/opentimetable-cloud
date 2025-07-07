<?

/**
 * Crypto class
 */
class Crypto {

    public static $mSign_PublicKey = null;
    public static $mSign_SecretKey = null;
    public static $mSalsa = null;
    public static $mCrc64Table = null;

    /**
     * Initilize Lang
     * 
     * @param <type> $pPath
     * @param <type> $pLanguages
     * @param <type> $mDefaultLanguage
     */
    public static function Initialise(array $pParams = array()) {
        self::$mSign_PublicKey  = array_key_exists('sign_public_key', $pParams) ? sodium_base642bin($pParams['sign_public_key'], SODIUM_BASE64_VARIANT_ORIGINAL)   : self::$mSign_PublicKey;
        self::$mSign_SecretKey  = array_key_exists('sign_secret_key', $pParams) ? sodium_base642bin($pParams['sign_secret_key'], SODIUM_BASE64_VARIANT_ORIGINAL)   : self::$mSign_SecretKey;
        self::$mSalsa           = array_key_exists('salsa', $pParams)           ? $pParams['salsa']                                                                : self::$mSalsa;
    }

    /**
     * 
     * Generate an Hx hash code
     *
     * @param string $pText
     * @return string
     */
    public static function Hx(string $pText): string {
        return sodium_crypto_pwhash_str($pText, SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE, SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE);
    }
    /**
     * 
     * Test an Hx hash code against a Text
     *
     * @param string|null $pHx
     * @param string|null $pText
     * @return bool
     */
    public static function IsHx(?string $pHx, ?string $pText): bool {
        return !$pHx || !$pText ? false : sodium_crypto_pwhash_str_verify($pHx, $pText);
    }

    /**
     * Generate a random unique key
     *
     * @return string
     */
    public static function Key(): string {
        return self::RandomUniqueHash(null, 'sha1');
    }

    /**
     * Generate a random unique user uid
     *
     * @return string
     */
    public static function Uuid(): string {
        return self::RandomUniqueHash(null, 'sha1');
    }

    /**
     * Generate a random unique group uid
     *
     * @return string
     */
    public static function Guid(): string {
        return self::RandomUniqueHash(null, 'sha1');
    }

    /**
     * Generate a random unique token
     *
     * @return string
     */
    public static function Token(): string {
        return self::RandomUniqueHash(null, 'sha512');
    }

    /**
     * Generate a random unique salsa
     *
     * @return string
     */
    public static function Salsa(): string {
        return self::RandomUniqueHash(null, 'sha512');
    }

    /**
     * Generate a random unique avatar
     *
     * @return string
     */
    public static function Avatar(): string {
        return self::RandomUniqueHash(null, 'sha512');
    }

    /**
     * Generate a random unique hash with high entrophy
     *
     * @param string|null $pAlphaPrefix
     * @param string $pAlgorithm
     * @return string
     */
    public static function RandomUniqueHash(?string $pAlphaPrefix = null, string $pAlgorithm = 'sha512'): string {
        $vUnique = hash($pAlgorithm, microtime() . ' ' . uniqid('', true) . ' ' . mt_rand());
        return $pAlphaPrefix ? $pAlphaPrefix . $vUnique : $vUnique;
    }

    /**
     * 
     * Generate the Sha256 of a string
     *
     * @param mixed $pInput
     * @return string
     */
    public static function Sha256(string $pInput): string {
        // Get Sha256
        return hash('sha256', $pInput);
    }

    /**
     * 
     * Generate the Sha512 of a string
     *
     * @param mixed $pInput
     * @return string
     */
    public static function Sha512(string $pInput): string {
        // Get Sha512
        return hash('sha512', $pInput);
    }

    /**
     * Sign and set an Item
     *
     * @param string $pCookie
     * @param string $pConfigPath
     * @param boolean $pForceSession
     */
    public static function SignItem(string $pItem) {
        return sodium_bin2base64(sodium_crypto_sign($pItem, self::$mSign_SecretKey), SODIUM_BASE64_VARIANT_ORIGINAL);
    }

    /**
     * Sign and set a cookie
     *
     * @param string $pConfigPath
     * @param string $pCookie
     * @param boolean $pForceSession
     * @param array $pOverrideCookieOptions
     */
    public static function SignCookie(string $pConfigPath, string $pCookie, bool $pForceSession = false, array $pOverrideCookieOptions = array()) {
        \Util::SetCookie($pConfigPath, self::SignItem($pCookie), false, $pForceSession, $pOverrideCookieOptions);
    }

    /**
     * Return an Item if signature matches
     *
     * @param string $pConfigPath
     * @return string|boolean
     */
    public static function GetSignedItem(string $pSignedItem): string|bool {
        return sodium_crypto_sign_open(sodium_base642bin($pSignedItem, SODIUM_BASE64_VARIANT_ORIGINAL), self::$mSign_PublicKey);
    }
    /**
     * Return a Cookie if signature matches
     *
     * @param string $pConfigPath
     * @param boolean $pIsMandatory
     * @return string
     */
    public static function GetSignedCookie(string $pConfigPath, $pIsMandatory = false): string {
        // Get signed cookied
        $vSignedCookie = Util::GetCookie($pConfigPath);
        // Verify base64 integrity and get cookie
        $vCookie =  $vSignedCookie != base64_encode(base64_decode($vSignedCookie)) ? false : self::GetSignedItem(Util::GetCookie($pConfigPath));
        if ($vCookie !== false)
            return $vCookie;
        else if ($pIsMandatory) {
            // Clean invalid signed cookie
            \Util::SetCookie($pConfigPath, null, true);
            throw new UnexpectedException();
        } else
            return false;
    }

    /**
     * 
     * Generate a Hash code
     *
     * @param mixed $pInput
     * @param string $pUseSalsa
     * @param string $pAlgorithm
     * @return string
     */
    public static function GetHash(mixed $pInput, bool $pUseSalsa = true, string $pAlgorithm = 'sha512'): string {
        // Get HASH
        return hash($pAlgorithm, serialize($pInput) . ($pUseSalsa ? self::$mSalsa : ''));
    }

    /**
     * Check a Hash code
     *
     * @param string $pHash
     * @param mixed $pInput
     * @param boolean $pUseSalsa
     * @param string $pAlgorithm
     * @return boolean
     */
    public static function IsHash(string $pHash, mixed $pInput = null, bool $pUseSalsa = true, string $pAlgorithm = 'sha512'): bool {
        if (strcasecmp($pHash, self::GetHash($pInput, $pUseSalsa, $pAlgorithm)))
            return false;
        else
            return true;
    }

    /**
     * Emulate CRC64 in PHP
     * https://gist.github.com/hightemp/4da5ac39b8d57fcd7e7988b90a48017d
     * 
     *  Crc64('php'); // afe4e823e7cef190
     *  Crc64('php, '%x'); // afe4e823e7cef190
     *  Crc64('php', '0x%x'); // 0xafe4e823e7cef190
     *  Crc64('php', '0x%X'); // 0xAFE4E823E7CEF190
     *  Crc64('php', '%d'); // -5772233581471534704 signed int
     *  Crc64('php', '%u'); // 12674510492238016912 unsigned int
     *
     * @param string $pInput
     * @param string $pFormat
     * @return string
     */
    public static function Crc64(string $pInput, string $pFormat = '%x'): string {
        // Init table if not yet filled in
        if (self::$mCrc64Table === null) {
            self::$mCrc64Table = [];

            // ECMA polynomial
            $poly64rev = (0xC96C5795 << 32) | 0xD7870F42;
            // ISO polynomial
            // $poly64rev = (0xD8 << 56);

            // Fill in table
            for ($i = 0; $i < 256; $i++) {
                for ($part = $i, $bit = 0; $bit < 8; $bit++) {
                    if ($part & 1) {
                        $part = (($part >> 1) & ~(0x8 << 60)) ^ $poly64rev;
                    } else {
                        $part = ($part >> 1) & ~(0x8 << 60);
                    }
                }
                self::$mCrc64Table[$i] = $part;
            }
        }

        // Calculate CRC64
        $vCrc64 = 0;
        for ($i = 0; $i < strlen($pInput); $i++) {
            $vCrc64 = self::$mCrc64Table[($vCrc64 ^ ord(self::$mCrc64Table[$i])) & 0xff] ^ (($vCrc64 >> 8) & ~(0xff << 56));
        }

        // Format CRC64
        return sprintf($pFormat, $vCrc64);
    }
}
