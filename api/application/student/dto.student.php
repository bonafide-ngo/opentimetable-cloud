<?

namespace App;

class DTO_Student extends \DTO {
    public ?string $period = null;
    public int $syncId = 0;
    public ?string $payloadBase64 = null;

    public function Validate() {
        \Validate::EnforceNull($this->period);
        \Validate::EnforceNull($this->payloadBase64);

        if (!\Validate::Exist($this->syncId))
            throw new \ValidateException;

        if (
            \Validate::Exist($this->payloadBase64)
            && $this->payloadBase64 != base64_encode(base64_decode($this->payloadBase64))
        )
            throw new \ValidateException;
    }
}
