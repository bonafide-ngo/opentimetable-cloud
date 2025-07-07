<?

namespace App;

class Student {


    /**
     * Read timetables
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Timetable(mixed $pParams = null): mixed {
        return BSO_Student::Read_Timetable($pParams);
    }
}
