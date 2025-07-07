<?

namespace App;

class Module {

    /**
     * Read modules
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Module(mixed $pParams = null): mixed {
        return BSO_Module::Read_Module($pParams);
    }

    /**
     * Read timetables
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Read_Timetable(mixed $pParams = null): mixed {
        return BSO_Module::Read_Timetable($pParams);
    }
}
