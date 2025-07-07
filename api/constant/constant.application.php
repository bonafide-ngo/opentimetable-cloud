<?
// Namespace
define('APP_NAMESPACE', 'App');

// Webhooks
define('APP_WEBHOOK_UPGRADE', 'upgrade');
define('APP_WEBHOOK_BATCH', 'batch');
define('APP_WEBHOOK_CLEANUP', 'cleanup');
define('APP_WEBHOOK_SYNC', 'sync');
define('APP_WEBHOOK_TRAFFIC', 'traffic');
define('APP_WEBHOOK_IPBLOCKLIST', 'ipblocklist');
define('APP_WEBHOOK_CUSTOM', 'custom');

// Blocklist
// https://www.blocklist.de/en/export.html
define('APP_IP_BLOCKLIST_URL_GETALL', 'https://lists.blocklist.de/lists/all.txt');
define('APP_IP_BLOCKLIST_URL_GETLAST', 'https://api.blocklist.de/getlast.php?time={0}');
define('APP_IP_BLOCKLIST_URL_INFO', 'https://api.blocklist.de/api.php?server={0}&apiKey={1}&ip={2}&format={3}');
define('APP_IP_BLOCKLIST_VALIDITY', 1800 + 60); // updated every 30 minutes + 1 minute contingency
define('APP_IP_BLOCKLIST_SERVER_ID', 7020);
define('APP_IP_BLOCKLIST_SERVER_KEY', 'bdb18b4b61');

// MXToolBox
define('APP_MXTOOLBOX_URL', 'https://api.mxtoolbox.com/api/v1/Lookup/blocklist/?argument={0}');
define('APP_MXTOOLBOX_KEY', '9bfd4732-c69a-460b-a92a-2df8bfdd9c70');

// SMS properties
define('APP_SMS_API_KEY', 'api_key');
define('APP_SMS_API_SECRET', 'api_secret');
// SMS providers (see environment)
define('APP_SMS_PROVIDER_VONAGE', 'vonage');

// MSAL Groups, must match config.json
define('APP_MSAL_GROUP_STUDENT', 'student');
define('APP_MSAL_GROUP_REVIEWER', 'reviewer');
define('APP_MSAL_GROUP_STAFF', 'staff');
define('APP_MSAL_GROUP_ADMIN', 'admin');

// Sync
define('APP_SYNC_LOCK', 'sync_lock');
define('APP_SYNC_USER', 'auto-sync');
define('APP_SYNC_DB_SUCCESS', 'success');
define('APP_SYNC_DB_PENDING', 'pending');
define('APP_SYNC_DB_ERROR', 'error');

// Semester
define('APP_SEMESTER_1', 1);
define('APP_SEMESTER_2', 2);
