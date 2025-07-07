<?

/**
 * OSQL extension for MySQL Class
 */
class OSQL_MYSQL extends OSQL {

    protected $mHost;
    protected $mPort;
    protected $mSchema;
    protected $mUser;
    protected $mPassword;
    protected $mResults;
    protected $mTransaction;
    protected $mQueryID;

    protected static $mInLimit = 1000;

    /**
     * Initialize a DB connection
     * 
     * @param array $pParams
     * @return void
     */
    public function __construct(array $pParams = array()) {
        $this->mHost        = array_key_exists('DB_HOST', $pParams)        ? $pParams['DB_HOST']      : '';
        $this->mPort        = array_key_exists('DB_PORT', $pParams)        ? $pParams['DB_PORT']      : '';
        $this->mSchema      = array_key_exists('DB_SCHEMA', $pParams)      ? $pParams['DB_SCHEMA']    : '';
        $this->mUser        = array_key_exists('DB_USER', $pParams)        ? $pParams['DB_USER']      : '';
        $this->mPassword    = array_key_exists('DB_PASSWORD', $pParams)    ? $pParams['DB_PASSWORD']  : '';
    }

    /**
     * Get protected properties
     *
     * @param string $pProperty
     * @return mixed
     */
    public function __get(string $pProperty): mixed {
        if (property_exists($this, $pProperty)) {
            return $this->$pProperty;
        } else
            return null;
    }

    /**
     * Connect to DB
     *
     * @return boolean
     */
    protected function Connect(): bool {
        // Create the mysqli object
        $this->mResource = mysqli_init();

        // Cast numbers
        $this->mResource->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);

