<?

namespace App;

class BSO_Upgrade {

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Update_DB(mixed $pParams = null): mixed {
        // Get signed salsa to authenticate request, do not urlencode for cron/curl
        //return new \ApiResponse(urlencode(\Crypto::SignItem(SALSA)));

        // Get params
        $vDTO = new DTO_Update_DB($pParams);

        // Get signed authentication
        if (!\Crypto::GetSignedItem($vDTO->signedSalsa_base64))
            throw new \UnexpectedException();

        // Switch version
        switch ($vDTO->version) {
            case 'x.y.x':
                //FUN_Upgrade::Upgrade_x_y_z();
                break;
            default:
                return new \ApiResponse('Invalid version: ' . $vDTO->version);
                break;
        }

        return new \ApiResponse(\JsonRpc::SUCCESS);
    }
}
