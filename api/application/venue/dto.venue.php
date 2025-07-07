<?

namespace App;

class DTO_Venue extends \DTO {
    public ?array $venues = null;
    public ?string $period = null;
    public int $syncId = 0;

    public function Validate() {
        \Validate::EnforceNull($this->venues);
        \Validate::EnforceNull($this->period);

        if (!\Validate::Exist($this->syncId))
            throw new \ValidateException;
    }
}