        // Connect to DB
        $this->mResource->real_connect($this->mHost, $this->mUser, $this->mPassword, $this->mSchema, $this->mPort);
        if ($this->mResource) {
            Log::Debug(__FILE__, __METHOD__, __LINE__, ['Connection success', $this->mHost . ':' . $this->mPort . ', ' . $this->mSchema]);
            return true;
        } else {
            $this->mResource = null;
            Log::Error(__FILE__, __METHOD__, __LINE__, ['Connection fail', $this->mHost . ':' . $this->mPort . ', ' . $this->mSchema], true);
            throw new Exception($this->mExceptionMessage);
            return false;
        }
    }

    /**
     * Check if connected to DB
     *
     * @param boolean $pConnect
     * @return boolean
     */
    public function IsConnected(bool $pConnect = true): bool {
        if ($this->mResource)
            return true;
        else if ($pConnect)
            return $this->Connect();
        else
            return false;
    }

    /**
     * Escape a string
     *
     * @param string $pString
     * @return string
     */
    public function Escape(string $pString): ?string {
        if ($this->IsConnected())
            return mysqli_real_escape_string($this->mResource, stripslashes($pString));
        else
            return null;
    }

    /**
     * Get an error form the DB
     * 
     * @return string
     */
    public function GetError(): ?string {
        if ($this->IsConnected())
            return mysqli_error($this->mResource);
        else
            return null;
    }

    /**
     * Run a Query
     * 
     * @param string $pQuery
     * @param array $pBindVars
     * @return integer
     */
    public function Query(string $pQuery, array $pBindVars = array()): ?int {
        if (!$this->IsConnected())
            return null;

        // Trim Query for execution
        $vQuery = trim($pQuery);

        // Normalise Binds
        $vBindVars = $pBindVars;

        // Clean up data
        $this->Flush();

        // Set Query ID
        $this->mQueryID++;
        Log::Debug(__FILE__, __METHOD__, __LINE__, ['Query [' . $this->mQueryID . ']', $vQuery]);

        // Format bindings
        if (
            !empty($vBindVars)
            && is_array($vBindVars)
        ) {
            $vBindKeys = array();
            $vBindValues = array();

            foreach ($vBindVars as $vKey => $vValue) {
                $vBindKeys[] = ":$vKey";

                if (is_null($vValue))
                    $vBindValues[] = 'NULL';
                elseif (is_array($vValue)) {
                    $vIn = array();
                    foreach ($vValue as $_val) {
                        if (is_null($_val))
                            $vIn[] = 'NULL';
                        elseif (is_int($_val))
                            $vIn[] = $_val;
                        elseif (is_string($_val) && $_val === '')
                            $vIn[] = "''";
                        else
                            $vIn[] = "'" . $this->Escape($_val) . "'";
                    }
                    $vBindValues[] = implode(', ', $vIn);
                } elseif (is_int($vValue))
                    $vBindValues[] = $vValue;
                elseif (is_string($vValue) && $vValue === '')
                    $vBindValues[] = "''";
                else
                    $vBindValues[] = "'" . $this->Escape($vValue) . "'";
            }

            $vBindVars = array_combine($vBindKeys, $vBindValues);
            uksort($vBindVars, 'OSQL::SortBindArray');
            Log::Debug(__FILE__, __METHOD__, __LINE__, ['Query [' . $this->mQueryID . ']', 'Bindings', $vBindVars]);

            // Verify all BindVars exists in the Query
            preg_match_all("/:\w+/", $vQuery, $vMatchedBindings);
            $vMatchedBindings = $vMatchedBindings[0] ?? array();
            if (count($vBindKeys) != count(array_intersect($vBindKeys, $vMatchedBindings))) {
                Log::Error(__FILE__, __METHOD__, __LINE__, ['Query [' . $this->mQueryID . ']', 'Binding mismatch', $vBindKeys, $vMatchedBindings, $vQuery], true);
                throw new Exception($this->mExceptionMessage);
            }

            // Inject bindings
            $vReplaceFrom = array_keys($vBindVars);
            $vReplaceTo = array_values($vBindVars);
            $vQuery = str_replace($vReplaceFrom, $vReplaceTo, $vQuery);
        } else {
            // Verify any missing (leftover) bindings in the Query
            preg_match_all("/:\w+/", $vQuery, $vMatchedBindings);
            $vMatchedBindings = $vMatchedBindings[0] ?? array();
            if (count($vMatchedBindings)) {
                Log::Error(__FILE__, __METHOD__, __LINE__, ['Query [' . $this->mQueryID . ']', 'Missing bindings', $vMatchedBindings, $vQuery], true);
                throw new Exception($this->mExceptionMessage);
            }
        }

        // Start chrono time
        Benchmark::Lap();
        // Run Query
        $this->mResults = mysqli_query($this->mResource, $vQuery);

        // Diff chrono time
        $vLap = Benchmark::DiffLap();

        // If there is an error then abort
        if (mysqli_error($this->mResource)) {
            Log::Error(__FILE__, __METHOD__, __LINE__, ['Query [' . $this->mQueryID . ']', $this->GetError(), $vQuery], true);
            throw new Exception($this->mExceptionMessage);
        } elseif (preg_match("/^(insert|replace)\s+/i", $vQuery)) {
            // Take note of the insert_id
            $this->mInsertID = mysqli_insert_id($this->mResource);
        } elseif (preg_match("/^(delete|update)\s+/i", $vQuery)) {
            // Do nothing
        } else {
            if ($this->mResults instanceof mysqli_result) {
                // Take note of column info
                $vIndex = 0;
                while ($vIndex < mysqli_num_fields($this->mResults)) {
                    if (empty($this->mColumnInfo[$vIndex]))
                        $this->mColumnInfo[$vIndex] = (mysqli_fetch_field_direct($this->mResults, $vIndex))->name;
                    $vIndex++;
                }

                // Store Query Results
                $this->mLastResults = $this->mResults ? mysqli_fetch_all($this->mResults, MYSQLI_ASSOC) : array();

                // Free the memory
                mysqli_free_result($this->mResults);
            } else
                // Blank Query Results
                $this->mLastResults = array();
        }

        // Get the number of rows affected
        $this->mAffectedRows = mysqli_affected_rows($this->mResource);
        $this->mAffectedRows = $this->mAffectedRows > 0 ? $this->mAffectedRows : 0;

        Log::Debug(__FILE__, __METHOD__, __LINE__, ['Query [' . $this->mQueryID . ']', [
            "Cost (ms)"     => number_format(round($vLap * 1000, 2), 2),
            "Columns"       => $this->mColumnInfo,
            "Rows"          => $this->mAffectedRows,
            "Insert ID"     => $this->mInsertID,
            "Results"       => $this->mLastResults
        ]]);

        // Return number fo rows affected
        return $this->mAffectedRows;
    }

    /**
     * Begin Transaction
     *
     * @return void
     */
    public function TransactionBegin() {
        // Run an implicit connection
        if ($this->IsConnected() && !$this->mTransaction) {
            mysqli_begin_transaction($this->mResource);
            $this->mTransaction = true;
        }
    }

    /**
     * Commit Transaction
     *
     * @return void
     */
    public function TransactionCommit() {
        if ($this->mTransaction) {
            mysqli_commit($this->mResource);
            $this->mTransaction = false;
        }
    }

    /**
     * Rollback Transaction
     *
     * @return void
     */
    public function TransactionRollback() {
        if ($this->mTransaction) {
            mysqli_rollback($this->mResource);
            $this->mTransaction = false;
        }
    }

    /**
     * Build the LIKE operator
     *
     * @param string $pSearch
     * @param string $pColumn
     * @param string $pOperator
     * @param array $pStrips
     * @return string
     */
    public static function LikeOperator(string $pSearch, string $pColumn, string $pOperator = 'OR', array $pStrips = []): string {
        // Init
        $vLike = array();
        // Remove multiple spaces
        $vSearch = preg_replace('/\s\s+/', ' ', $pSearch);
        // Split keywords
        $vKeywords = explode(' ', $vSearch);
        foreach ($vKeywords as $vKeyword) {
            // Strip
            if (!empty($pStrips))
                $vKeyword = str_replace($pStrips, [], $vKeyword);

            // Trim
            $vKeyword = trim($vKeyword);

            // Build LIKE if keyword is still not blank
            if (strlen($vKeyword))
                $vLike[] = $pColumn . " LIKE '" . OSQL::__Escape($vKeyword) . "%'";
        }
        // Join LIKE with the operator
        return empty($vLike) ? '0 = 1' : '(' . implode(' ' . $pOperator . ' ', $vLike) . ')';
    }

    /**
     * Build the IN operator
     *
     * @param mixed $pStuff
     * @param array $pStrips
     * @return string
     */
    public static function InOperator(mixed $pStuff, array $pStrips = []): string {
        // Init
        $vItems = array();

        // Account for either a string or array input type
        if (is_array($pStuff))
            // Assume an array
            $vItems = $pStuff;
        else {
            // Assume is a string
            // Remove multiple spaces
            $pStuff = preg_replace('/\s\s+/', ' ', $pStuff);
            // Split keywords
            $vItems = explode(' ', $pStuff);
        }

        // Concatenate array items
        foreach ($vItems as $vIndex => &$pItem) {
            if ($vIndex < self::$mInLimit) {
                // Strip
                if (!empty($pStrips))
                    $pItem = str_replace($pStrips, [], $pItem);

                // Build IN 
                $pItem = "'" . OSQL::__Escape($pItem) . "'";
            } else {
                // Unset item
                $pItem = null;
                unset($vItems[$vIndex]);
            }
        }

        // Join IN 
        return '(' . implode(', ', $vItems) . ')';
    }

    /**
     * Convert a null to a blank to facilitate DB binding
     *
     * @param mixed $pParam
     * @return mixed
     */
    public static function Null2Blank(mixed $pParam): mixed {
        return $pParam ?? '';
    }
}
