<?

namespace App;

class DTO_Update_DB extends \DTO {
    public string $version = '';
    public string $signedSalsa_base64 = '';

    public function Validate() {
        if (!\Validate::Exist($this->version))
            throw new \ValidateException;

        if (
            !\Validate::Exist($this->signedSalsa_base64)
            || $this->signedSalsa_base64 != base64_encode(base64_decode($this->signedSalsa_base64))
        )
            throw new \ValidateException;
    }
}
