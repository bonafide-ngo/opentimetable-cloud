<?

/**
 * Undocumented function
 *
 * @param string $label
 * @param integer $code
 * @param Throwable|null $previous
 */
class JWTDataException extends Exception {
	// Redefine the exception so message isn't optional
	public function __construct($label = 'static.exception-jwt-data', $code = -32094, Throwable $previous = null) {
		// Make sure everything is assigned properly
		parent::__construct(\Lang::Get($label), $code, $previous);
	}
}

/**
 * Undocumented function
 *
 * @param string $label
 * @param integer $code
 * @param Throwable|null $previous
 */
class JWTUnexpectedException extends Exception {
	// Redefine the exception so message isn't optional
	public function __construct($label = 'static.exception-jwt-unexpected', $code = -32095, Throwable $previous = null) {
		// Make sure everything is assigned properly
		parent::__construct(\Lang::Get($label), $code, $previous);
	}
}

/**
 * Undocumented function
 *
 * @param string $label
 * @param integer $code
 * @param Throwable|null $previous
 */
class DataException extends Exception {
	// Redefine the exception so message isn't optional
	public function __construct($label = 'static.exception-ajax-data', $code = -32096, Throwable $previous = null) {
		// Make sure everything is assigned properly
		parent::__construct(\Lang::Get($label), $code, $previous);
	}
}

/**
 * Undocumented function
 *
 * @param string $label
 * @param integer $code
 * @param Throwable|null $previous
 */
class UnexpectedException extends Exception {
	// Redefine the exception so message isn't optional
	public function __construct($label = 'static.exception-ajax-unexpected', $code = -32097, Throwable $previous = null) {
		// Make sure everything is assigned properly
		parent::__construct(\Lang::Get($label), $code, $previous);
	}
}

/**
 * Undocumented function
 *
 * @param string $label
 * @param integer $code
 * @param Throwable|null $previous
 */
class ParseErrorException extends Exception {
	// Default parse error to fall into final block
	public function __construct() {
		parent::__construct();
	}
}

/**
 * API Class for JSON-RPC 
 */
class JsonRpc {
	// Constants
	const SUCCESS = 'success';

	const JSONRPC_VERSION = '2.0';
	const JSONRPC_MIMETYPE = 'application/json';

	// Properties
	protected static $mAppNamespace = 'App';
	protected static $mJsonRpc_Request = null;
	protected static $mApiResponse = null;
	protected static $mIsVTI = false;
	protected static $mByteIn = 0;
	protected static $mByteOut = 0;
	protected static $mIsIpBlocked = false;

