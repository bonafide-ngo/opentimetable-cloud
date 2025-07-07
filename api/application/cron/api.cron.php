<?

namespace App;

class Cron {

    /**
     * Run notification queue
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Update_Notification(mixed $pParams = null): mixed {
        return BSO_Cron::Update_Notification($pParams);
    }

    /**
     * Update the IP blocklist
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Update_IpBlocklist(mixed $pParams = null): mixed {
        return BSO_Cron::Update_IpBlocklist($pParams);
    }

    /**
     * Delete outdated traffic
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Delete_Traffic(mixed $pParams = null): mixed {
        return BSO_Cron::Delete_Traffic($pParams);
    }

    /**
     * Create sync
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Create_Sync(mixed $pParams = null): mixed {
        // Load dependency
        \JsonRpc::LoadReferences('App', 'Admin');

        return BSO_Cron::Create_Sync($pParams);
    }
}
