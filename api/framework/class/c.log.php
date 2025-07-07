<?

/**
 * Log Class
 */
class Log {
	const DEBUG             = 'debug';
	const ERROR             = 'error';
	const REPORT            = 'report';
	const FILE_NAME         = 'log';
	const PATTERN_LOG       = '|^[a-zA-Z0-9\\.@_]*$|'; // log.2022.03.01@13.30.11_3b4d99d2b8f45907260b487e8d714d92e123b6f3

	/**
	 * Error messages in the same order the errors were raised
	 */
	private static $mErrors = [];

	protected static $mPath          	= 'log/';
	protected static $mPathAbs       	= '/';
	protected static $mMaxsize       	= 1048576; // 1MB
	protected static $mWebmasterEmail   = '';
	protected static $mWebmasterAlias   = '';

	/**
	 * Initialize Log
	 *
	 * @param array $pParams
	 * @return void
	 */
	public static function Initialise(array $pParams = array()) {
		self::$mPath             	= array_key_exists('path', $pParams)            	? $pParams['path']          	: self::$mPath;
		self::$mPathAbs             = array_key_exists('path_abs', $pParams)            ? $pParams['path_abs']          : self::$mPathAbs;
		self::$mMaxsize          	= array_key_exists('maxsize', $pParams)         	? $pParams['maxsize']       	: self::$mMaxsize;
		self::$mWebmasterEmail      = array_key_exists('webmaster_email', $pParams)     ? $pParams['webmaster_email']   : self::$mWebmasterEmail;
		self::$mWebmasterAlias      = array_key_exists('webmaster_alias', $pParams)     ? $pParams['webmaster_alias']   : self::$mWebmasterAlias;

		self::Debug(__FILE__, __METHOD__, __LINE__, ['********************************************************************************', $pParams]);
	}
	/**
	 * Check if an error exists and trow an exeption if any
	 * 
	 * @return boolean
	 */
	public static function Checkpoint() {
		if (!empty(self::$mErrors))
			throw new Exception(var_export(self::$mErrors, true), 999999999);
	}

	/**
	 * 
	 * Breakpoint code execution
	 *
	 * @param mixed $pLog
	 * @param boolean $pDie
	 * @param boolean $pMail
	 * @return void
	 */
	public static function Breakpoint(mixed $pLog = null, bool $pDie = true, bool $pMail = false) {
		$vLog = $pLog ? $pLog : 'Breakpoint';

		// Log the Breakpoint
		self::Debug(__FILE__, __METHOD__, __LINE__, $vLog, $pMail);

		if ($pDie)
			die('<pre>' . var_export($vLog, true) . '</pre>');
	}

	/**
	 * Count number of errors
	 * 
	 * @return integer
	 */
	public static function Count(): int {
		return count(self::$mErrors);
	}

	/**
	 * Add an error to the stack
	 *
	 * @param mixed $pError
	 */
	public static function Add(mixed $pError) {
		self::$mErrors[] = $pError;
	}

	/**
	 * Get all errors
	 *
	 * @return array
	 */
	public static function Get(): array {
		return self::$mErrors;
	}

	/**
	 * Get last error
	 *
	 * @return mixed
	 */
	public static function GetLast(): mixed {
		return self::$mErrors[array_key_last(self::$mErrors)];
	}