	/**
	 * Listen JsonRpc
	 *
	 * @param string|null $pAppNamespace
	 * @return void
	 */
	public static function Listen(string $pAppNamespace = null) {
		Log::Debug(__FILE__, __METHOD__, __LINE__, 'JSON-RPC interface opened');

		// Set properties
		self::$mAppNamespace = $pAppNamespace ? $pAppNamespace : self::$mAppNamespace;
		self::$mJsonRpc_Request = new JsonRpc_Request();

		try {
			// Mandatory checkpoint
			Log::Checkpoint();

			// Parse request
			list($vNamespace, $vClass, $vMethod) = self::ParseRequest();

			// Check IP blocklist
			self::$mIsIpBlocked = \Security::IsIpBlocked();
			if (self::$mIsIpBlocked)
				self::ParseError(new JsonRpc_Error(-32002, \Lang::Get('static.error-ip-blocked')));

			// Unbox VTI
			self::$mIsVTI = VTI::Unbox(self::$mJsonRpc_Request->params);

			// Check if under maintenance
			if (MAINTENANCE)
				// Bypass maintenance for Admin APIs
				if (!($vNamespace == 'App' && $vClass == 'Admin'))
					self::ParseError(new JsonRpc_Error(-32001));

			// Load references 
			self::LoadReferences($vNamespace, $vClass, $vMethod);

			// Begin transaction
			\OSQL::__TransactionBegin();

			// Get API result
			self::$mApiResponse = self::GetApiResponse($vNamespace, $vClass, $vMethod, self::$mJsonRpc_Request->params);

			// Mandatory checkpoint
			Log::Checkpoint();

			//No API response
			if (!self::$mApiResponse) {
				Log::Error(__FILE__, __METHOD__, __LINE__, "No API response returned by $vNamespace/$vClass::$vMethod", true);
				self::ParseError(new JsonRpc_Error(-32603));
			}
			// Genuine API error
			elseif (self::$mApiResponse->error)
				self::ParseError(new JsonRpc_Error(-32099, null, self::$mApiResponse->error));
			// Genuine API result
			else
				self::ParseResult();
		}
		// Parse Error exception
		catch (ParseErrorException $e) {
			// Fall through finally block
		}
		// JWT exception
		catch (JWTDataException $e) {
			Log::Debug(__FILE__, __METHOD__, __LINE__, 'JWT Data exception');
			self::ParseError(new JsonRpc_Error($e->getCode(), $e->getMessage()), false);
		}
		// JWT exception
		catch (JWTUnexpectedException $e) {
			Log::Debug(__FILE__, __METHOD__, __LINE__, 'JWT Unexpected exception');
			self::ParseError(new JsonRpc_Error($e->getCode(), $e->getMessage()), false);
		}
		// Data exception
		catch (DataException $e) {
			Log::Debug(__FILE__, __METHOD__, __LINE__, 'JSON-RPC Data exception');
			self::ParseError(new JsonRpc_Error($e->getCode(), $e->getMessage()), false);
		}
		// Validation exception
		catch (ValidateException $e) {
			Log::Debug(__FILE__, __METHOD__, __LINE__, 'JSON-RPC Validation exception');
			// Must parse message by language
			self::ParseError(new JsonRpc_Error($e->getCode(), $e->getMessage()), false);
		}
		// Unexpected exception
		catch (UnexpectedException $e) {
			if (!STATELESS)
				// Regenerate potentially invalid session id and prevent endless loop 
				session_regenerate_id(true);

			Log::Debug(__FILE__, __METHOD__, __LINE__, 'JSON-RPC Unexpected exception');
			self::ParseError(new JsonRpc_Error($e->getCode(), $e->getMessage()), false);
		}
		// Catch all other exceptions
		catch (\Throwable $e) {
			Log::Error(__FILE__, __METHOD__, __LINE__, $e->getCode() . ': ' . $e->getMessage(), true);
			self::ParseError(new JsonRpc_Error(-32603), false);
		} finally {
			// Do not log a blocked request
			if (!self::$mIsIpBlocked) {
				// Benchmarking
				Benchmark::Log(self::$mByteIn, self::$mByteOut, self::$mJsonRpc_Request->method);
				Log::Debug(__FILE__, __METHOD__, __LINE__, 'JSON-RPC interface closed');
			}
		}
	}

	/**
	 * Invoke JsonRpc 
	 *
	 * @param string $pMethod
	 * @param object $pParams
	 * @return void
	 */
	public static function Invoke(string $pMethod, object $pParams) {
		// Populate the JSON-RPC request
		$vRequest = new \stdClass();
		$vRequest->jsonrpc = \JsonRpc::JSONRPC_VERSION;
		$vRequest->method = $pMethod;
		$vRequest->params = $pParams;

		// Set postraw
		$_REQUEST[\Security::POSTRAW] = json_encode($vRequest);

		// Listen JsonRpc
		self::Listen();
	}

