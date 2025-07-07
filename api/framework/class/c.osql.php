<?

/**
 * OSQL Class
 */
class OSQL {
    protected $mResource        = null;
    protected $mAffectedRows    = 0;
    protected $mInsertID        = 0;
    protected $mLastResults     = array();
    protected $mColumnInfo      = null;
    protected $mExceptionMessage = 'OSQL exception';

    /**
     * Flush data
     */
    protected function Flush() {
        $this->mLastResults = array();
        $this->mColumnInfo = null;
        $this->mInsertID = 0;
    }

    /**
     * Get the insert ID
     *
     * @return integer
     */
    public static function __GetInsertID(string $pDB = DB_LINK_DEFAULT) {
        global $gDBs;

        return $gDBs[$pDB]->GetInsertID();
    }
    public function GetInsertID(): int {
        return $this->mInsertID;
    }

    /**
     * Get the number of affected rows
     *
     * @return integer
     */
    public function GetAffectedRows(): int {
        return $this->mAffectedRows;
    }

    /**
     * Get the database resource
     *
     * @return mysqli
     */
    public function GetResource(): mysqli {
        return $this->mResource;
    }

    /**
     * Get the last SQL Error
     * Virtual implementation
     *
     * @return void
     */
    public function GetError() {
    }

    /**
     * Run a SQL query
     * Virtual implementation
     *
     * @param string $pQuery
     * @param array $pBindVars
     * @return integer
     */
    public static function __Query(string $pQuery, array $pBindVars = array(), string $pDB = DB_LINK_DEFAULT): ?int {
        global $gDBs;

        return $gDBs[$pDB]->Query($pQuery, $pBindVars);
    }
    /**
     * Undocumented function
     *
     * @param string $pPath
     * @param string $pFilename
     * @param array $pBindVars
     * @param [type] $pDB
     * @param boolean $pTiming
     * @return integer|null
     */
    public static function _Query(string $pPath, string $pFilename, array $pBindVars = array(), string $pDB = DB_LINK_DEFAULT, bool $pTiming = false): ?int {
        global $gDBs;

        if ($pTiming)
            $vTime = time();

        $vQuery = file_get_contents($pPath . strtolower($pFilename) . '.sql');
        $vAffectedRows = $gDBs[$pDB]->Query($vQuery, $pBindVars);

        if ($pTiming)
            \Log::Debug(__FILE__, __METHOD__, __LINE__, $pFilename . ': ' . (time() - $vTime) . 's', true);

        return $vAffectedRows;
    }
    protected function Query(string $pQuery, array $pBindVars = array()): ?int {
        return 0 | 1;
    }

    /**
     * Get a Value
     *
     * @param string $pQuery
     * @param array $pBindVars
     * @param integer $pX
     * @param integer $pY
     * @param string $pDB
     * @return mixed
     */
    public static function __GetValue(string $pQuery = null, array $pBindVars = array(), int $pX = 0, int $pY = 0, string $pDB = DB_LINK_DEFAULT): mixed {
        global $gDBs;

        return $gDBs[$pDB]->GetValue($pQuery, $pBindVars, $pX, $pY);
    }
    /**
     * @param string $pPath
     * @param string $pFilename
     * @param array $pBindVars
     * @param integer $pX
     * @param integer $pY
     * @param [type] $pDB
     * @return mixed
     */
    public static function _GetValue(string $pPath, string $pFilename, array $pBindVars = array(), int $pX = 0, int $pY = 0, string $pDB = DB_LINK_DEFAULT): mixed {
        global $gDBs;

        $vQuery = file_get_contents($pPath . strtolower($pFilename) . '.sql');
        return $gDBs[$pDB]->GetValue($vQuery, $pBindVars, $pX, $pY);
    }
    public function GetValue(string $pQuery = null, array $pBindVars = array(), int $pX = 0, int $pY = 0): mixed {
        // If there is a query then perform it else use cached results
        if ($pQuery)
            $this->Query($pQuery, $pBindVars);

        // Extract the value out of the (last) cached results
        $vLastResults = isset($this->mLastResults[$pY]) ? $this->mLastResults[$pY] : array();
        $vValues = array_values(get_object_vars((object)$vLastResults));

        // If there is a value return it else return null
        return isset($vValues[$pX]) ? $vValues[$pX] : null;
    }

