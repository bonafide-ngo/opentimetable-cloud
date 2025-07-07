<?

namespace App;

class BSO_Department {

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Department(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Department($pParams);

        // Check cache
        $vDepartments = \Cache::Get([__CLASS__, __METHOD__, $vDTO]);
        if ($vDepartments)
            return new \ApiResponse($vDepartments);

        $vBindVars = array('snc_id' => $vDTO->syncId);
        $vDepartments = \OSQL::_GetResults(PATH_SQL_DEPARTMENT, 'select_department', $vBindVars);

        // Set cache
        \Cache::Set([__CLASS__, __METHOD__, $vDTO], $vDepartments);

        return new \ApiResponse($vDepartments);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Course(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Department($pParams);

        // Check cache
        $vCourses = \Cache::Get([__CLASS__, __METHOD__, $vDTO]);
        if ($vCourses)
            return new \ApiResponse($vCourses);

        $vBindVars = array(
            'snc_id' => $vDTO->syncId,
            'in_list_departments' => $vDTO->departments ? 1 : 0,
            'in_departments' => \OSQL::__Null2Blank($vDTO->departments)
        );
        $vCourses = \OSQL::_GetResults(PATH_SQL_DEPARTMENT, 'select_department_course', $vBindVars);

        // Set cache
        \Cache::Set([__CLASS__, __METHOD__, $vDTO], $vCourses);

        return new \ApiResponse($vCourses);
    }

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Module(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Department($pParams);

        // Parse period into week or semester
        $vPeriod = Common::ParsePeriod($vDTO->period);

        // Check cache
        $vModules = \Cache::Get([__CLASS__, __METHOD__, $vDTO]);
        if ($vModules)
            return new \ApiResponse($vModules);

        // Get modules
        $vBindVars = array(
            'snc_id' => $vDTO->syncId,
            'in_list_departments' => $vDTO->departments ? 1 : 0,
            'in_departments' => \OSQL::__Null2Blank($vDTO->departments),
            'in_list_courses' => $vDTO->courses ? 1 : 0,
            'in_courses' => \OSQL::__Null2Blank($vDTO->courses),
            'in_semester' => \OSQL::__Null2Blank($vPeriod->semester),
            'in_week' => \OSQL::__Null2Blank($vPeriod->week),
        );
        $vModules = \OSQL::_GetResults(PATH_SQL_DEPARTMENT, 'select_department_module', $vBindVars);

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
        $vDTO = new DTO_Department($pParams);

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
            'in_list_departments' => $vDTO->departments ? 1 : 0,
            'in_departments' => \OSQL::__Null2Blank($vDTO->departments),
            'in_list_courses' => $vDTO->courses ? 1 : 0,
            'in_courses' => \OSQL::__Null2Blank($vDTO->courses),
            'in_semester' => \OSQL::__Null2Blank($vPeriod->semester),
            'in_week' => \OSQL::__Null2Blank($vPeriod->week),
            'in_list_modules' => $vDTO->modules ? 1 : 0,
            'in_modules' => \OSQL::__Null2Blank($vDTO->modules),
        );
        $vTimetables = \OSQL::_GetResults(PATH_SQL_DEPARTMENT, 'select_department_timetable', $vBindVars);

        // Set cache
        \Cache::Set([__CLASS__, __METHOD__, $vDTO], $vTimetables);

        return new \ApiResponse(Common::ParseTimetables($vPeriod, $vTimetables));
    }
}
