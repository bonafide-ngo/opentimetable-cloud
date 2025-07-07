<?
// Switches
define('DEBUG',             false); // Debugging mode
define('MAINTENANCE',       false); // Maintenance mode
define('STATELESS',         false); // Stateless / Stateful
define('SMTP',              true); // Send emails
define('OG_GZHANDLER',      false); // Compress output instead of web-server
define('UTF8_DECODE_INPUT', false); // UTF-8 decoding to ISO-8859-1
define('REVERSE_PROXY',     null); // Reverse Proxy [null, 'X-Forwarded-For', 'true-client-ip']

// Debugging precursor
ini_set('display_errors',           intval(DEBUG));
ini_set('display_startup_errors',   intval(DEBUG));

// Set paths
require_once('path.php');
