<?

namespace App;

class BSO_Module {

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Module(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Module($pParams);

        // Check cache
        $vModules = \Cache::Get([__CLASS__, __METHOD__, $vDTO]);
        if ($vModules)
            return new \ApiResponse($vModules);

        // Check sync id
        $vBindVars = array('snc_id' => $vDTO->syncId);
        if (!\OSQL::_GetRow(PATH_SQL_APPLICATION, 'select_sync_timetable', $vBindVars))
            throw new \DataException();

        // Get modules
        $vBindVars = array('snc_id' => $vDTO->syncId);
        $vModules = \OSQL::_GetResults(PATH_SQL_MODULE, 'select_module_module', $vBindVars);

        // Set cache
        \Cache::Set([__CLASS__, __METHOD__, $vDTO], $vModules);

        return new \ApiResponse($vModules);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Timetable(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Module($pParams);

        // Parse period into week or semester
        $vPeriod = Common::ParsePeriod($vDTO->period);

        // SyncId may be blank when sharing from external apps
        $vDTO->syncId = $vDTO->syncId ? $vDTO->syncId : \OSQL::_GetValue(PATH_SQL_ADMIN, 'select_sync_active');
        if (!$vDTO->syncId)
            throw new \DataException();

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
            'in_semester' => \OSQL::__Null2Blank($vPeriod->semester),
            'in_week' => \OSQL::__Null2Blank($vPeriod->week),
            'in_list_modules' => $vDTO->modules ? 1 : 0,
            'in_modules' => \OSQL::__Null2Blank($vDTO->modules)
        );
        $vTimetables = \OSQL::_GetResults(PATH_SQL_MODULE, 'select_module_timetable', $vBindVars);

        // Set cache
        \Cache::Set([__CLASS__, __METHOD__, $vDTO], $vTimetables);

        return new \ApiResponse(Common::ParseTimetables($vPeriod, $vTimetables));
    }
}