	/**
	 * Parse request
	 *
	 * @return array
	 */
	private static function ParseRequest(): array {
		// Verify a request exists
		if (!isset($_REQUEST[Security::POSTRAW]))
			self::ParseError(new JsonRpc_Error(-32700));

		// Set inbound size
		self::$mByteIn = strlen($_REQUEST[Security::POSTRAW]);

		// Extract the request
		$vRequest = json_decode($_REQUEST[Security::POSTRAW] ?? null);
		if (!$vRequest)
			self::ParseError(new JsonRpc_Error(-32700));

		// Populate the JSON-RPC request from the payload 
		self::$mJsonRpc_Request->jsonrpc = $vRequest->jsonrpc ?? null;
		self::$mJsonRpc_Request->method = $vRequest->method ?? null;
		self::$mJsonRpc_Request->params = $vRequest->params ?? null;
		self::$mJsonRpc_Request->id = $vRequest->id ?? null;

		Log::Debug(__FILE__, __METHOD__, __LINE__, self::$mJsonRpc_Request);

		// Check version and method exist
		if (
			!self::$mJsonRpc_Request->jsonrpc
			|| !self::$mJsonRpc_Request->method
		)
			self::ParseError(new JsonRpc_Error(-32600));

		// Check version number
		if (self::$mJsonRpc_Request->jsonrpc != self::JSONRPC_VERSION)
			self::ParseError(new JsonRpc_Error(-32000));

		// Parse the method, class, namespace
		// N.B. Stick to naming convention: namespace.class.method
		$vExplodedMethod = explode('.', self::$mJsonRpc_Request->method);
		if (count($vExplodedMethod) == 2) {
			$vExplodedMethod[2] = $vExplodedMethod[1] ?? ''; // Method
			$vExplodedMethod[1] = $vExplodedMethod[0] ?? ''; // Class
			$vExplodedMethod[0] = ''; // Namespace
		} else {
			// Namespace.Class.Method
			$vExplodedMethod[2] = $vExplodedMethod[2] ?? ''; // Method
			$vExplodedMethod[1] = $vExplodedMethod[1] ?? ''; // Class
			$vExplodedMethod[0] = $vExplodedMethod[0] ?? ''; // Namespace
		}

		// Check Application namespace and VTI Initialisation
		if (
			$vExplodedMethod[0] == self::$mAppNamespace
			|| ($vExplodedMethod[1] == VTI::$mClass && $vExplodedMethod[2] == VTI::$mInit)
		)
			return array($vExplodedMethod[0], $vExplodedMethod[1], $vExplodedMethod[2]);
		else {
			self::ParseError(new JsonRpc_Error(-32601));
			return array();
		}
	}

	/**
	 * Parse the request
	 *
	 * @param string $pNamespace
	 * @param string $pClass
	 * @param string $pMethod
	 * @return void
	 */
	public static function LoadReferences(string $pNamespace, string $pClass, string $pMethod = null) {
		// Do not load references if VTI initialisation is in progress
		if ($pClass == VTI::$mClass && $pMethod == VTI::$mInit)
			return;

		$vClassNormalised = strtolower($pClass);

		// Load API
		$vPathApi = PATH_APP . $vClassNormalised . '/' . "api.$vClassNormalised.php";
		if (is_file($vPathApi))
			require_once($vPathApi);
		else
			Log::Debug(__FILE__, __METHOD__, __LINE__, "API reference not found: $vPathApi");

		// Load BSO
		$vPathBso = PATH_APP . $vClassNormalised . '/' . "bso.$vClassNormalised.php";
		if (is_file($vPathBso))
			require_once($vPathBso);
		else
			Log::Debug(__FILE__, __METHOD__, __LINE__, "BSO reference not found: $vPathBso");

		// Load DTO
		$vPathDto = PATH_APP . $vClassNormalised . '/' . "dto.$vClassNormalised.php";
		if (is_file($vPathDto))
			require_once($vPathDto);

		// Check \namespace\class and method exist
		if ($pMethod && !method_exists("$pNamespace\\$pClass", $pMethod))
			self::ParseError(new JsonRpc_Error(-32601));
	}

