<?
// Benchmark
Benchmark::Start();

// Load Configuration
Util::LoadConfig(PATH_CONFIG . 'config.json');

// Set PHP directives
ini_set('url_rewriter.tags', '');
ini_set('ignore_user_abort', true);
ini_set('date.timezone', Util::GetConfig('php.date.timezone'));

// Set PHP Session directives
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_secure', intval(Util::GetConfig('cookie.option.secure')));
ini_set('session.cookie_samesite', Util::GetConfig('cookie.option.sameSite'));
ini_set('session.cookie_httponly', intval(Util::GetConfig('cookie.property.session.id.httponly')));
ini_set('session.cookie_lifetime', Util::GetConfig('cookie.property.session.id.expires')); // cookie_lifetime = gc_maxlifetime
ini_set('session.gc_maxlifetime', Util::GetConfig('php.session.gc_maxlifetime')); // gc_maxlifetime = cookie_lifetime
ini_set('session.gc_probability', Util::GetConfig('php.session.gc_probability'));
ini_set('session.gc_divisor', Util::GetConfig('php.session.gc_divisor'));
ini_set('session.use_trans_sid', 0);

// Session handling with MemCacheD, else file system
if (MEMCACHED_SESSION) {
    ini_set('session.save_handler', 'memcached');
    ini_set('session.save_path', \Cache::ImplodeServers(MEMCACHED_SERVERS));
    ini_set('memcached.sess_locking', 1); // Force serialization of requests to prevent race conditions
    ini_set('memcached.sess_lock_retries', round(ini_get('max_execution_time') * 1000 / ini_get('memcached.sess_lock_wait_min'))); // sess_lock_wait_min is in ms
} else {
    // Requests are always serialized with file-based sessions
    ini_set('session.save_handler', 'files');
    ini_set('session.save_path', PATH_SESSION);
}

// Error handler
set_error_handler('Log::ErrorHandler');

// Exception handler
set_exception_handler('Log::ExceptionHandler');

// Shutdown function
register_shutdown_function('Log::ShutdownFunction');

// Initialize Util
Util::Initialise(array(
    'reverse_proxy' => REVERSE_PROXY,
    'url' => Util::GetConfig('url.api'),
    'stateless' => STATELESS,
    'session_name' => Util::GetConfig('cookie.property.session.id.name')
));

// Initialize Mailer
Mailer::Initialise(array(
    'timeout' => 9,

    'smtp' => SMTP,
    'smtp_host' => SMTP_HOST,
    'smtp_port' => SMTP_PORT,
    'smtp_username' => SMTP_USERNAME,
    'smtp_password' => SMTP_PASSWORD,
    'smtp_authentication' => SMTP_AUTHENTICATION,
    'smtp_secure' => SMTP_SECURE,

    'webmaster_email' => Util::GetConfig('email.support.0'),
    'webmaster_alias' => Util::GetConfig('email.support.1'),

    'noreply_email' => Util::GetConfig('email.noreply.0'),
    'noreply_alias' => Util::GetConfig('email.noreply.1'),

    'dkim'  => DKIM,
    'dkim_domain' => DKIM_DOMAIN,
    'dkim_private_path' => PATH_CONSTANT_DKIM . 'dkim.pem',
    'dkim_selector' => DKIM_SELECTOR
));

// Initialize Log
Log::Initialise(array(
    'path' => PATH_LOG,
    'path_abs' => PATH_LOG_ABS,
    'log_maxsize' => Util::GetConfig('php.log.maxsize'),
    'webmaster_email' => Util::GetConfig('email.support.0'),
    'webmaster_alias' => Util::GetConfig('email.support.1')
));

// Initialize Security
Security::Initialise(array(
    'ip_blocklist' => (array) Util::GetConfig('ipBlocklist'),
    'utf8_decode_input' => UTF8_DECODE_INPUT
));

// Initialize Language
Lang::Initialise(array(
    'path' => PATH_LANG,
    'languages' => Util::GetConfig('language'),
    'selected' => Util::GetCookie('cookie.property.user.lang')
));

// Initialize CacheControl
Cache::Initialise(array(
    'path' => PATH_CACHE,
    'memcached_servers' => MEMCACHED_SERVERS,
    'ob_gzhandler' => OG_GZHANDLER
));

// Initialize Validate
Validate::Initialise(array(
    'setting_codes' => Util::FlattenArray(Util::GetConfig('setting'))
));

// Initialize Crypto
Crypto::Initialise(array(
    'sign_secret_key' => CRYPTO_SIGN_SECRET_KEY,
    'sign_public_key' => CRYPTO_SIGN_PUBLIC_KEY,
    'salsa' => SALSA
));

// Initialise the IP blocklist
Security::InitIpBlocklist();

// Extend cookies
Util::ExtendCookies(Util::GetConfig('cookie.alive'));

// Initialise DBs
OSQL::Initialise(DB);
