<?

namespace App;

class Upgrade {

    /**
     * Run notification queue
     *
     * @param mixed $pParams
     * @return mixed
     */
    public static function Update_DB(mixed $pParams = null): mixed {
        // Load dependency
        \JsonRpc::LoadReferences('App', 'Admin');
        \JsonRpc::LoadReferences('App', 'Department');
        \JsonRpc::LoadReferences('App', 'Lecture');
        \JsonRpc::LoadReferences('App', 'Module');
        \JsonRpc::LoadReferences('App', 'Student');
        \JsonRpc::LoadReferences('App', 'Timetable');
        \JsonRpc::LoadReferences('App', 'Venue');

        return BSO_Upgrade::Update_DB($pParams);
    }
}
