<?

namespace App;

class Venue {

    /**
     * Read venues
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Venue(mixed $pParams = null): mixed {
        return BSO_Venue::Read_Venue($pParams);
    }

    /**
     * Read timetables
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Timetable(mixed $pParams = null): mixed {
        return BSO_Venue::Read_Timetable($pParams);
    }
}
