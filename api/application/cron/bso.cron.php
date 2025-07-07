<?

namespace App;

class BSO_Cron {

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Update_Notification(mixed $pParams = null): mixed {
        // Get signed salsa to authenticate request, no not urlencode for cron/curl
        //return new \ApiResponse(urlencode(\Crypto::SignItem(SALSA)));

        // Get params
        $vDTO = new DTO_Cron($pParams);

        // Get signed authentication
        if (!\Crypto::GetSignedItem($vDTO->signedSalsa_base64))
            throw new \UnexpectedException();

        // Check if a retry-after exists
        $vRetryAfter = (int)\Cache::Get('firebase.retry-after');
        if ($vRetryAfter > NOW)
            // Silent response
            return new \ApiResponse();

        // Process batches of max 100 notifications each
        do {
            // Read notifications
            $vNotifications = \OSQL::_GetResults(PATH_SQL_CRON, 'select_notification');
            if (!$vNotifications)
                // Silent response
                return new \ApiResponse();

            // Restructure and decode base64 the notifications into a flat array
            foreach ($vNotifications as $vNotification) {
                // TODO, test json_decode works as object expected
                $vPOST = json_decode(base64_decode($vNotification['ntf_post_base64']));
                // Send Firebase notification
                Firebase::FireSingle($vPOST);
            }

            // Flush notifications taking note of the last sent
            $vLastNotification = end($vNotifications);
            $vBindVars = array('ntf_id' => $vLastNotification['ntf_id']);
            \OSQL::_Query(PATH_SQL_CRON, 'delete_notification', $vBindVars);
        } while (!$vNotifications);

        return new \ApiResponse(\JsonRpc::SUCCESS);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Update_IpBlocklist(mixed $pParams = null): mixed {
        // Get signed salsa to authenticate request, no not urlencode for cron/curl
        //return new \ApiResponse(urlencode(\Crypto::SignItem(SALSA)));

        // Get params
        $vDTO = new DTO_Cron($pParams);

        // Get signed authentication
        if (!\Crypto::GetSignedItem($vDTO->signedSalsa_base64))
            throw new \UnexpectedException();

        // Get and cache the updated IP blocklist
        \Security::FetchIpBlocklist();

        return new \ApiResponse(\JsonRpc::SUCCESS);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Delete_Traffic(mixed $pParams = null): mixed {
        // Get signed salsa to authenticate request, no not urlencode for cron/curl
        //return new \ApiResponse(urlencode(\Crypto::SignItem(SALSA)));

        // Get params
        $vDTO = new DTO_Cron($pParams);

        // Get signed authentication
        if (!\Crypto::GetSignedItem($vDTO->signedSalsa_base64))
            throw new \UnexpectedException();

        // Delete outdated traffic
        $vBindVars = array('validity' => \Util::GetConfig('validity.traffic'));
        \OSQL::_Query(PATH_SQL_CRON, 'delete_traffic', $vBindVars);

        return new \ApiResponse(\JsonRpc::SUCCESS);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Create_Sync(mixed $pParams = null): mixed {
        // Get signed salsa to authenticate request, no not urlencode for cron/curl
        //return new \ApiResponse(urlencode(\Crypto::SignItem(SALSA)));

        return \App\Admin::Create_Sync($pParams);
    }
}
