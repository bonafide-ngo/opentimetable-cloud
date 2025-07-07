<?

/**
 * MSAL class
 */
class MSAL {

    /**
     * Decode the Azure JWT
     *
     * @param string $pJWT
     * @return mixed
     */
    public static function DecodeAzureJWT(string $pJWT): mixed {
        // Explode the JWT
        $vJWTs = explode(".", $pJWT);
        if (count($vJWTs) !== 3) {
            \Log::Debug(__FILE__, __METHOD__, __LINE__, ['Invalid JWT token format', $pJWT], true);
            throw new \JWTUnexpectedException();
        }

        // Decode the JWT header
        $vHeaderJWT = json_decode(base64_decode($vJWTs[0]), true);
        if (!isset($vHeaderJWT['kid'])) {
            \Log::Debug(__FILE__, __METHOD__, __LINE__, ['No \'kid\' property found in JWT token header', $vHeaderJWT], true);
            throw new \JWTUnexpectedException();
        }

        // Fetch JWKS (public keys from Azure AD)
        $vJWKS = file_get_contents(\Util::GetConfig('msal.url.keys'));
        $vJWKS = json_decode($vJWKS, true);
        if (!$vJWKS || !isset($vJWKS['keys'])) {
            \Log::Debug(__FILE__, __METHOD__, __LINE__, ['Unable to fetch JWKS keys', $vJWKS], true);
            throw new \JWTUnexpectedException();
        }

        // Add 'alg' to each key before parsing
        foreach ($vJWKS['keys'] as &$key) {
            if (!isset($key['alg'])) {
                // Azure JWT uses RS256
                $key['alg'] = 'RS256';
            }
        }

        // Find the correct public key based on 'kid'
        $vPublicKeys = \Firebase\JWT\JWK::parseKeySet($vJWKS);
        if (!isset($vPublicKeys[$vHeaderJWT['kid']])) {
            \Log::Debug(__FILE__, __METHOD__, __LINE__, ['No matching key found for JWT kid in public key', array('kid' => $vHeaderJWT['kid'], 'Available Keys' => $vJWKS['keys'], 'Public Keys' => $vPublicKeys)], true);
            throw new \JWTUnexpectedException();
        }
        $vPublicKey = $vPublicKeys[$vHeaderJWT['kid']];

        // Decode and verify the token using the public key
        try {
            return (array) \Firebase\JWT\JWT::decode($pJWT, $vPublicKey);
        } catch (\Throwable $e) {
            // The token is either invalid or expired
            throw new \JWTDataException();
        }
    }

    /**
     * Get someone's (oid) extra property via Graph API
     *
     * @param string $pIdToken
     * @param string $pAccessToken
     * @param string $pProperty
     * @return mixed
     */
    public static function GetSomeone_ExtraProperty(string $pIdToken, string $pAccessToken, string $pProperty): mixed {
        // Decode id token
        $vDecodedIdToken = self::DecodeAzureJWT($pIdToken);

        // Get user unique identifier
        $vOID = isset($vDecodedIdToken['oid']) && $vDecodedIdToken['oid'] ? $vDecodedIdToken['oid'] : null;
        if (!$vOID)
            throw new \UnexpectedException();

        $vHearders = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $pAccessToken
        ];
        // Query graph api
        // Attemp HTTP/2 for better perfomance
        $vResponse = \Util::cURL(\Util::ParseString(\Util::GetConfig('msal.url.select'), [$vOID, $pProperty]), null, $vHearders, CURL_HTTP_VERSION_2);
        $vJsonResponse = json_decode($vResponse->body);

        return isset($vJsonResponse->$pProperty) ? $vJsonResponse->$pProperty : null;
    }
    /**
     * Get a logged in user's (me) standard property via Graph API
     *
     * @param string $pAccessToken
     * @param string $pStandardProperty
     * @return mixed
     */
    public static function GetMe_StandardProperty(string $pAccessToken, string $pStandardProperty): mixed {
        $vHearders = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $pAccessToken
        ];
        // Query graph api
        // Attemp HTTP/2 for better perfomance
        $vResponse = \Util::cURL(\Util::GetConfig('msal.url.me'), null, $vHearders, CURL_HTTP_VERSION_2);
        $vJsonResponse = json_decode($vResponse->body);

        return isset($vJsonResponse->$pStandardProperty) ? $vJsonResponse->$pStandardProperty : null;
    }

    /**
     * Get the logged in user's (me) groups via Graph API
     *
     * @param string $pAccessToken
     * @return mixed
     */
    public static function GetMe_Groups(string $pAccessToken): mixed {
        // Init groups
        $vGroups = [];

        $vHearders = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $pAccessToken
        ];
        // Query graph api
        // Attemp HTTP/2 for better perfomance
        $vResponse = \Util::cURL(\Util::GetConfig('msal.url.memberOf'), null, $vHearders, CURL_HTTP_VERSION_2);
        $vJsonResponse = json_decode($vResponse->body);

        foreach ($vJsonResponse['value'] as $vGroup) {
            if ($vGroup['@odata.type'] === '#microsoft.graph.group') {
                $vGroups = $vGroup['id'];
            }
        }

        return $vGroups;
    }
}
