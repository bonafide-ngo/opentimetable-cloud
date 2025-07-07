<?

namespace App;

class Department {

    /**
     * Read departments
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Department(mixed $pParams = null): mixed {
        return BSO_Department::Read_Department($pParams);
    }

    /**
     * Read courses
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Course(mixed $pParams = null): mixed {
        return BSO_Department::Read_Course($pParams);
    }

    /**
     * Read modules
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Module(mixed $pParams = null): mixed {
        return BSO_Department::Read_Module($pParams);
    }

    /**
     * Read timetables
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Timetable(mixed $pParams = null): mixed {
        return BSO_Department::Read_Timetable($pParams);
    }
}
