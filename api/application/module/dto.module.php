<?

namespace App;

class DTO_Module extends \DTO {
    public ?string $period = null;
    public ?array $modules = null;
    public ?int $syncId = 0;

    public function Validate() {
        \Validate::EnforceNull($this->period);
        \Validate::EnforceNull($this->modules);
    }
}