	/**
	 * Get result by calling the API
	 *
	 * @param string $pNamespace
	 * @param string $pClass
	 * @param string $pMethod
	 * @param mixed $pParams
	 * @return mixed
	 */
	public static function GetApiResponse(string $pNamespace, string $pClass, string $pMethod, mixed $pParams = null): mixed {
		$vCall = "$pNamespace\\$pClass::$pMethod";

		$vResponse = call_user_func($vCall, $pParams);
		if (get_class($vResponse) == 'ApiResponse')
			return $vResponse;
		else {
			Log::Error(__FILE__, __METHOD__, __LINE__, "Invalid class returned by $vCall", true);
			self::ParseError(new JsonRpc_Error(-32603));
			return null;
		}
	}

	/**
	 * Parse the result
	 *
	 * @return void
	 */
	private static function ParseResult() {
		// Commit transaction
		\OSQL::__TransactionCommit();

		if (self::$mIsVTI) {
			// Box VTI
			VTI::Box(self::$mApiResponse->result);
		}

		// Set JSON-RPC response
		$vResponse = new JsonRpc_ResponseResult(self::$mApiResponse->result, self::$mJsonRpc_Request->id);
		Log::Debug(__FILE__, __METHOD__, __LINE__, $vResponse);
		// JSON encode response 
		$vResponse = json_encode($vResponse, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
		// Set outbound size
		self::$mByteOut = strlen($vResponse);

		// Send Headers
		Cache::SendHeaders();

		// Send response
		echo $vResponse;
	}

	/**
	 * Parse the  error
	 * From -32000 to -32099 reserved for implementation-defined errors.
	 *
	 * @param object $pError
	 * @param boolean $pThrowException
	 * @return void
	 */
	private static function ParseError(object $pError, bool $pThrowException = true) {
		if (!$pError->message)
			switch ($pError->code) {
				// Custom codes and messages
				case -32000:
					$pError->message = "Invalid version";
					break;
				case -32001:
					$pError->message = "System maintenance";
					break;
				case -32002:
					$pError->message = "IP blocked";
					break;
				case -32094:
					$pError->message = "JWT Data exception";
					break;
				case -32095:
					$pError->message = "JWT Unexpected exception";
					break;
				case -32096:
					$pError->message = "Data exception";
					break;
				case -32097:
					$pError->message = "Unexpected exception";
					break;
				case -32098:
					$pError->message = "Validation error";
					break;
				case -32099:
					$pError->message = "Application error";
					break;

				// Standard codes and messages
				case -32700:
					$pError->message = "Parse error";
					break;
				case -32600:
					$pError->message = "Invalid request";
					break;
				case -32601:
					$pError->message = "Method not found";
					break;
				case -32603:
				default:
					$pError->code = -32603;
					$pError->message = "Internal error";
					break;
			}

		// Check if to enforce commit despite an error, else roolback
		if (self::$mApiResponse && self::$mApiResponse->enforceCommit)
			// Commit transaction
			\OSQL::__TransactionCommit();
		else
			// Rollback transaction
			\OSQL::__TransactionRollback();

		// Set JSON-RPC response
		$vResponse = new JsonRpc_ResponseError($pError, self::$mJsonRpc_Request->id);
		Log::Debug(__FILE__, __METHOD__, __LINE__, ['Error Code: ' . $pError->code, 'Error Message: ' . $pError->message, 'Error Data: ' . var_export($pError->data, true)]);
		// JSON encode response 
		$vResponse = json_encode($vResponse, JSON_UNESCAPED_UNICODE);
		// Set outbound size
		self::$mByteOut = strlen($vResponse);

		// Send Headers
		Cache::SendHeaders();

		// Send response
		echo $vResponse;

		// Throw parse error exception on demand
		if ($pThrowException)
			throw new ParseErrorException();
	}

	/**
	 * Send a fire and forget JSON-RPC request
	 *
	 * @param string $pUrl
	 * @param string $pMethod
	 * @param mixed $pParams
	 * @param boolean $pEnsureCommit
	 * @return void
	 */
	public static function FireAndForget(string $pUrl, string $pMethod, mixed $pParams, bool $pEnsureCommit = true): void {
		// Enforce commit to sync timing between calls
		if ($pEnsureCommit)
			\OSQL::__TransactionCommit();

		// Build JSON-RPC payload
		$vJsonRpc = new JsonRpc_Request();
		$vJsonRpc->method = $pMethod;
		$vJsonRpc->params = $pParams;
		$vJsonRpc->id = mt_rand();

		// Serialise post data
		$vPost = json_encode($vJsonRpc);
		$vContentLength = strlen($vPost);

		// Extract URL parts
		$vUrlParts = parse_url($pUrl);
		$vUrlParts['path'] = $vUrlParts['path'] ?? '/';
		$vUrlParts['port'] = $vUrlParts['port'] ?? $vUrlParts['scheme'] === 'https' ? 443 : 80;

		// Set user-agent
		$vUserAgent = "PHP " . phpversion();

		// Set request
		$vRequest = "POST {$vUrlParts['path']} HTTP/1.1\r\n";
		$vRequest .= "Host: {$vUrlParts['host']}\r\n";
		$vRequest .= "User-Agent: {$vUserAgent}\r\n";
		$vRequest .= "Content-Length: {$vContentLength}\r\n";
		$vRequest .= "Content-Type: application/json\r\n\r\n";
		$vRequest .= json_encode($vJsonRpc);

		// Set protocol
		$vProtocol = substr($pUrl, 0, 8) === 'https://' ? 'tls://' : '';

		// Open socket
		$vSocket = fsockopen($vProtocol . $vUrlParts['host'], $vUrlParts['port']);
		if (!$vSocket)
			return;

		// Send socket request
		fwrite($vSocket, $vRequest);
		// Close socket request without waiting for reply
		fclose($vSocket);
	}
}

class ApiResponse {
	public $result = null;
	public $error = null;
	public $enforceCommit = false;