    /**
     * Get a row
     *
     * @param string $pQuery
     * @param array $pBindVars
     * @param string $pOutputType
     * @param integer $pY
     * @param string $pDB
     * @return mixed
     */
    public static function __GetRow(string $pQuery = null, array $pBindVars = array(), string $pOutputType = MYSQLI_ASSOC, int $pY = 0, string $pDB = DB_LINK_DEFAULT): mixed {
        global $gDBs;

        return $gDBs[$pDB]->GetRow($pQuery, $pBindVars, $pOutputType, $pY);
    }
    /**
     * @param string $pPath
     * @param string $pFilename
     * @param array $pBindVars
     * @param [type] $pOutputType
     * @param integer $pY
     * @param [type] $pDB
     * @return mixed
     */
    public static function _GetRow(string $pPath, string $pFilename, array $pBindVars = array(), string $pOutputType = MYSQLI_ASSOC, int $pY = 0, string $pDB = DB_LINK_DEFAULT): mixed {
        global $gDBs;

        $vQuery = file_get_contents($pPath . strtolower($pFilename) . '.sql');
        return $gDBs[$pDB]->GetRow($vQuery, $pBindVars, $pOutputType, $pY);
    }
    public function GetRow(string $pQuery = null, array $pBindVars = array(), string $pOutputType = MYSQLI_ASSOC, int $pY = 0): mixed {
        // If there is a query then perform it else use cached results
        if ($pQuery)
            $this->Query($pQuery, $pBindVars);

        // Extract the row out of the (last) cached results
        $vLastResults = isset($this->mLastResults[$pY]) ? $this->mLastResults[$pY] : array();
        switch ($pOutputType) {
            case MYSQLI_ASSOC:
                return $vLastResults;
                break;
            case MYSQLI_NUM:
                return array_values(get_object_vars((object)$vLastResults));
                break;
            default:
                Log::Debug(__FILE__, __METHOD__, __LINE__, "GetRow: Invalid output type $pOutputType");
                return null;
                break;
        }
    }

    /**
     * Get a Column
     *
     * @param string $pQuery
     * @param array $pBindVars
     * @param integer $pX
     * @param string $pDB
     * @return array
     */
    public static function __GetColumn(string $pQuery = null, array $pBindVars = array(), int $pX = 0, string $pDB = DB_LINK_DEFAULT): mixed {
        global $gDBs;

        return $gDBs[$pDB]->GetColumn($pQuery, $pBindVars, $pX);
    }
    /**
     * @param string $pPath
     * @param string $pFilename
     * @param array $pBindVars
     * @param integer $pX
     * @param [type] $pDB
     * @return mixed
     */
    public static function _GetColumn(string $pPath, string $pFilename, array $pBindVars = array(), int $pX = 0, string $pDB = DB_LINK_DEFAULT): mixed {
        global $gDBs;

        $vQuery = file_get_contents($pPath . strtolower($pFilename) . '.sql');
        return $gDBs[$pDB]->GetColumn($vQuery, $pBindVars, $pX);
    }
    public function GetColumn(string $pQuery = null, array $pBindVars = array(), int $pX = 0): array {
        // If there is a query then perform it else use cached results
        if ($pQuery)
            $this->Query($pQuery, $pBindVars);

        // Extract the column values
        $vValues = array();
        for ($vI = 0; $vI < count($this->mLastResults); $vI++) {
            $vValues[$vI] = $this->GetValue(null, array(), $pX, $vI);
        }

        return $vValues;
    }

    /**
     * Get Results
     *
     * @param string $pQuery
     * @param array $pBindVars
     * @param string $pOutputType
     * @param string $pDB
     * @return mixed
     */
    public static function __GetResults(string $pQuery = null, array $pBindVars = array(), string $pOutputType = MYSQLI_ASSOC, string $pDB = DB_LINK_DEFAULT): mixed {
        global $gDBs;

        return $gDBs[$pDB]->GetResults($pQuery, $pBindVars, $pOutputType);
    }
    /**
     * @param string $pPath
     * @param string $pFilename
     * @param array $pBindVars
     * @param [type] $pOutputType
     * @param [type] $pDB
     * @return mixed
     */
    public static function _GetResults(string $pPath, string $pFilename, array $pBindVars = array(), string $pOutputType = MYSQLI_ASSOC, string $pDB = DB_LINK_DEFAULT): mixed {
        global $gDBs;

        $vQuery = file_get_contents($pPath . strtolower($pFilename) . '.sql');
        return $gDBs[$pDB]->GetResults($vQuery, $pBindVars, $pOutputType);
    }
    public function GetResults(string $pQuery = null, array $pBindVars = array(), string $pOutputType = MYSQLI_ASSOC): mixed {
        // If there is a query then perform it else use cached results
        if ($pQuery)
            $this->Query($pQuery, $pBindVars);

        switch ($pOutputType) {
            case MYSQLI_ASSOC:
                return $this->mLastResults;
                break;
            case MYSQLI_NUM:
                if (!empty($this->mLastResults)) {
                    $vValues = array();
                    foreach ($this->mLastResults as $vRow)
                        $vValues[] = array_values(get_object_vars((object)$vRow));

                    return $vValues;
                } else
                    return null;
                break;
            default:
                Log::Debug(__FILE__, __METHOD__, __LINE__, "GetResults: Invalid output type $pOutputType");
                return null;
                break;
        }
    }

