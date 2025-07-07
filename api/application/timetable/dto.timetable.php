<?

namespace App;

class DTO_Timetable extends \DTO {
    public int $syncId = 0;

    public function Validate() {
        if (!\Validate::Exist($this->syncId))
            throw new \ValidateException;
    }
}

class DTO_Timetable_Location extends \DTO {
    public int $syncId = 0;
    public string $venueCode = '';

    public function Validate() {
        if (!\Validate::Exist($this->syncId))
            throw new \ValidateException;
        if (!\Validate::Exist($this->venueCode))
            throw new \ValidateException;
    }
}
