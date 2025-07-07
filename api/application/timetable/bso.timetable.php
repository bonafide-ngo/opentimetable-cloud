<?

namespace App;

class BSO_Timetable {

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Period_Week(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Timetable($pParams);

        // Check cache
        $vPeriods = \Cache::Get([__CLASS__, __METHOD__, $vDTO]);
        if ($vPeriods)
            return new \ApiResponse($vPeriods);

        // Get periods
        $vBindVars = array('snc_id' => $vDTO->syncId);
        $vPeriods = \OSQL::_GetResults(PATH_SQL_TIMETABLE, 'select_period_week', $vBindVars);

        // Set cache
        \Cache::Set([__CLASS__, __METHOD__, $vDTO], $vPeriods);

        return new \ApiResponse($vPeriods);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Period_Semester(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Timetable($pParams);

        // Check cache
        $vPeriods = \Cache::Get([__CLASS__, __METHOD__, $vDTO]);
        if ($vPeriods)
            return new \ApiResponse($vPeriods);

        $vBindVars = array('snc_id' => $vDTO->syncId);
        $vPeriods = \OSQL::_GetResults(PATH_SQL_TIMETABLE, 'select_period_semester', $vBindVars);

        // Set cache
        \Cache::Set([__CLASS__, __METHOD__, $vDTO], $vPeriods);

        return new \ApiResponse($vPeriods);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Location(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Timetable_Location($pParams);

        $vBindVars = array(
            'snc_id' => $vDTO->syncId,
            'lct_code' => $vDTO->venueCode
        );
        return new \ApiResponse(\OSQL::_GetRow(PATH_SQL_TIMETABLE, 'select_location', $vBindVars));
    }
}
