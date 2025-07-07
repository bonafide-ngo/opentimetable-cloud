<?

namespace App;

/**
 * Firebase methods in App namespace
 * https://firebase.google.com/docs/cloud-messaging/send-message
 * https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages
 * https://firebase.google.com/docs/cloud-messaging/http-server-ref
 * N.B. All numbers must be stringified
 */
class Firebase {

    // Back-off time in seconds to match the cron job frequency
    private static int $mBackOff = 6;
    // Time to live (TTL) in seconds, default and max of 4 weeks
    // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#AndroidConfig
    private static int $mTtl = 2419200;

    /**
     * Get Oauth2Bearer
     *
     * @return string
     */
    private static function Oauth2Bearer(): string {
        // Check if OAuth exists in cache
        $vOauth2Key = [APP_FIREBASE_SCOPE, APP_FIREBASE_OAUTH2];
        $vOauth2Bearer = \Cache::Get($vOauth2Key);
        if (!$vOauth2Bearer) {
            // Call for a new OAuth2
            // https://github.com/googleapis/google-api-php-client/issues/1714
            $vOauth2 = \Google\Auth\CredentialsLoader::makeCredentials(APP_FIREBASE_SCOPE, json_decode(APP_FIREBASE_OAUTH2, true), true);

            $vOauth2Token = $vOauth2->fetchAuthToken();
            $vOauth2Bearer = $vOauth2Token['access_token'];
            $vOauth2Validity = $vOauth2Token['expires_in'];

            // Store bearer in cache with a safety margin of 60 sec
            \Cache::Set($vOauth2Key, $vOauth2Bearer, $vOauth2Validity - 60);
        }

        return $vOauth2Bearer;
    }

    /**
     * Undocumented function
     *
     * @param object $pPOST
     * @param string|null $pLink
     * @param integer|null $pTtl
     * @return string
     */
    private static function InitPOST(object $pPOST, string $pLink = null, int $pTtl = null): string {
        // Set default time-to-live, allowing for 0 (zero) as well
        $vTtl = $pTtl || $pTtl === 0 ? min($pTtl, self::$mTtl) : self::$mTtl;

        // Android: options
        // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#AndroidConfig
        // https://firebase.google.com/docs/cloud-messaging/concept-options.html#ttl
        $pPOST->message->android = new \stdClass();
        $pPOST->message->android->ttl = strval($vTtl . 's');
        $pPOST->message->android->priority = 'high';

        // iOS: options
        // https://firebase.google.com/docs/cloud-messaging/concept-options.html#ttl
        // https://developer.apple.com/library/archive/documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/CommunicatingwithAPNs.html
        $pPOST->message->apns = new \stdClass();
        $pPOST->message->apns->headers = new \stdClass();
        $pPOST->message->apns->headers->{'apns-expiration'} = $vTtl ? strval(NOW + $vTtl) : strval($vTtl);
        $pPOST->message->apns->headers->{'apns-priority'} = '10';
        $pPOST->message->apns->payload = new \stdClass();
        $pPOST->message->apns->payload->aps = new \stdClass();
        if (isset($pPOST->message->notification))
            // iOS: notification sound
            // N.B. Must be handle here instead of in xcode (different form android)
            // https://stackoverflow.com/questions/39335363/fcm-notification-in-ios-doesnt-play-sound-when-received
            $pPOST->message->apns->payload->aps->sound = "beep.mp3";
        else if (isset($pPOST->message->data))
            // iOS: data only
            // https://firebase.google.com/docs/cloud-messaging/send-message#defining_the_message_options
            $pPOST->message->apns->payload->aps->{'content-available'} = 1;

        // Webpush: options
        // https://firebase.google.com/docs/cloud-messaging/concept-options.html#ttl
        $pPOST->message->webpush = new \stdClass;
        $pPOST->message->webpush->headers = new \stdClass();
        $pPOST->message->webpush->headers->TTL = strval($vTtl);
        $pPOST->message->webpush->headers->Urgency = "high";
        if ($pLink) {
            $pPOST->message->webpush->fcm_options = new \stdClass;
            $pPOST->message->webpush->fcm_options->link = $pLink;
        }

        // JSON serialise for transmission
        return json_encode($pPOST);
    }

