<?

namespace App;

class BSO_Admin {

    /* 
     * Admin - Settings
     * ************************************************************************
     */

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Setting(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Admin_Read_Setting($pParams);

        // Get setting
        $vBindVars = array('stt_code' => $vDTO->code);
        $vValue = \OSQL::_GetValue(PATH_SQL_APPLICATION, 'select_setting', $vBindVars);

        return new \ApiResponse($vValue);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Setting_All(mixed $pParams = null): mixed {
        // Get all settings
        $vSettings = \OSQL::_GetResults(PATH_SQL_ADMIN, 'select_setting_all');

        return new \ApiResponse($vSettings);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Update_SettingFlag(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Admin_Update_SettingFlag($pParams);

        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN, APP_MSAL_GROUP_STAFF));
        // Get user
        $vUser = Common::GetUser(\Util::GetConfig('msal.property.user'));

        // Deactivate current setting
        $vBindVars = array('stt_code' => $vDTO->code);
        \OSQL::_Query(PATH_SQL_ADMIN, 'update_setting', $vBindVars);
        // Insert new setting
        $vBindVars = array(
            'stt_code' => $vDTO->code,
            'stt_flag' => intval($vDTO->flag),
            'stt_text' => null,
            'stt_create_by' => $vUser
        );
        \OSQL::_Query(PATH_SQL_ADMIN, 'insert_setting', $vBindVars);

        return new \ApiResponse(\JsonRpc::SUCCESS);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Update_SettingText(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Admin_Update_SettingText($pParams);

        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN, APP_MSAL_GROUP_STAFF));
        // Get user
        $vUser = Common::GetUser(\Util::GetConfig('msal.property.user'));

        // Deactivate current setting
        $vBindVars = array('stt_code' => $vDTO->code);
        \OSQL::_Query(PATH_SQL_ADMIN, 'update_setting', $vBindVars);
        // Insert new setting
        $vBindVars = array(
            'stt_code' => $vDTO->code,
            'stt_flag' => null,
            'stt_text' => $vDTO->text,
            'stt_create_by' => $vUser
        );
        \OSQL::_Query(PATH_SQL_ADMIN, 'insert_setting', $vBindVars);

