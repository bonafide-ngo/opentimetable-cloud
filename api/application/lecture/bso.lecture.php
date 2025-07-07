<?

namespace App;

class BSO_Lecture {

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Course(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Lecture($pParams);

        // Check cache
        $vCourses = \Cache::Get([__CLASS__, __METHOD__, $vDTO]);
        if ($vCourses)
            return new \ApiResponse($vCourses);

        // Check sync id
        $vBindVars = array('snc_id' => $vDTO->syncId);
        if (!\OSQL::_GetRow(PATH_SQL_APPLICATION, 'select_sync_timetable', $vBindVars))
            throw new \DataException();

        // Get courses
        $vBindVars = array('snc_id' => $vDTO->syncId);
        $vCourses = \OSQL::_GetResults(PATH_SQL_LECTURE, 'select_lecture_course', $vBindVars);

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
        $vDTO = new DTO_Lecture($pParams);

        // Parse period into week or semester
        $vPeriod = Common::ParsePeriod($vDTO->period);

        // Check cache
        $vModules = \Cache::Get([__CLASS__, __METHOD__, $vDTO]);
        if ($vModules)
            return new \ApiResponse($vModules);

        // Check sync id
        $vBindVars = array('snc_id' => $vDTO->syncId);
        if (!\OSQL::_GetRow(PATH_SQL_APPLICATION, 'select_sync_timetable', $vBindVars))
            throw new \DataException();

        // Get modules
        $vBindVars = array(
            'snc_id' => $vDTO->syncId,
            'in_list_courses' => $vDTO->courses ? 1 : 0,
            'in_courses' => \OSQL::__Null2Blank($vDTO->courses),
            'in_semester' => \OSQL::__Null2Blank($vPeriod->semester),
            'in_week' => \OSQL::__Null2Blank($vPeriod->week),
        );
        $vModules = \OSQL::_GetResults(PATH_SQL_LECTURE, 'select_lecture_module', $vBindVars);

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
        $vDTO = new DTO_Lecture($pParams);

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
            'in_list_courses' => $vDTO->courses ? 1 : 0,
            'in_courses' => \OSQL::__Null2Blank($vDTO->courses),
            'in_semester' => \OSQL::__Null2Blank($vPeriod->semester),
            'in_week' => \OSQL::__Null2Blank($vPeriod->week),
            'in_list_modules' => $vDTO->modules ? 1 : 0,
            'in_modules' => \OSQL::__Null2Blank($vDTO->modules)
        );
        $vTimetables = \OSQL::_GetResults(PATH_SQL_LECTURE, 'select_lecture_timetable', $vBindVars);

        // Set cache
        \Cache::Set([__CLASS__, __METHOD__, $vDTO], $vTimetables);

        return new \ApiResponse(Common::ParseTimetables($vPeriod, $vTimetables));
    }
}