	/**
	 * Undocumented function
	 *
	 * @param mixed $result
	 * @param mixed $error
	 * @param boolean $enforceCommit
	 */
	public function  __construct(mixed $result = null, mixed $error = null, bool $enforceCommit = false) {
		$this->result = $result;
		$this->error = $error;
		$this->enforceCommit = $enforceCommit;
	}
}

class JsonRpc_Request {
	public $jsonrpc = JsonRpc::JSONRPC_VERSION;
	public $method = null;
	public $params = null;
	public $id = null;
}

class JsonRpc_ResponseResult {
	public $jsonrpc = JsonRpc::JSONRPC_VERSION;
	public $result = null;
	public $id = null;

	/**
	 *
	 * @param mixed $result
	 * @param string $id
	 */
	public function  __construct(mixed $result = null, string $id = null) {
		$this->result = $result;
		$this->id = $id;
	}
}

class JsonRpc_ResponseError {
	public $jsonrpc = JsonRpc::JSONRPC_VERSION;
	public $error = null;
	public $id = null;

	/**
	 * 
	 * @param mixed $error
	 * @param string $id
	 */
	public function  __construct(mixed $error = null, string $id = null) {
		$this->error = $error;
		$this->id = $id;
	}
}

class JsonRpc_ResponseException {
	public $jsonrpc = JsonRpc::JSONRPC_VERSION;
	public $id = null;

	/**
	 * 
	 * @param string|null $id
	 */
	public function  __construct(?string $id = null) {
		$this->id = $id;
	}
}
class JsonRpc_Error {
	public $code = null;
	public $message = null;
	public $data = null;

	/**
	 * Undocumented function
	 *
	 * @param integer|null $code
	 * @param string|null $message
	 * @param mixed $data
	 */
	public function  __construct(int $code = null, ?string $message = null, mixed $data = null) {
		$this->code = $code;
		$this->message = $message;
		$this->data = $data;
	}
}