	/**
	 * 
	 * PHP error handler
	 *
	 * @param integer $pErrorID
	 * @param string $pErrorDescription
	 * @param string $pErrorFilename
	 * @param integer $pErrorLine
	 * @return void
	 */
	public static function ErrorHandler(int $pErrorID, string $pErrorDescription, string $pErrorFilename = null, int $pErrorLine = null) {
		switch ($pErrorID) {
			case 0: // Suppressed code by @operator
				return true;
				break;
			case E_ALL: // 30719
				$vErrorCode = 'E_ALL';
				break;
			case E_ERROR: // 1
				$vErrorCode = 'E_ERROR';
				break;
			case E_RECOVERABLE_ERROR: // 4096
				$vErrorCode = 'E_RECOVERABLE_ERROR';
				break;
			case E_WARNING: // 2
				$vErrorCode = 'E_WARNING';
				break;
			case E_PARSE: // 4
				$vErrorCode = 'E_PARSE';
				break;
			case E_NOTICE: // 8
				$vErrorCode = 'E_NOTICE';
				break;
			case E_STRICT: // 2048
				$vErrorCode = 'E_STRICT';
				break;
			case E_CORE_ERROR: // 16
				$vErrorCode = 'E_CORE_ERROR';
				break;
			case E_CORE_WARNING: // 32
				$vErrorCode = 'E_CORE_WARNING';
				break;
			case E_COMPILE_ERROR: // 64
				$vErrorCode = 'E_COMPILE_ERROR';
				break;
			case E_COMPILE_WARNING: // 128
				$vErrorCode = 'E_COMPILE_WARNING';
				break;
			case E_USER_ERROR: // 256
				$vErrorCode = 'E_USER_ERROR';
				break;
			case E_USER_WARNING: // 512
				$vErrorCode = 'E_USER_WARNING';
				break;
			case E_USER_NOTICE: // 1024
				$vErrorCode = 'E_USER_NOTICE';
				break;
			case E_DEPRECATED: // 8192
				$vErrorCode = 'E_DEPRECATED';
				break;
			case E_USER_DEPRECATED: // 16384
				$vErrorCode = 'E_USER_DEPRECATED';
				break;
			default:
				$vErrorCode = 'UNKNOWN';
				break;
		}

		if ($pErrorID != E_NOTICE || DEBUG) {
			$pLog  = 	  "PHP " . PHP_VERSION . " [$pErrorID] $vErrorCode:  $pErrorDescription";
			$pLog .= NL . "Error in file $pErrorFilename at line $pErrorLine";

			// Log the error
			self::Error(__FILE__, __METHOD__, __LINE__, $pLog, true);
		}

		// Bypass default PHP error-handler
		return true;
	}

	/**
	 * PHP Exception handler
	 *
	 * @param mixed $pException
	 * @return void
	 */
	public static function ExceptionHandler(mixed $pException) {
		self::Error(__FILE__, __METHOD__, __LINE__, ['Uncaught exception', $pException->getCode() . ': ' . $pException->getMessage()], true);
	}

	/**
	 * PHP Shutdown function
	 *
	 * @param integer $pStatus
	 * @return void
	 */
	public static function ShutdownFunction(int $pStatus = CONNECTION_TIMEOUT) {
		// get last error if any
		$vError = error_get_last();
		if ($vError)
			self::Error(__FILE__, __METHOD__, __LINE__, $vError, true, true);

		if (connection_status() == $pStatus)
			self::Error(__FILE__, __METHOD__, __LINE__, 'Connection timed out', true, true);

		// Terminate the script
		exit();
	}

	/**
	 * Save a Log for reporting
	 *
	 * @param string $pFile
	 * @param string $pMethod
	 * @param integer $pLine
	 * @param mixed $pLog
	 * @param boolean $pMail
	 * @return void
	 */
	public static function Report(string $pFile, string $pMethod, int $pLine, mixed $pLog = null, bool $pMail = false) {
		self::LogHandler($pFile, $pMethod, $pLine, self::REPORT, $pLog, $pMail);
	}

	/**
	 * Save a Log for debugging
	 *
	 * @param string $pFile
	 * @param string $pMethod
	 * @param integer $pLine
	 * @param mixed $pLog
	 * @param boolean $pMail
	 * @return void
	 */
	public static function Debug(string $pFile, string $pMethod, int $pLine, mixed $pLog = null, bool $pMail = false) {
		if (DEBUG)
			self::LogHandler($pFile, $pMethod, $pLine, self::DEBUG, $pLog, $pMail);
	}

	/**
	 * Save a Log because of an error
	 *
	 * @param string $pFile
	 * @param string $pMethod
	 * @param integer $pLine
	 * @param mixed $pLog
	 * @param boolean $pMail
	 * @param boolean $pShutdown
	 * @param boolean $pAddError
	 * @return void
	 */
	public static function Error(string $pFile, string $pMethod, int $pLine, mixed $pLog, bool $pMail = false, bool $pShutdown = false, bool $pAddError = true) {
		if ($pAddError)
			self::Add($pLog);

		self::LogHandler($pFile, $pMethod, $pLine, self::ERROR, $pLog, $pMail, $pShutdown);
	}

