<?

/**
 * VTI (Virtual Tunnel Interface) class
 */
class VTI {

    protected static string $mSessionKey = '__VTI';
    public static string $mClass = __CLASS__;
    public static string $mInit = 'Init';

    /**
     * Initiate a VTI
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Init(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new VTI_Init($pParams);

        // Get NaCl
        $vKeyPair = sodium_crypto_box_keypair();

        // Extract keys from the pair
        $vPublicKey = sodium_crypto_box_publickey($vKeyPair);
        $vSecretKey = sodium_crypto_box_secretkey($vKeyPair);

        // Encode keys for storage
        $vVTI_Keys = new VTI_Keys();
        $vVTI_Keys->publickey_base64 = $vDTO->publicKey_base64;
        $vVTI_Keys->secretkey_base64 = sodium_bin2base64($vSecretKey, SODIUM_BASE64_VARIANT_ORIGINAL);

        // Save in session
        Util::SetInSession(self::$mSessionKey, $vVTI_Keys);

        Log::Debug(__FILE__, __METHOD__, __LINE__, [self::$mSessionKey, $vVTI_Keys]);

        // Return public key
        return new \ApiResponse(sodium_bin2base64($vPublicKey, SODIUM_BASE64_VARIANT_ORIGINAL));
    }

    /**
     * Box a VTI request
     *
     * @param mixed $pResult
     */
    public static function Box(mixed &$pResult = null) {
        if (!Util::GetConfig('vti.enable'))
            // Skip, VTI is not enabled
            return;

        // Get VTI keys from session
        $vVTI_Keys = Util::GetFromSession(self::$mSessionKey);
        if (!$vVTI_Keys)
            throw new UnexpectedException();

        // Let's box
        $vNonce = random_bytes(24);
        $vKeyPair = sodium_crypto_box_keypair_from_secretkey_and_publickey(sodium_base642bin($vVTI_Keys->secretkey_base64, SODIUM_BASE64_VARIANT_ORIGINAL), sodium_base642bin($vVTI_Keys->publickey_base64, SODIUM_BASE64_VARIANT_ORIGINAL));
        $vBox = sodium_crypto_box(json_encode($pResult), $vNonce, $vKeyPair);

        // Set VTI boxed result
        $__VTI = Util::GetConfig('vti.param');
        $vResult = new stdClass();
        $vResult->{$__VTI} = new stdClass();
        $vResult->{$__VTI}->box_base64 = sodium_bin2base64($vBox, SODIUM_BASE64_VARIANT_ORIGINAL);
        $vResult->{$__VTI}->nonce_base64 = sodium_bin2base64($vNonce, SODIUM_BASE64_VARIANT_ORIGINAL);

        // Override JsonRpc result
        $pResult = $vResult;

        Log::Debug(__FILE__, __METHOD__, __LINE__, ['VTI boxed', $vVTI_Keys]);
    }

    /**
     * Unbox a VTI request
     *
     * @param mixed $pParams
     * @return boolean
     */
    public static function Unbox(mixed &$pParams = null): bool {
        if (!Util::GetConfig('vti.enable'))
            // Skip, VTI is not enabled
            return false;

        $__VTI = Util::GetConfig('vti.param');
        if (!isset($pParams->{$__VTI}))
            // Skip, no VTI to unbox
            return false;

        // Get VTI
        $vBox_Base64 = $pParams->{$__VTI}->box_base64;
        $vNonce_Base64 = $pParams->{$__VTI}->nonce_base64;

        // Let's unbox
        $vVTI_Keys = Util::GetFromSession(self::$mSessionKey);
        if (!$vVTI_Keys)
            throw new UnexpectedException();

        // Get NaCl
        $vKeyPair = sodium_crypto_box_keypair_from_secretkey_and_publickey(sodium_base642bin($vVTI_Keys->secretkey_base64, SODIUM_BASE64_VARIANT_ORIGINAL), sodium_base642bin($vVTI_Keys->publickey_base64, SODIUM_BASE64_VARIANT_ORIGINAL));
        $vBox = json_decode(sodium_crypto_box_open(sodium_base642bin($vBox_Base64, SODIUM_BASE64_VARIANT_ORIGINAL), sodium_base642bin($vNonce_Base64, SODIUM_BASE64_VARIANT_ORIGINAL), $vKeyPair));

        if (empty($vBox))
            throw new UnexpectedException();

        Log::Debug(__FILE__, __METHOD__, __LINE__, ['VTI unboxed', $vVTI_Keys]);

        // Override JsonRpc params
        $pParams = $vBox;
        return true;
    }
}

class VTI_Keys {
    public string $publickey_base64 = '';
    public string $secretkey_base64 = '';
}

class VTI_Init extends DTO {

    public string $publicKey_base64 = '';

    public function Validate() {
        if (
            !\Validate::Exist($this->publicKey_base64)
            || $this->publicKey_base64 != base64_encode(base64_decode($this->publicKey_base64))
        )
            throw new \ValidateException;
    }
}
