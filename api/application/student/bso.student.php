<?

namespace App;

class BSO_Student {

    /**
     * Undocumented function
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Timetable(mixed $pParams = null): mixed {
        // Get params
        $vDTO = new DTO_Student($pParams);

        // Check payload
        $vPayload = $vDTO->payloadBase64 ? json_decode(base64_decode($vDTO->payloadBase64)) : null;
        if ($vPayload) {
            // Check payload structure
            if (!$vPayload->studentId || !$vPayload->timestamp || !$vPayload->seed || !$vPayload->signature)
                throw new \UnexpectedException();
            // Check signature 
            if (\Crypto::Sha512($vPayload->studentId . $vPayload->timestamp . $vPayload->seed . SALSA) != $vPayload->signature)
                throw new \UnexpectedException();
            // Check timestamp
            if ($vPayload->timestamp + \Util::GetConfig('validity.payload') < NOW)
                return new \ApiResponse(null, \Lang::Get('static.error-secure-link'));

            // Set student
            $vStudentId = $vPayload->studentId;
        } else {
            // Check privile
            Common::CheckUserInGroups(array(APP_MSAL_GROUP_STUDENT));

            // Get student
            $vStudentId = Common::GetStudentId();
        }

        // Parse period into week or semester
        $vPeriod = Common::ParsePeriod($vDTO->period);

        // Check cache
        $vTimetables = \Cache::Get([__CLASS__, __METHOD__, [$vStudentId, $vDTO->period, $vDTO->syncId]]);
        if ($vTimetables)
            return new \ApiResponse(Common::ParseTimetables($vPeriod, $vTimetables));

        // Check sync id
        $vBindVars = array('snc_id' => $vDTO->syncId);
        if (!\OSQL::_GetRow(PATH_SQL_APPLICATION, 'select_sync_timetable', $vBindVars))
            throw new \DataException();

        // Get timetables
        $vBindVars = array(
            'snc_id' => $vDTO->syncId,
            'in_student' => $vStudentId,
            'in_semester' => \OSQL::__Null2Blank($vPeriod->semester),
            'in_week' => \OSQL::__Null2Blank($vPeriod->week)
        );
        $vTimetables = \OSQL::_GetResults(PATH_SQL_STUDENT, 'select_student_timetable', $vBindVars);

        // Set cache
        \Cache::Set([__CLASS__, __METHOD__, [$vStudentId, $vDTO->period, $vDTO->syncId]], $vTimetables);

        return new \ApiResponse(Common::ParseTimetables($vPeriod, $vTimetables));
    }
}
