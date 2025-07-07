<?

namespace App;

class BSO_Venue {

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Venue(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Venue($pParams);

        // Check cache
        $vVenues = \Cache::Get([__CLASS__, __METHOD__, $vDTO]);
        if ($vVenues)
            return new \ApiResponse($vVenues);

        // Check sync id
        $vBindVars = array('snc_id' => $vDTO->syncId);
        if (!\OSQL::_GetRow(PATH_SQL_APPLICATION, 'select_sync_timetable', $vBindVars))
            throw new \DataException();

        // Get venues
        $vBindVars = array('snc_id' => $vDTO->syncId);
        $vVenues = \OSQL::_GetResults(PATH_SQL_VENUE, 'select_venue', $vBindVars);

        // Set cache
        \Cache::Set([__CLASS__, __METHOD__, $vDTO], $vVenues);

        return new \ApiResponse($vVenues);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Timetable(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Venue($pParams);

        // Parse period into week or semester
        $vPeriod = Common::ParsePeriod($vDTO->period);

        // Check cache
        $vTimetables = \Cache::Get([__CLASS__, __METHOD__, $vDTO]);
        if ($vTimetables)
            return new \ApiResponse(Common::ParseTimetables($vPeriod, $vTimetables));

        // Check sync id
        $vBindVars = array('snc_id' => $vDTO->syncId);
        if (!\OSQL::_GetRow(PATH_SQL_APPLICATION, 'select_sync_timetable', $vBindVars))
            throw new \DataException();

        // Get timetables
        $vBindVars = array(
            'snc_id' => $vDTO->syncId,
            'in_list_venues' => $vDTO->venues ? 1 : 0,
            'in_venues' => \OSQL::__Null2Blank($vDTO->venues),
            'in_semester' => \OSQL::__Null2Blank($vPeriod->semester),
            'in_week' => \OSQL::__Null2Blank($vPeriod->week)
        );
        $vTimetables = \OSQL::_GetResults(PATH_SQL_VENUE, 'select_venue_timetable', $vBindVars);

        // Set cache
        \Cache::Set([__CLASS__, __METHOD__, $vDTO], $vTimetables);

        return new \ApiResponse(Common::ParseTimetables($vPeriod, $vTimetables));
    }
}