        return new \ApiResponse(\JsonRpc::SUCCESS);
    }

    /* 
     * Admin - Sync
     * ************************************************************************
     */

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Sync_All(mixed $pParams = null): mixed {
        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN, APP_MSAL_GROUP_STAFF));

        // Read all sync records
        return new \ApiResponse(\OSQL::_GetResults(PATH_SQL_ADMIN, 'select_sync_all'));
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Sync_Pending(mixed $pParams = null): mixed {
        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN, APP_MSAL_GROUP_STAFF));

        // If a lock is in progress, then don't disturb because it's syncing
        if (\Cache::Get(APP_SYNC_LOCK))
            // Pending sync
            return new \ApiResponse(true);

        // Get latest record
        $vResult = \OSQL::_GetRow(PATH_SQL_ADMIN, 'select_sync_latest');

        switch ($vResult['snc_status']) {
            case APP_SYNC_DB_ERROR:
                return new \ApiResponse(null, \Util::ParseString(\Lang::Get('dynamic.error-batch'), [\Util::GetConfig('url.ticketingSystem')]));
                break;
            case APP_SYNC_DB_SUCCESS:
                return new \ApiResponse(false);
                break;
            case APP_SYNC_DB_PENDING:
            default:
                return new \ApiResponse(true);
                break;
        }
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Update_Sync_Pending(mixed $pParams = null): mixed {
        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN, APP_MSAL_GROUP_STAFF));

        // If a lock is in progress, then don't disturb because it's syncing
        if (\Cache::Get(APP_SYNC_LOCK))
            return new \ApiResponse(true);

        // Get pending records
        $vPendings = \OSQL::_GetResults(PATH_SQL_ADMIN, 'select_sync_pending');

        if (empty($vPendings))
            return new \ApiResponse(false);
        else {
            // Set to error those records older than the sync lock
            foreach ($vPendings as $vPending) {
                // N.B. Cleanup will hard delete OTT data later
                if ($vPending['snc_create_timestamp'] + \Util::GetConfig('validity.syncTimeout') < NOW) {
                    // Update sync status
                    $vBindVars = array(
                        'snc_id' => $vPending['snc_id'],
                        'snc_status' => APP_SYNC_DB_ERROR
                    );
                    \OSQL::_Query(PATH_SQL_BATCH, 'update_sync_status', $vBindVars);
                }
            }

            return new \ApiResponse(true);
        }
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Raw(mixed $pParams = null): mixed {
        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN));

        // If a lock is in progress, then don't disturb because it's syncing
        if (\Cache::Get(APP_SYNC_LOCK))
            return new \ApiResponse(0);

        // Check if raw data exists
        return new \ApiResponse(\OSQL::_GetValue(PATH_SQL_ADMIN, 'select_raw'));
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Delete_Raw(mixed $pParams = null): mixed {
        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN));

        // If a lock is in progress, then don't disturb because it's syncing
        if (\Cache::Get(APP_SYNC_LOCK))
            return new \ApiResponse(null, \Lang::Get('static.error-sync'));

        // Truncate raw tables
        \OSQL::_Query(PATH_SQL_BATCH, 'truncate_raw_timetable');
        \OSQL::_Query(PATH_SQL_BATCH, 'truncate_raw_student');
        \OSQL::_Query(PATH_SQL_BATCH, 'truncate_raw_module');

        return new \ApiResponse(\JsonRpc::SUCCESS);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Create_Sync(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Admin_Create_Sync($pParams);

        // Get user
        if ($vDTO->signedSalsa_base64) {
            if (!\Crypto::GetSignedItem($vDTO->signedSalsa_base64))
                throw new \UnexpectedException();

            // Get user
            $vUser = APP_SYNC_USER;

            // Check autosync setting
            $vBindVars = array('stt_code' => \Util::GetConfig('setting.flag.autosync'));
            if (!\OSQL::_GetValue(PATH_SQL_APPLICATION, 'select_setting', $vBindVars))
                return new \ApiResponse(null, \Lang::Get('static.error-autosync'));
        } else {
            // Check privile
            Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN, APP_MSAL_GROUP_STAFF));
            // Get user
            $vUser = Common::GetUser(\Util::GetConfig('msal.property.user'));
        }

        // Check if already in progress, not matter if via cronjob or manual trigger
        if (\Cache::Get(APP_SYNC_LOCK))
            return new \ApiResponse(null, \Lang::Get('static.error-sync'));

        // Run batch asyncronously
        // Send fire and forget via cURL
        // Attemp HTTP/2 for better perfomance
        \Util::cURL(\Util::GetConfig('url.api'), array(
            REQUEST_WEHBOOK => APP_WEBHOOK_BATCH,
            'signedUser_base64' => \Crypto::SignItem($vUser),
        ), array(), CURL_HTTP_VERSION_2, 0);

        return new \ApiResponse(\JsonRpc::SUCCESS);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Create_Batch(mixed $pParams = null): mixed {
        global $gDBs;

        try {
            // Get params
            $vDTO = new DTO_Admin_Create_Batch($pParams);

            // Get user
            $vUser = \Crypto::GetSignedItem($vDTO->signedUser_base64);
            if (!$vUser)
                throw new \UnexpectedException();

            // Set autopublish
            if ($vUser == APP_SYNC_USER) {
                $vBindVars = array('stt_code' => \Util::GetConfig('setting.flag.autopublish'));
                $vIsAutoPublish = \OSQL::_GetValue(PATH_SQL_APPLICATION, 'select_setting', $vBindVars);
            } else
                $vIsAutoPublish = false;

            // Check if already in progress, not matter if via webhook or manual trigger
            if (\Cache::Get(APP_SYNC_LOCK))
                return new \ApiResponse(null, \Lang::Get('static.error-sync'));

            // Lock sync
            \Cache::Set(APP_SYNC_LOCK, true, \Util::GetConfig('validity.syncTimeout'));

            // Begin transaction
            \OSQL::__TransactionBegin();

            // Insert batch
            if (!\OSQL::_Query(PATH_SQL_BATCH, 'insert_batch'))
                throw new \UnexpectedException();
            $vBatchId = \OSQL::__GetInsertID();

            // Insert sync
            $vBindVars = array(
                'snc_btc_id' => $vBatchId,
                'snc_create_by' => $vUser
            );
            if (!\OSQL::_Query(PATH_SQL_BATCH, 'insert_sync', $vBindVars))
                throw new \UnexpectedException();
            $vSyncId = \OSQL::__GetInsertID();

            // Commit transaction
            // N.B. Allows other users to see sync in progress
            \OSQL::__TransactionCommit();

            try {
                // Increase db session and php script timeouts as data may be slow to pull and insert
                $vSyncTimeout = intval(\Util::GetConfig('validity.syncTimeout'));
                if ($vSyncTimeout) {
                    \OSQL::__Query("SET SESSION wait_timeout = $vSyncTimeout");
                    set_time_limit($vSyncTimeout);
                }

                // Insert raw data
                // N.B. Out of transaction to retain records in case of error for debugging
                $vBindVars = array('btc_id' => $vBatchId);
                if (
                    !\OSQL::_Query(PATH_SQL_INSTANCE, 'insert_raw_timetable', $vBindVars, DB_LINK_DEFAULT, true) ||
                    !\OSQL::_Query(PATH_SQL_INSTANCE, 'insert_raw_student', $vBindVars, DB_LINK_DEFAULT, true) ||
                    !\OSQL::_Query(PATH_SQL_INSTANCE, 'insert_raw_module', $vBindVars, DB_LINK_DEFAULT, true)
                )
                    throw new \UnexpectedException();

                // Begin transaction
                \OSQL::__TransactionBegin();

                // Insert and update ott data
                $vBindVars = array('btc_id' => $vBatchId);
                if (
                    !\OSQL::_Query(PATH_SQL_INSTANCE, 'insert_timetable', $vBindVars, DB_LINK_DEFAULT, true) ||
                    !\OSQL::_Query(PATH_SQL_INSTANCE, 'update_timetable', $vBindVars, DB_LINK_DEFAULT, true) ||
                    !\OSQL::_Query(PATH_SQL_INSTANCE, 'insert_student', $vBindVars, DB_LINK_DEFAULT, true) ||
                    !\OSQL::_Query(PATH_SQL_INSTANCE, 'insert_course', $vBindVars, DB_LINK_DEFAULT, true) ||
                    !\OSQL::_Query(PATH_SQL_INSTANCE, 'insert_department', $vBindVars, DB_LINK_DEFAULT, true) ||
                    !\OSQL::_Query(PATH_SQL_INSTANCE, 'insert_period', $vBindVars, DB_LINK_DEFAULT, true) ||
                    !\OSQL::_Query(PATH_SQL_INSTANCE, 'insert_venue', $vBindVars, DB_LINK_DEFAULT, true)
                )
                    throw new \UnexpectedException();

                // Insert unique location after venue
                // N.B. Run after venue insert and it may retun no updated row count (INSERT IGNORE)
                $vBindVars = array(
                    'btc_id' => $vBatchId,
                    'lct_update_by' => $vUser
                );
                \OSQL::_Query(PATH_SQL_BATCH, 'insert_location', $vBindVars, DB_LINK_DEFAULT, true);
            } catch (\Throwable $e) {
                // Rollback transaction (if any)
                \OSQL::__TransactionRollback();

                // Update sync status
                $vBindVars = array(
                    'snc_id' => $vSyncId,
                    'snc_status' => APP_SYNC_DB_ERROR
                );
                if (!\OSQL::_Query(PATH_SQL_BATCH, 'update_sync_status', $vBindVars))
                    throw new \UnexpectedException();

                throw $e;
            }

            // Update batch end timestamp
            $vBindVars = array('btc_id' => $vBatchId);
            if (!\OSQL::_Query(PATH_SQL_BATCH, 'update_batch_end', $vBindVars))
                throw new \UnexpectedException();

            // Update sync status
            $vBindVars = array(
                'snc_id' => $vSyncId,
                'snc_status' => APP_SYNC_DB_SUCCESS
            );
            if (!\OSQL::_Query(PATH_SQL_BATCH, 'update_sync_status', $vBindVars))
                throw new \UnexpectedException();

            // Handle autopublish
            if ($vIsAutoPublish) {
                // Publish
                $vParams = new \stdClass();
                $vParams->syncId = $vSyncId;
                self::Update_Sync_Publish($vParams, $vUser);
            }

            // Commit transaction
            \OSQL::__TransactionCommit();

            // Dump raw tables
            // N.B. Restore from terminal: gunzip -c mysqldump_batch_VERSION_TIMESTAMP.sql.gz | mysql -u USER -p DATABASE
            exec(
                'mysqldump'
                    . ' --host=' . $gDBs[DB_LINK_MYSQLDUMP]->mHost
                    . ' --port=' . $gDBs[DB_LINK_MYSQLDUMP]->mPort
                    . ' --user=' . $gDBs[DB_LINK_MYSQLDUMP]->mUser
                    . ' --password="' . $gDBs[DB_LINK_MYSQLDUMP]->mPassword . '"'
                    . ' ' . $gDBs[DB_LINK_MYSQLDUMP]->mSchema
                    . ' raw_timetable raw_student'
                    . ' | gzip > ' . PATH_SQL_DUMP_ABS . 'mysqldump_batch_' . $vBatchId . '_' . NOW . '.sql.gz',
                $vExecOutput,
                $vExecReturn
            );

            // Check exec error
            if ($vExecReturn !== 0) {
                // Log and mail error
                \Log::ERROR(__FILE__, __METHOD__, __LINE__, array('mysqldump', $vExecOutput, $vExecReturn), true);
                throw new \UnexpectedException();
            }

            // Truncate raw tables
            \OSQL::_Query(PATH_SQL_BATCH, 'truncate_raw_timetable');
            \OSQL::_Query(PATH_SQL_BATCH, 'truncate_raw_student');
            \OSQL::_Query(PATH_SQL_BATCH, 'truncate_raw_module');

            // Unlock sync
            \Cache::Delete(APP_SYNC_LOCK);

            // Send success email to business
            \App\Common::Send_Email('Timetable Sync - Success', 'The <b>' . ($vUser == APP_SYNC_USER ? 'Auto-Sync ' : 'Sync ') . ($vIsAutoPublish ? '+ Auto-Publish ' : '') . "</b> of the Timetable database to <b>Version $vSyncId</b> completed successfully.", \Util::GetConfig('email.timetable')[0], \Util::GetConfig('email.timetable')[1]);
        } catch (\Throwable $e) {
            // Unlock sync
            \Cache::Delete(APP_SYNC_LOCK);

            // Send error email to business
            \App\Common::Send_Email('Timetable Sync - Fail', 'The <b>' . ($vUser == APP_SYNC_USER ? 'Auto-Sync ' : 'Sync ') . ($vIsAutoPublish ? '+ Auto-Publish ' : '') . "</b> of the Timetable database to <b>Version $vSyncId</b> failed. Please log an <a href=\"" . \Util::GetConfig('url.ticketingSystem') . "\" target=\"_blank\">Issue</a>", \Util::GetConfig('email.timetable')[0], \Util::GetConfig('email.timetable')[1]);

            throw $e;
        }

        // Run cleanup asyncronously
        // Send fire and forget via cURL
        // Attemp HTTP/2 for better perfomance
        \Util::cURL(\Util::GetConfig('url.api'), array(
            REQUEST_WEHBOOK => APP_WEBHOOK_CLEANUP,
            'signedSalsa_base64' => \Crypto::SignItem(SALSA),
        ), array(), CURL_HTTP_VERSION_2, 0);

        return new \ApiResponse(\JsonRpc::SUCCESS);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Delete_Batch(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Admin_Delete_Batch($pParams);

        // Check signature
        if (!\Crypto::GetSignedItem($vDTO->signedSalsa_base64))
            throw new \UnexpectedException();

        // Get latest syncs to retain
        $vRetainers = \OSQL::_GetResults(PATH_SQL_ADMIN, 'select_sync_keep');
        if (!empty($vRetainers)) {
            foreach ($vRetainers as $vRetainer) {
                $vSyncIds[] = $vRetainer['snc_id'];
            }
            // Soft delete syncs NOT to retain
            $vBindVars = array('in_retainers' => $vSyncIds);
            \OSQL::_Query(PATH_SQL_ADMIN, 'update_sync_delete', $vBindVars);
        }

        // Commit transaction
        \OSQL::__TransactionCommit();

        // Cleanup out of transaction
        // Hard delete OTT data for soft deleted or error syncs
        \OSQL::_Query(PATH_SQL_BATCH, 'delete_course');
        \OSQL::_Query(PATH_SQL_BATCH, 'delete_department');
        \OSQL::_Query(PATH_SQL_BATCH, 'delete_venue');
        \OSQL::_Query(PATH_SQL_BATCH, 'delete_period');
        \OSQL::_Query(PATH_SQL_BATCH, 'delete_student');
        \OSQL::_Query(PATH_SQL_BATCH, 'delete_timetable');

        // Delete SQL dumps
        $vDeletes = \OSQL::_GetResults(PATH_SQL_ADMIN, 'select_sync_delete');
        if (!empty($vDeletes))
            foreach ($vDeletes as $vDelete) {
                \Util::SafeUnlink(PATH_SQL_DUMP . 'mysqldump_batch_' . $vDelete['btc_id'] . '_*.sql.gz');
            }

        return new \ApiResponse(\JsonRpc::SUCCESS);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Sync_Draft(mixed $pParams = null): mixed {
        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_REVIEWER));

        // Check draft setting
        $vBindVars = array('stt_code' => \Util::GetConfig('setting.flag.draft'));
        if (!\OSQL::_GetValue(PATH_SQL_APPLICATION, 'select_setting', $vBindVars))
            return new \ApiResponse(null);

        // Read draft
        return new \ApiResponse(\OSQL::_GetValue(PATH_SQL_ADMIN, 'select_sync_draft'));
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Sync_Active(mixed $pParams = null): mixed {
        // Read active
        return new \ApiResponse(\OSQL::_GetValue(PATH_SQL_ADMIN, 'select_sync_active'));
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @param string|null $pUser
     * @return mixed
     */
    public static function Update_Sync_Publish(mixed $pParams = null, ?string $pUser = null): mixed {
        // Get params
        $vDTO = new DTO_Admin_Update_Sync_Publish($pParams);

        // Check for autopublish by autosync
        if ($pUser == APP_SYNC_USER)
            $vUser = $pUser;
        else {
            // Check privile
            Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN, APP_MSAL_GROUP_STAFF));
            // Get user
            $vUser = Common::GetUser(\Util::GetConfig('msal.property.user'));
        }

        // Unpublish active sync
        \OSQL::_Query(PATH_SQL_ADMIN, 'update_sync_unpublish');

        // Publish sync
        $vBindVars = array(
            'snc_id' => $vDTO->syncId,
            'snc_live_by' => $vUser
        );
        if (!\OSQL::_Query(PATH_SQL_ADMIN, 'update_sync_publish', $vBindVars))
            throw new \DataException();

        // Align draft to avoid confusion  
        $vParams = new \stdClass();
        $vParams->syncId = $vDTO->syncId;
        self::Update_Sync_Draft($vParams, $pUser);

        return new \ApiResponse(\JsonRpc::SUCCESS);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Create_Sync_Rollback(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Admin_Create_Sync_Rollback($pParams);

        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN, APP_MSAL_GROUP_STAFF));

        // Check if a sync is already in progress, not matter if via cronjob or manual trigger
        if (\Cache::Get(APP_SYNC_LOCK))
            return new \ApiResponse(null, \Lang::Get('static.error-sync'));

        // Get user
        $vUser = Common::GetUser(\Util::GetConfig('msal.property.user'));

        // Clone sync
        $vBindVars = array('snc_id' => $vDTO->syncId);
        if (!\OSQL::_Query(PATH_SQL_ADMIN, 'insert_sync_rollback', $vBindVars))
            throw new \DataException();
        $vNewSyncId = \OSQL::__GetInsertID();

        // Unpublish active sync
        \OSQL::_Query(PATH_SQL_ADMIN, 'update_sync_unpublish');

        // Publish new sync
        $vBindVars = array(
            'snc_id' => $vNewSyncId,
            'snc_live_by' => $vUser
        );
        if (!\OSQL::_Query(PATH_SQL_ADMIN, 'update_sync_publish', $vBindVars))
            throw new \DataException();

        // Align draft to avoid confusion  
        $vParams = new \stdClass();
        $vParams->syncId = $vNewSyncId;
        self::Update_Sync_Draft($vParams);

        return new \ApiResponse(\JsonRpc::SUCCESS);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @param string|null $pUser
     * @return mixed
     */
    public static function Update_Sync_Draft(mixed $pParams = null, ?string $pUser = null): mixed {
        // Get params
        $vDTO = new DTO_Admin_Update_Sync_Draft($pParams);

        // Check for autopublish by autosync
        if ($pUser != APP_SYNC_USER)
            // Check privile
            Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN, APP_MSAL_GROUP_STAFF));

        // Undraft sync
        \OSQL::_Query(PATH_SQL_ADMIN, 'update_sync_undraft');

        // Draft sync
        $vBindVars = array('snc_id' => $vDTO->syncId);
        if (!\OSQL::_Query(PATH_SQL_ADMIN, 'update_sync_draft', $vBindVars))
            throw new \DataException();

        return new \ApiResponse(\JsonRpc::SUCCESS);
    }

    /* 
     * Admin - Location
     * ************************************************************************
     */

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Location_All(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Admin_Read_Location_All($pParams);

        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN, APP_MSAL_GROUP_STAFF));

        // Check sync id
        $vBindVars = array('snc_id' => $vDTO->syncId);
        if (!\OSQL::_GetRow(PATH_SQL_APPLICATION, 'select_sync_timetable', $vBindVars))
            return new \ApiResponse(array());

        // Read all location records
        $vBindVars = array('snc_id' => $vDTO->syncId);
        return new \ApiResponse(\OSQL::_GetResults(PATH_SQL_ADMIN, 'select_location_all', $vBindVars));
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Update_Location(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Admin_Update_Location($pParams);

        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN, APP_MSAL_GROUP_STAFF));

        // Get user
        $vUser = Common::GetUser(\Util::GetConfig('msal.property.user'));

        // Update all location records
        $vBindVars = array(
            'lct_id' => $vDTO->locationId,
            'lct_latitude' => floatval($vDTO->latitude) ? floatval($vDTO->latitude) : null,
            'lct_longitude' => floatval($vDTO->longitude) ? floatval($vDTO->longitude) : null,
            'lct_update_by' => $vUser,
        );
        if (!\OSQL::_Query(PATH_SQL_ADMIN, 'update_location', $vBindVars))
            throw new \DataException();

        return new \ApiResponse(\JsonRpc::SUCCESS);
    }

    /* 
     * Admin - System
     * ************************************************************************
     */

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_EnvironmentStats(mixed $pParams = null): mixed {
        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN));

        $vResponse = new DTO_Admin_Read_EnvironmentStats_Response();
        $vResponse->benchmark = \Benchmark::Stats(false);
        // Get php info from buffer to avoid sending headers
        ob_start();
        phpinfo();
        $vResponse->phpinfo = ob_get_contents();
        ob_end_clean();

        return new \ApiResponse($vResponse);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_TrafficStats(mixed $pParams = null): mixed {
        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN));

        $vResponse = new DTO_Admin_Read_TrafficStats_Response();
        $vResponse->top = \OSQL::_GetResults(PATH_SQL_ADMIN, 'select_traffic_top');
        $vResponse->avgHits = intval(\OSQL::_GetValue(PATH_SQL_ADMIN, 'select_traffic_avg4hits'));
        $vResponse->avgSizeIn = intval(\OSQL::_GetValue(PATH_SQL_ADMIN, 'select_traffic_avg4size_in'));
        $vResponse->avgSizeOut = intval(\OSQL::_GetValue(PATH_SQL_ADMIN, 'select_traffic_avg4size_out'));
        $vResponse->avgTime = intval(\OSQL::_GetValue(PATH_SQL_ADMIN, 'select_traffic_avg4time'));
        return new \ApiResponse($vResponse);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_CacheStats(mixed $pParams = null): mixed {
        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN));

        // Cache
        $vStats = \Cache::Stats();

        $vResponse = new DTO_Admin_Read_CacheStats_Response();
        $vResponse->uptime = $vStats['uptime'];
        $vResponse->hits = ['in' => $vStats['cmd_set'], 'out' => $vStats['cmd_get']];
        $vResponse->size = ['in' => $vStats['bytes_read'], 'out' => $vStats['bytes_written']];
        return new \ApiResponse($vResponse);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Delete_Cache(mixed $pParams = null): mixed {
        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN));

        // Flush cache
        \Cache::Flush();

        return new \ApiResponse(\JsonRpc::SUCCESS);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_QueryIp(mixed $pParams = null): mixed {
        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN));

        // Get params
        $vDTO = new DTO_Admin_Read_QueryIp($pParams);

        // Get country
        $vCountry = \Util::GetCountry($vDTO->ip);

        $vResponse = new DTO_Admin_Read_QueryIp_Response();
        $vResponse->geoIsoCode = $vCountry ? $vCountry->isoCode : null;
        $vResponse->geoName = $vCountry ? $vCountry->name : null;
        $vResponse->information = \Security::QueryIpInformation($vDTO->ip);
        $vResponse->blocklists = \Security::QueryIpBlocklists($vDTO->ip);
        $vResponse->isBlocked = \Security::IsIpBlocked($vDTO->ip);
        return new \ApiResponse($vResponse);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Update_IpBlock(mixed $pParams = null): mixed {
        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN));

        // Get params
        $vDTO = new DTO_Admin_Update_IpBlock($pParams);

        if ($vDTO->block ? \Security::SetIpBlock($vDTO->ip) : \Security::DeleteIpBlock($vDTO->ip))
            return new \ApiResponse(\JsonRpc::SUCCESS);
        else
            return new \ApiResponse(null, \Lang::Get('static.error-unblock-ip'));
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Log(mixed $pParams = null): mixed {
        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN));

        // Get params
        $vDTO = new DTO_Admin_Read_Log($pParams);

        // Get log filepath
        $vFilepath = PATH_LOG . $vDTO->log;
        return new \ApiResponse(is_file($vFilepath) ? file_get_contents($vFilepath) : '');
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_LogHistory(mixed $pParams = null): mixed {
        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN));

        // Init response
        $vResponse = new DTO_Admin_Read_LogHistory_Response();

        // Scan log folder and sort descending
        $vScan = scandir(PATH_LOG, SCANDIR_SORT_DESCENDING);
        if (!empty($vScan))
            foreach ($vScan as $vFilename) {
                // Do not get system elements and the last log
                if (!in_array($vFilename, array('.', '..', '.empty', 'log'))) {
                    $vResponse->filenames[] = $vFilename;
                    $vResponse->filesizes[] = filesize(PATH_LOG . $vFilename);
                }
            }

        return new \ApiResponse($vResponse);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Delete_LogHistory(mixed $pParams = null): mixed {
        // Check privile
        Common::CheckUserInGroups(array(APP_MSAL_GROUP_ADMIN));

        // Scan log folder unsorted
        $vFilenames = scandir(PATH_LOG, SCANDIR_SORT_NONE);
        if (!empty($vFilenames))
            foreach ($vFilenames as $vFilename) {
                // Do not delete system elements and the last log
                if (!in_array($vFilename, array('.', '..', '.empty', 'log', 'lock')))
                    unlink(PATH_LOG . $vFilename);
            }

        return new \ApiResponse(\JsonRpc::SUCCESS);
    }
}
