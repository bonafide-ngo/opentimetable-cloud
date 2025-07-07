<?

namespace App;

class Lecture {

    /**
     * Read courses
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Course(mixed $pParams = null): mixed {
        return BSO_Lecture::Read_Course($pParams);
    }

    /**
     * Read modules
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Module(mixed $pParams = null): mixed {
        return BSO_Lecture::Read_Module($pParams);
    }

    /**
     * Read timetables
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Timetable(mixed $pParams = null): mixed {
        return BSO_Lecture::Read_Timetable($pParams);
    }
}
