<?

namespace App;

class DTO_Department extends \DTO {
    public ?array $departments = null;
    public ?array $courses = null;
    public ?string $period = null;
    public ?array $modules = null;
    public int $syncId = 0;

    public function Validate() {
        \Validate::EnforceNull($this->departments);
        \Validate::EnforceNull($this->courses);
        \Validate::EnforceNull($this->period);
        \Validate::EnforceNull($this->modules);

        if (!\Validate::Exist($this->syncId))
            throw new \ValidateException;
    }
}
