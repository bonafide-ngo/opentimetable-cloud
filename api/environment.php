<?
// Constants
require_once(PATH_CONSTANT . 'constant.db.php');
require_once(PATH_CONSTANT . 'constant.framework.php');
require_once(PATH_CONSTANT . 'constant.application.php');

// Constant environemnt
require_once(PATH_CONSTANT_ENV . 'env.php');

// Framework - Device Detector (Composer PHP 8.3.6)
// https://gitea.bonafide.ngo/bonafide.ngo/device-detector
// N.B. Diff client/mobile_apps.yml
require_once(PATH_LIB . 'device-detector/6.4.1/vendor/autoload.php');

// Framework - GeoIp2 (Composer PHP 8.3.6)
// https://gitea.bonafide.ngo/bonafide.ngo/GeoIP2-php
require_once(PATH_LIB . 'geoip2/2.13.0/vendor/autoload.php');

// Framework - Google Auth (Composer PHP 8.3.6)
// https://gitea.bonafide.ngo/bonafide.ngo/google-auth-library-php
require_once(PATH_LIB . 'google-auth-php/1.40.0/vendor/autoload.php');

// Framework - LibPhoneNumber for PHP (Composer PHP 8.3.6)
// https://gitea.bonafide.ngo/bonafide.ngo/libphonenumber-for-php
require_once(PATH_LIB . 'libphonenumber-for-php/8.13.37/vendor/autoload.php');

// Framework - PHP MemCacheD Lock (Composer PHP 8.3.6)
// https://gitea.bonafide.ngo/bonafide.ngo/php-memcached-lock
require_once(PATH_LIB . 'php-memcached-lock/1.0.5/vendor/autoload.php');

// Framework - PHPMailer (Composer PHP 8.3.6)
// https://gitea.bonafide.ngo/bonafide.ngo/PHPMailer
require_once(PATH_LIB . 'phpmailer/6.9.1/vendor/autoload.php');

// Framework - Vonage PHP SDK (Composer PHP 8.3.6)
// https://gitea.bonafide.ngo/bonafide.ngo/vonage-php-sdk
require_once(PATH_LIB . 'vonage-php-sdk/4.0.0/vendor/autoload.php');

// Framework - Firebase PHP JWT (Composer PHP 8.3.6)
// https://gitea.bonafide.ngo/bonafide.ngo/php-jwt
require_once(PATH_LIB . 'firebase-php-jwt/6.11.0/vendor/autoload.php');

// Framework - OSQL
require_once(PATH_CLASS . 'c.osql.php');
require_once(PATH_CLASS . 'c.osql.mysql.php');
// Framework - Mail
require_once(PATH_CLASS . 'c.mailer.php');
// Framework - Classes
require_once(PATH_CLASS . 'c.util.php');
require_once(PATH_CLASS . 'c.lang.php');
require_once(PATH_CLASS . 'c.log.php');
require_once(PATH_CLASS . 'c.security.php');
require_once(PATH_CLASS . 'c.benchmark.php');
require_once(PATH_CLASS . 'c.cache.php');
require_once(PATH_CLASS . 'c.validate.php');
require_once(PATH_CLASS . 'c.image.php');
require_once(PATH_CLASS . 'c.jsonrpc.php');
require_once(PATH_CLASS . 'c.crypto.php');
require_once(PATH_CLASS . 'c.msal.php');
require_once(PATH_CLASS . 'c.dto.php');
require_once(PATH_CLASS . 'c.vti.php');
require_once(PATH_CLASS . 'c.captcha.php');
require_once(PATH_CLASS . 'c.ott.php');

// Application
require_once(PATH_APP . 'app.override.php');
require_once(PATH_APP . 'app.common.php');
require_once(PATH_APP . 'app.firebase.php');
require_once(PATH_APP . 'app.webhook.php');
