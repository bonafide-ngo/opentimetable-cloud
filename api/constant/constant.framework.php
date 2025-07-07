<?
// Unique
define('NOW',   time());

// Text
define('NL',    PHP_OS_FAMILY == 'Windows' ? "\r\n" : "\n");
define('TAB',   "\t");

// Paths API based
define('PATH_APP', PATH_API . 'application/');
define('PATH_FRAMEWORK', PATH_API . 'framework/');

// Paths Framework based
define('PATH_TEMPLATE', PATH_FRAMEWORK . 'template/');
define('PATH_CLASS', PATH_FRAMEWORK . 'class/');
define('PATH_LIB', PATH_FRAMEWORK . 'lib/');

// Requests 
define('REQUEST_WEHBOOK', 'webhook');