	/**
	 * Save a Log into a daily log file
	 *
	 * @param string $pFile
	 * @param string $pMethod
	 * @param integer $pLine
	 * @param string $pType
	 * @param mixed $pLog
	 * @param boolean $pMail
	 * @param boolean $pShutdown
	 * @return void
	 */
	protected static function LogHandler(string $pFile, string $pMethod, int $pLine, string $pType, mixed $pLog = null, bool $pMail = false, $pShutdown = false) {
		global $gMailer;

		// Normalise UTF8 log
		$pLog = !empty($pLog) ? NL . mb_convert_encoding(var_export($pLog, true), 'UTF-8', 'auto') : '';

		// Handle shutdown
		if ($pShutdown)
			$vSubject = 'Fatal log';
		else
			switch ($pType) {
				case self::ERROR:
					$vSubject = 'Error log';
					break;
				case self::REPORT:
					$vSubject = 'Report log';
					break;
				case self::DEBUG:
				default:
					$vSubject = 'Debug log';
					break;
			}

		// PHP is in shutdown, work with absolute paths only
		$vPath = $pShutdown ? self::$mPathAbs : self::$mPath;

		// Set timestamp
		$vDay       = gmdate("d", NOW);
		$vMonth     = gmdate("m", NOW);
		$vYear      = gmdate("Y", NOW);
		$vHours     = gmdate("H", NOW);
		$vMinutes   = gmdate("i", NOW);
		$vSeconds   = gmdate("s", NOW);

		// Set paths
		$vFilePath = $vPath . self::FILE_NAME;
		$vRotatedFilePath = $vPath . self::FILE_NAME . '.' . $vYear . '.' . $vMonth . '.' . $vDay . '@' . $vHours . '.' . $vMinutes . '.' . $vSeconds . '_' . Crypto::RandomUniqueHash(null, 'sha1');

		// Set log
		$vLog  = NL;
		$vLog .= NL . "$vDay/$vMonth/$vYear " . Util::GetConfig('php.date.timezone') . " $vHours:$vMinutes:$vSeconds " . strtoupper($vSubject) . " " . basename($pFile, '.php') . ' >> ' . $pMethod . ':' . $pLine;
		$vLog .= $pLog;

		if ($pMail) {
			$vLog2Mail  =      'IP: ' . Util::GetIP();
			$vLog2Mail .= NL . 'URI: ' . Util::GetURI();
			$vLog2Mail .= NL . 'UserAgent: ' . Util::GetUserAgent();
			$vLog2Mail .= NL;
			$vLog2Mail .= NL . basename($pFile, '.php') . ' >> ' . $pMethod . ' : ' . $pLine;
			$vLog2Mail .= NL . $pLog;

			$vParams  = array();
			$vParams['txt_title']	= Lang::Get('instance.i-title');
			$vParams['txt_header']  = $vSubject;
			$vParams['txt_body']   	= $vLog2Mail;
			$vParams['txt_date']    = "$vDay/$vMonth/$vYear";
			$vParams['txt_time']  	= "$vHours:$vMinutes:$vSeconds";

			$gMailer->clearAllRecipients();
			$gMailer->addAddress(self::$mWebmasterEmail, self::$mWebmasterAlias);
			$gMailer->msgHTML(Util::ParseTemplate(PATH_TEMPLATE . 't.mail.wrapper.html', PATH_TEMPLATE . 't.mail.body.html', $vParams));
			$gMailer->msgPlain(Util::ParseTemplate(PATH_TEMPLATE . 't.mail.wrapper.txt', PATH_TEMPLATE . 't.mail.body.txt', $vParams));
			$gMailer->Mail($vSubject);
		}

		// Log and rotate if oversized
		self::LogAndRotate($vLog, $vPath, $vFilePath, $vRotatedFilePath);
	}

	/**
	 * Log and rotate
	 *
	 * @param string $pLog
	 * @param string $pDirPath
	 * @param string $pFilePath
	 * @param string $pRotatedFilePath
	 * @return void
	 */
	protected static function LogAndRotate(string $pLog, string $pDirPath, string $pFilePath, string $pRotatedFilePath) {
		// Open log
		$vFp = fopen($pFilePath, "a");
		// Write log
		fwrite($vFp, $pLog);
		// Close log
		fclose($vFp);

		// Check size and implement lock semaphore
		// https://docstore.mik.ua/orelly/webprog/pcook/ch18_25.htm
		$vLock = false;
		$vSafeLock = 10;
		$vPathLock = $pDirPath . 'lock';
		try {
			// Catch any issue not to break the code execution
			while (!$vLock && $vSafeLock-- && is_file($pFilePath) && filesize($pFilePath) > self::$mMaxsize) {

				// N.B. file and directory are the same in linux
				if (
					// Check the lock does not exists
					!is_file($vPathLock) && !is_dir($vPathLock)
					// Create lock, suppress warning due to possible race condition
					&& @mkdir($vPathLock)
				) {
					$vLock = true;
					break;
				} else
					usleep(10);
			}
		} catch (\Throwable $e) {
			// Silent catch, carry on
		}

		if ($vLock) {
			// Rotate and unlock
			rename($pFilePath, $pRotatedFilePath);
			rmdir($vPathLock);
		} else if (!$vSafeLock)
			// Unlock avoiding endless loop, but do not rotate yet
			rmdir($vPathLock);
	}
}