    /**
     * Get Column info
     *
     * @param string $pType
     * @param integer $pOffset
     * @param string $pDB
     * @return mixed
     */
    public static function __GetColumnInfo(string $pType = 'name', int $pOffset = -1, string $pDB = DB_LINK_DEFAULT): mixed {
        global $gDBs;

        return $gDBs[$pDB]->GetColumnInfo($pType, $pOffset);
    }
    public function GetColumnInfo(string $pType = 'name', int $pOffset = -1): mixed {
        if ($this->mColumnInfo) {
            if ($pOffset == -1) {
                $vIndex = 0;
                $vValues = array();
                foreach ($this->mColumnInfo as $vColumn) {
                    $vValues[$vIndex] = $vColumn->{$pType};
                    $vIndex++;
                }

                return $vValues;
            } else
                return $this->mColumnInfo[$pOffset]->{$pType};
        } else
            return null;
    }

    /**
     * Escape an input
     *
     * @param string $pString
     * @param string $pDB
     * @return void
     */
    public static function __Escape(string $pString, string $pDB = DB_LINK_DEFAULT) {
        global $gDBs;

        return $gDBs[$pDB]->Escape($pString);
    }

    /**
     * Begin Transaction
     *
     * @param string $pDB
     * @return void
     */
    public static function __TransactionBegin(string $pDB = DB_LINK_DEFAULT) {
        global $gDBs;

        return $gDBs[$pDB]->TransactionBegin();
    }

    /**
     * Commit Transaction
     *
     * @param string $pDB
     * @return void
     */
    public static function __TransactionCommit(string $pDB = DB_LINK_DEFAULT) {
        global $gDBs;

        return $gDBs[$pDB]->TransactionCommit();
    }

    /**
     * Rollback Transaction
     *
     * @param string $pDB
     * @return void
     */
    public static function __TransactionRollback(string $pDB = DB_LINK_DEFAULT) {
        global $gDBs;

        return $gDBs[$pDB]->TransactionRollback();
    }

    /**
     * Function to sort bind array by length of keys
     *
     * @param string $pA
     * @param string $pB
     * @return integer
     */
    public static function SortBindArray(string $pA, string $pB): int {
        $vLengthA = strlen($pA);
        $vLengthB = strlen($pB);
        if ($vLengthA == $vLengthB)
            return 0;

        return ($vLengthA > $vLengthB) ? -1 : 1;
    }

    /**
     * Build the LIKE operator
     *
     * @param string $pSearch
     * @param string $pColumn
     * @param string $pOperator
     * @param string $pStrips
     * @param string $pDB
     * @return string
     */
    public static function __LikeOperator(string $pSearch, string $pColumn, string $pOperator = 'OR', array $pStrips = [], string $pDB = DB_LINK_DEFAULT): string {
        global $gDBs;

        return $gDBs[$pDB]->LikeOperator($pSearch,  $pColumn,  $pOperator, $pStrips);
    }

    /**
     * Build the IN operator
     *
     * @param mixed $pStuff
     * @param string $pStrips
     * @param string $pDB
     * @return string
     */
    public static function __InOperator(mixed $pStuff, array $pStrips = [], string $pDB = DB_LINK_DEFAULT): string {
        global $gDBs;

        return $gDBs[$pDB]->InOperator($pStuff, $pStrips);
    }

    /**
     * Convert a null to a blank to facilitate DB binding
     *
     * @param mixed $pParam
     * @param string $pDB
     * @return mixed
     */
    public static function __Null2Blank(mixed $pParam, string $pDB = DB_LINK_DEFAULT): mixed {
        global $gDBs;

        return $gDBs[$pDB]->Null2Blank($pParam);
    }
}