    /**
     * Send a single notification
     *
     * @param object $pPOST
     * @param string|null $pLink
     * @param integer|null $pTtl
     * @return void
     */
    public static function FireSingle(object $pPOST, string $pLink = null, int $pTtl = null) {
        // Init POST
        $vPOST = self::InitPOST($pPOST, $pLink, $pTtl);

        // Compose URL
        $vUrl = \Util::GetConfig('url.firebaseFCM', [APP_FIREBASE_PROJECT_ID]);

        // Compose headers
        $vHearders = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . self::Oauth2Bearer()
        ];

        // Log
        \Log::Debug(__FILE__, __METHOD__, __LINE__, ['Firebase Notification', $vUrl, $vHearders, $pPOST], true);

        // Check if a retry-after exists
        $vRetryAfter = (int)\Cache::Get('firebase.retry-after');
        if ($vRetryAfter > NOW) {
            // Add to queue, preserve json escaping
            $vBindVars = array('ntf_post_base64' => base64_encode($vPOST));
            \OSQL::_Query('insert_notification', PATH_SQL_APPLICATION, $vBindVars);
            return;
        }

        // Send notification via cURL
        // Attemp HTTP/2 for better perfomance
        $vResponse = \Util::cURL($vUrl, $vPOST, $vHearders, CURL_HTTP_VERSION_2);

