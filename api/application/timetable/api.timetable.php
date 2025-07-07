<?

namespace App;

class Timetable {

    /**
     * Read weeks
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Period_Week(mixed $pParams = null): mixed {
        return BSO_Timetable::Read_Period_Week($pParams);
    }

    /**
     * Read semesters
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Period_Semester(mixed $pParams = null): mixed {
        return BSO_Timetable::Read_Period_Semester($pParams);
    }

    /**
     * Read location
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Location(mixed $pParams = null): mixed {
        return BSO_Timetable::Read_Location($pParams);
    }
}
