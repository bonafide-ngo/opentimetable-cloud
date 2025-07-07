<?

namespace App;

/**
 * Handle restful webhooks
 */
class Webhook {

    /**
     * Upgrade
     * https://domain/api/?webhook=upgrade&version=XXX&signedSalsa_base64=YYY
     *
     * @param string|null $pVersion
     * @param string|null $pSignedSalsa_base64
     * @return void
     */
    public static function Upgrade(?string $pVersion, ?string $pSignedSalsa_base64 = null) {
        // Load references
        \JsonRpc::LoadReferences('App', 'Upgrade');

        try {
            // Build params
            $vParams = new \stdClass();
            $vParams->version = $pVersion;
            $vParams->signedSalsa_base64 = $pSignedSalsa_base64;

            // Begin transaction
            \OSQL::__TransactionBegin();

            // Show response
            $vResponse = \App\Upgrade::Update_DB($vParams);

            // Commit transaction
            \OSQL::__TransactionCommit();

            // Show response
            print_r($vResponse);
        } catch (\Throwable $e) {
            // Rollback transaction
            \OSQL::__TransactionRollback();

            // Show exception
            print_r($e->getMessage());
        }

        exit();
    }

    /**
     * Cleanup
     * https://domain/api/?webhook=cleanup&signedSalsa_base64=YYY
     *
     * @param string|null $pSignedSalsa_base64
     * @return void
     */
    public static function Cleanup(?string $pSignedSalsa_base64 = null) {
        // Build params
        $vParams = new \stdClass();
        $vParams->signedSalsa_base64 = $pSignedSalsa_base64;

        // Invoke jsonrpc
        \JsonRpc::Invoke('App.Admin.Delete_Batch', $vParams);
    }

    /**
     * Batch
     * https://domain/api/?webhook=batch&signedUser_base64=YYY
     *
     * @param string|null $pSignedUser_base64
     * @return void
     */
    public static function Batch(?string $pSignedUser_base64 = null) {
        // Build params
        $vParams = new \stdClass();
        $vParams->signedUser_base64 = $pSignedUser_base64;

        // Invoke jsonrpc
        \JsonRpc::Invoke('App.Admin.Create_Batch', $vParams);
    }

    /**
     * IpBlocklist
     * https://domain/api/?webhook=ipblocklist&signedSalsa_base64=YYY
     *
     * @param string|null $pSignedSalsa_base64
     * @return void
     */
    public static function IpBlocklist(?string $pSignedSalsa_base64 = null) {
        // Build params
        $vParams = new \stdClass();
        $vParams->signedSalsa_base64 = $pSignedSalsa_base64;

        // Invoke jsonrpc
        \JsonRpc::Invoke('App.Cron.Update_IpBlocklist', $vParams);
    }

    /**
     * Traffic
     * https://domain/api/?webhook=traffic&signedSalsa_base64=YYY
     *
     * @param string|null $pSignedSalsa_base64
     * @return void
     */
    public static function Traffic(?string $pSignedSalsa_base64 = null) {
        // Build params
        $vParams = new \stdClass();
        $vParams->signedSalsa_base64 = $pSignedSalsa_base64;

        // Invoke jsonrpc
        \JsonRpc::Invoke('App.Cron.Delete_Traffic', $vParams);
    }

    /**
     * Sync
     * https://domain/api/?webhook=sync&signedSalsa_base64=YYY
     *
     * @param string|null $pSignedSalsa_base64
     * @return void
     */
    public static function Sync(?string $pSignedSalsa_base64 = null) {
        // Build params
        $vParams = new \stdClass();
        $vParams->signedSalsa_base64 = $pSignedSalsa_base64;

        // Invoke jsonrpc
        \JsonRpc::Invoke('App.Cron.Create_Sync', $vParams);
    }
}
