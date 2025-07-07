<?

namespace App;

class Admin {

    /* 
     * Admin - Settings
     * ************************************************************************
     */

    /**
     * Read setting
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Setting(mixed $pParams = null): mixed {
        return BSO_Admin::Read_Setting($pParams);
    }

    /**
     * Read setting
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Setting_All(mixed $pParams = null): mixed {
        return BSO_Admin::Read_Setting_All($pParams);
    }

    /**
     * Updating setting flag
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Update_SettingFlag(mixed $pParams = null): mixed {
        return BSO_Admin::Update_SettingFlag($pParams);
    }

    /**
     * Updating setting text
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Update_SettingText(mixed $pParams = null): mixed {
        return BSO_Admin::Update_SettingText($pParams);
    }

    /* 
     * Admin - Sync
     * ************************************************************************
     */

    /**
     * Read sync all
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Sync_All(mixed $pParams = null): mixed {
        return BSO_Admin::Read_Sync_All($pParams);
    }

    /**
     * Read sync pending
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Sync_Pending(mixed $pParams = null): mixed {
        return BSO_Admin::Read_Sync_Pending($pParams);
    }

    /**
     * Update sync pending
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Update_Sync_Pending(mixed $pParams = null): mixed {
        return BSO_Admin::Update_Sync_Pending($pParams);
    }

    /**
     * Read if any raw record exists
     * 
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Raw(mixed $pParams = null): mixed {
        return BSO_Admin::Read_Raw($pParams);
    }

    /**
     * Delete Raw tables
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Delete_Raw(mixed $pParams = null): mixed {
        return BSO_Admin::Delete_Raw($pParams);
    }


    /**
     * Create sync
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Create_Sync(mixed $pParams = null): mixed {
        return BSO_Admin::Create_Sync($pParams);
    }

    /**
     * Create batch
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Create_Batch(mixed $pParams = null): mixed {
        return BSO_Admin::Create_Batch($pParams);
    }

    /**
     * Delete Batch
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Delete_Batch(mixed $pParams = null): mixed {
        return BSO_Admin::Delete_Batch($pParams);
    }

    /**
     * Read sync draft
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Sync_Draft(mixed $pParams = null): mixed {
        return BSO_Admin::Read_Sync_Draft($pParams);
    }

    /**
     * Read sync active
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Sync_Active(mixed $pParams = null): mixed {
        return BSO_Admin::Read_Sync_Active($pParams);
    }

    /**
     * Update sync to publish
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Update_Sync_Publish(mixed $pParams = null): mixed {
        return BSO_Admin::Update_Sync_Publish($pParams);
    }

    /**
     * Create sync to rollback
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Create_Sync_Rollback(mixed $pParams = null): mixed {
        return BSO_Admin::Create_Sync_Rollback($pParams);
    }

    /**
     * Update sync draft
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Update_Sync_Draft(mixed $pParams = null): mixed {
        return BSO_Admin::Update_Sync_Draft($pParams);
    }

    /* 
     * Admin - Venue
     * ************************************************************************
     */

    /**
     * Read location all
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Location_All(mixed $pParams = null): mixed {
        return BSO_Admin::Read_Location_All($pParams);
    }

    /**
     * Update location 
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Update_Location(mixed $pParams = null): mixed {
        return BSO_Admin::Update_Location($pParams);
    }

    /* 
     * Admin - System
     * ************************************************************************
     */

    /**
     * Read environment parameters
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_EnvironmentStats(mixed $pParams = null): mixed {
        return BSO_Admin::Read_EnvironmentStats($pParams);
    }

    /**
     * Read traffic stats
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_TrafficStats(mixed $pParams = null): mixed {
        return BSO_Admin::Read_TrafficStats($pParams);
    }

    /**
     * Read cache stats
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_CacheStats(mixed $pParams = null): mixed {
        return BSO_Admin::Read_CacheStats($pParams);
    }

    /**
     * Flush cache
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Delete_Cache(mixed $pParams = null): mixed {
        return BSO_Admin::Delete_Cache($pParams);
    }

    /**
     * Query IP information
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_QueryIp(mixed $pParams = null): mixed {
        return BSO_Admin::Read_QueryIp($pParams);
    }

    /**
     * Update IP block
     * 
     * @param mixed $pParams
     * @return mixed
     */
    public static function Update_IpBlock(mixed $pParams = null): mixed {
        return BSO_Admin::Update_IpBlock($pParams);
    }

    /**
     * Read log
     * 
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Log(mixed $pParams = null): mixed {
        return BSO_Admin::Read_Log($pParams);
    }

    /**
     * Read log history
     * 
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_LogHistory(mixed $pParams = null): mixed {
        return BSO_Admin::Read_LogHistory($pParams);
    }

    /**
     * Delete log history
     * 
     * @param mixed $pParams
     * @return mixed
     */
    public static function Delete_LogHistory(mixed $pParams = null): mixed {
        return BSO_Admin::Delete_LogHistory($pParams);
    }
}
