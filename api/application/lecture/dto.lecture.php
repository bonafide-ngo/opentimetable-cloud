<?

namespace App;

class DTO_Lecture extends \DTO {
    public ?array $courses = null;
    public ?string $period = null;
    public ?array $modules = null;
    public int $syncId = 0;

    public function Validate() {
        \Validate::EnforceNull($this->courses);
        \Validate::EnforceNull($this->period);
        \Validate::EnforceNull($this->modules);

        if (!\Validate::Exist($this->syncId))
            throw new \ValidateException;
    }
}
