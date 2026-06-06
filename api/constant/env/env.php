<?
// Error level
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);

// MemCacheD
define('MEMCACHED_SERVERS', [['', 11211]]); // MemCacheD Servers' host, port or null
define('MEMCACHED_SESSION', false); // MemCacheD for session handling, else file system

// Database
define('DB', array(
    DB_LINK_MASTER => array(
        DB_DEBUG => DEBUG,
        DB_TYPE => 'OSQL_MYSQL',
        DB_HOST => '',
        DB_PORT => 3306,
        DB_SCHEMA => '',
        DB_USER => '',
        DB_PASSWORD => ''
    ),
    DB_LINK_MYSQLDUMP => array(
        DB_DEBUG => DEBUG,
        DB_TYPE => 'OSQL_MYSQL',
        DB_HOST => '',
        DB_PORT => 3306,
        DB_SCHEMA => '',
        DB_USER => '',
        DB_PASSWORD => ''
    )
));

// SMS (see environment)
define('SMS_PROVIDER', '');
define('SMS_SENDER', ''); // Max 11 chars
define('SMS_DEBUG_MOBILE', false);
define('SMS_CREDENTIALS', array(
    APP_SMS_PROVIDER_VONAGE => array(
        APP_SMS_API_KEY => null,
        APP_SMS_API_SECRET => null
    )
));

// SMTP
define('SMTP_HOST', null);
define('SMTP_PORT', null);
define('SMTP_USERNAME', null);
define('SMTP_PASSWORD', null);
define('SMTP_AUTHENTICATION', true);
define('SMTP_SECURE', 'ssl');

// DKIM
// Sync against mail server, enable PHP's DKIM for hosting (no own DNS server)
define('DKIM', false);
define('DKIM_DOMAIN', null);
define('DKIM_SELECTOR', null);

// Salsa
define('SALSA', '');

// Crypto
/**
 * Implement in webhook=custom
 * $vKeyPair = sodium_crypto_sign_keypair();
 * Log::Breakpoint([sodium_bin2base64(sodium_crypto_sign_publickey($vKeyPair), SODIUM_BASE64_VARIANT_ORIGINAL), sodium_bin2base64(sodium_crypto_sign_secretkey($vKeyPair), SODIUM_BASE64_VARIANT_ORIGINAL)]);
 */
// sodium_crypto_sign_keypair, sodium_crypto_sign_publickey, sodium_crypto_sign_secretkey 
define('CRYPTO_SIGN_PUBLIC_KEY', '');
define('CRYPTO_SIGN_SECRET_KEY', '');

// Firebase
// Manage Service Accounts >> Keys
define('APP_FIREBASE_PROJECT_ID', null);
define('APP_FIREBASE_SCOPE', 'https://www.googleapis.com/auth/cloud-platform');
define('APP_FIREBASE_OAUTH2', '{}');