        // Check if no response
        if (!$vResponse->body) {
            // Send now or never, do not queue for retry
            if ($pTtl === 0)
                return;

            // Add to queue, preserve json escaping
            $vBindVars = array('ntf_post_base64' => base64_encode($vPOST));
            \OSQL::_Query('insert_notification', PATH_SQL_APPLICATION, $vBindVars);

            // Check if to honour the retry-after header
            if (in_array('retry-after', $vResponse->headers)) {
                $vRetryAfter = NOW + $vResponse->headers['retry-after'];
                // Log retry-after
                \Log::Debug(__FILE__, __METHOD__, __LINE__, ['Firebase Retry-After?', $vResponse->headers], true);
            } else
                // Set back-off
                $vRetryAfter = NOW + self::$mBackOff;

            // Store retry-after
            \Cache::Set('firebase.retry-after', $vRetryAfter);
        }
    }

    /**
     * DEPRECATED
     * Send notifications in batch
     * https://firebase.google.com/docs/cloud-messaging/send-message#send-a-batch-of-messages
     *
     * @param array $pPOSTs
     * @param string|null $pLink
     * @param boolean $pIsInitPOST
     * @return boolean
     */
    public static function FireBatch(array $pPOSTs, $pLink = null, $pIsInitPOST = false): bool {
        // Enforce batch limit of 100
        $vPOSTs = array_slice($pPOSTs, 0, 100, true);

        // Init POSTs on demand
        if ($pIsInitPOST)
            foreach ($vPOSTs as $vIndex => $vPOST) {
                $vPOSTs[$vIndex] = self::InitPOST($vPOST, $pLink);
            }

        // Get Oauth2Bearer
        $vOauth2Bearer = self::Oauth2Bearer();

        // Compose URL
        $vUrl = \Util::GetConfig('url.firebaseBatchFCM');

        // Set boundary
        $vBoundary = \Crypto::GetHash(null, true, 'sha1');

        // Handle constants in HEREDOC notation
        // https://stackoverflow.com/questions/10041200/interpolate-a-constant-not-variable-into-heredoc
        $C = 'constant';

        // Build batch post
        $vBatchPOST = "";
        foreach ($vPOSTs as $vPOST) {
            $vBatchPOST .=
                <<<JSON
{$C('PHP_EOL')}
--$vBoundary
Content-Type: application/http
Content-Transfer-Encoding: binary
Authorization: Bearer $vOauth2Bearer

POST /v1/projects/{$C('APP_FIREBASE_PROJECT_ID')}/messages:send
Content-Type: application/json
accept: application/json

$vPOST
JSON;
        }

        // Append tail
        $vBatchPOST .=
            <<<JSON
{$C('PHP_EOL')}
--$vBoundary--
JSON;

        // Compose headers
        $vHearders = [
            "Content-Type: multipart/mixed; boundary=$vBoundary"
        ];

        // Log
        \Log::Debug(__FILE__, __METHOD__, __LINE__, ['Firebase batch of notifications', $vUrl, $vHearders, $vBatchPOST], true);

        // Check if a retry-after exists
        $vRetryAfter = (int)\Cache::Get('firebase.retry-after');
        if ($pIsInitPOST && $vRetryAfter > NOW) {
            // Add to queue if this is a fresh new initialised post, preserve json escaping
            foreach ($vPOSTs as $vPOST) {
                $vBindVars = array('ntf_post_base64' => base64_encode($vPOST));
                \OSQL::_Query('insert_notification', PATH_SQL_APPLICATION, $vBindVars);
            }
            return true;
        }

        // Send batch notification via cURL
        // Attemp HTTP/2 for better perfomance
        $vResponse = \Util::cURL($vUrl, $vBatchPOST, $vHearders, CURL_HTTP_VERSION_2);

        // Check if no response or HTTP error in response body (for batch only) to queue a retry
        if (
            !$vResponse->body
            || (strripos($vResponse->body, "HTTP/1 200") === false
                && strripos($vResponse->body, 'HTTP/1.1 200') === false
                && strripos($vResponse->body, "HTTP/2 200") === false
                && strripos($vResponse->body, "HTTP/2.0 200") === false
                && strripos($vResponse->body, "HTTP/3 200") === false)
        ) {
            if ($pIsInitPOST) {
                // Add to queue, preserve json escaping
                foreach ($vPOSTs as $vPOST) {
                    $vBindVars = array('ntf_post_base64' => base64_encode($vPOST));
                    \OSQL::_Query('insert_notification', PATH_SQL_APPLICATION, $vBindVars);
                }

                // Check if to honour the retry-after header
                if (in_array('retry-after', $vResponse->headers)) {
                    $vRetryAfter = NOW + $vResponse->headers['retry-after'];
                    // Log retry-after
                    \Log::Debug(__FILE__, __METHOD__, __LINE__, ['Firebase Retry-After?', $vResponse->headers], true);
                } else
                    // Increase back-off
                    $vRetryAfter = ($vRetryAfter ? $vRetryAfter : NOW) + self::$mBackOff;

                // Store retry-after
                \Cache::Set('firebase.retry-after', $vRetryAfter);

                return true;
            } else
                return false;
        } else
            return true;
    }

    /**
     * Generica notification
     *
     * @param string|null $pToken
     * @param string $pTitle
     * @param string $pBody
     * @return void
     */
    public static function Generic(?string $pToken, string $pTitle, string $pBody) {
        if (!$pToken)
            return;

        // Compose POST request
        $vPOST = new \stdClass;
        $vPOST->message = new \stdClass;
        $vPOST->message->token = $pToken;
        $vPOST->message->notification = new \stdClass;
        $vPOST->message->notification->title = $pTitle;
        $vPOST->message->notification->body = $pBody;
        $vPOST->message->data = new \stdClass;
        $vPOST->message->data->title = $pTitle;
        $vPOST->message->data->body = $pBody;
        $vPOST->message->data->type = \Util::GetConfig('firebase.generic');

        // Send Firebase notification
        self::FireSingle($vPOST);
    }
}
