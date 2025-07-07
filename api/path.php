<?

/**
 * Root's depth
 *
 * @return string
 */
function Ignite_GetRootPath(): string {
	$vPath = '';
	while (!is_file($vPath . 'root.php'))
		$vPath .= '../';
	return $vPath;
}

// Environment
define('ENVIRONMENT', 			basename(dirname(__FILE__) . '/'));

// Paths
define('PATH_ROOT', 			Ignite_GetRootPath());
define('PATH_CONFIG', 			PATH_ROOT . 'config/');
define('PATH_IMG', 				PATH_ROOT . 'img/');
define('PATH_LANG', 			PATH_ROOT . 'lang/');
// API paths
define('PATH_API', 				PATH_ROOT . ENVIRONMENT . '/');
define('PATH_LOG', 				PATH_API . 'log/');
define('PATH_LOG_ABS', 			getcwd() . '/' . 'log/');
define('PATH_CACHE', 			PATH_API . 'cache/');
define('PATH_CONSTANT', 		PATH_API . 'constant/');
define('PATH_SESSION', 			PATH_API . 'session/');
define('PATH_CONSTANT_ENV', 	PATH_CONSTANT . 'env/');
define('PATH_CONSTANT_DKIM', 	PATH_CONSTANT . 'dkim/');
// SQL paths
define('PATH_SQL', 				PATH_ROOT . 'sql/');
define('PATH_SQL_FRAMEWORK',	PATH_SQL . 'framework/');
define('PATH_SQL_APPLICATION',	PATH_SQL . 'application/');
define('PATH_SQL_DUMP',			PATH_SQL . 'dump/');
define('PATH_SQL_DUMP_ABS',		getcwd() . '/' . PATH_SQL_DUMP);
define('PATH_SQL_ADMIN',		PATH_SQL_APPLICATION . 'admin/');
define('PATH_SQL_CRON',			PATH_SQL_APPLICATION . 'cron/');
define('PATH_SQL_TIMETABLE',	PATH_SQL_APPLICATION . 'timetable/');
define('PATH_SQL_LECTURE',		PATH_SQL_APPLICATION . 'lecture/');
define('PATH_SQL_VENUE',		PATH_SQL_APPLICATION . 'venue/');
define('PATH_SQL_DEPARTMENT',	PATH_SQL_APPLICATION . 'department/');
define('PATH_SQL_STUDENT',		PATH_SQL_APPLICATION . 'student/');
define('PATH_SQL_MODULE',		PATH_SQL_APPLICATION . 'module/');
define('PATH_SQL_BATCH',		PATH_SQL_APPLICATION . 'batch/');
define('PATH_SQL_INSTANCE',		PATH_SQL_BATCH . 'instance/');
