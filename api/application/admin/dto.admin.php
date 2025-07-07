<?

namespace App;

/* 
 * Admin - Settings
 * ************************************************************************
 */

class DTO_Admin_Read_Setting extends \DTO {
    public string $code = '';

    public function Validate() {
        if (!\Validate::SettingCode($this->code))
            throw new \ValidateException;
    }
}
class DTO_Admin_Update_SettingFlag extends \DTO {
    public string $code = '';
    public bool $flag = false;

    public function Validate() {
        if (!\Validate::SettingCode($this->code))
            throw new \ValidateException;
    }
}
class DTO_Admin_Update_SettingText extends \DTO {
    public string $code = '';
    public ?string $text = null;

    public function Validate() {
        if (!\Validate::SettingCode($this->code))
            throw new \ValidateException;

        \Validate::EnforceNull($this->text);
    }
}

/* 
 * Admin - Sync
 * ************************************************************************
 */

class DTO_Admin_Create_Sync extends \DTO {
    public ?string $signedSalsa_base64 = null;

    public function Validate() {
        if (
            \Validate::Exist($this->signedSalsa_base64)
            && $this->signedSalsa_base64 != base64_encode(base64_decode($this->signedSalsa_base64))
        )
            throw new \ValidateException;
    }
}

class DTO_Admin_Create_Batch extends \DTO {
    public ?string $signedUser_base64 = null;

    public function Validate() {
        if (
            !\Validate::Exist($this->signedUser_base64)
            || $this->signedUser_base64 != base64_encode(base64_decode($this->signedUser_base64))
        )
            throw new \ValidateException;
    }
}

class DTO_Admin_Delete_Batch extends \DTO {
    public ?string $signedSalsa_base64 = null;

    public function Validate() {
        if (
            !\Validate::Exist($this->signedSalsa_base64)
            || $this->signedSalsa_base64 != base64_encode(base64_decode($this->signedSalsa_base64))
        )
            throw new \ValidateException;
    }
}

class DTO_Admin_Update_Sync_Publish extends \DTO {
    public int $syncId = 0;

    public function Validate() {
        if (!\Validate::Exist($this->syncId))
            throw new \ValidateException;
    }
}

class DTO_Admin_Create_Sync_Rollback extends \DTO {
    public int $syncId = 0;

    public function Validate() {
        if (!\Validate::Exist($this->syncId))
            throw new \ValidateException;
    }
}

class DTO_Admin_Update_Sync_Draft extends \DTO {
    public int $syncId = 0;

    public function Validate() {
        if (!\Validate::Exist($this->syncId))
            throw new \ValidateException;
    }
}

/* 
 * Admin - Venue
 * ************************************************************************
 */

class DTO_Admin_Read_Location_All extends \DTO {
    public int $syncId = 0;

    public function Validate() {
        if (!\Validate::Exist($this->syncId))
            throw new \ValidateException;
    }
}

class DTO_Admin_Update_Location extends \DTO {
    public int $locationId = 0;
    public ?string $latitude = null;
    public ?string $longitude = null;

    public function Validate() {
        \Validate::EnforceNull($this->latitude);
        \Validate::EnforceNull($this->longitude);

        if (!\Validate::Exist($this->locationId))
            throw new \ValidateException;
        if (!\Validate::Numeric($this->latitude))
            throw new \ValidateException;
        if (!\Validate::Numeric($this->longitude))
            throw new \ValidateException;
    }
}

/* 
 * Admin - System
 * ************************************************************************
 */

class DTO_Admin_Read_EnvironmentStats_Response {
    public bool $debug = DEBUG;
    public bool $maintenance = MAINTENANCE;
    public string $benchmark = '';
    public string $phpinfo = '';
}
class DTO_Admin_Read_TrafficStats_Response {
    public array $top = array();
    public int $avgHits = 0;
    public int $avgSizeIn = 0;
    public int $avgSizeOut = 0;
    public int $avgTime = 0;
}
class DTO_Admin_Read_CacheStats_Response {
    public int $uptime = 0;
    public array $hits = array();
    public array $size = array();
}
class DTO_Admin_Read_QueryIp extends \DTO {
    public string $ip = '';

    public function Validate() {
        if (
            !\Validate::Exist($this->ip)
            || !\Validate::IP($this->ip)
        )
            throw new \ValidateException;
    }
}
class DTO_Admin_Read_QueryIp_Response {
    public string $geoIsoCode = '';
    public string $geoName = '';
    public string $information = '';
    public mixed $blocklists = null;
    public bool $isBlocked = false;
}
class DTO_Admin_Update_IpBlock extends \DTO {
    public string $ip = '';
    public bool $block = false;

    public function Validate() {
        if (
            !\Validate::Exist($this->ip)
            || !\Validate::IP($this->ip)
        )
            throw new \ValidateException;
    }
}
class DTO_Admin_Read_log extends \DTO {
    public string $log = '';

    public function Validate() {
        if (!\Validate::Exist($this->log))
            // Default to the latest
            $this->log = 'log';
        else if (
            \Validate::Exist($this->log)
            && !\Validate::Pattern($this->log, \Log::PATTERN_LOG)
        )
            throw new \ValidateException;
    }
}
class DTO_Admin_Read_LogHistory_Response {
    public array $filenames = [];
    public array $filesizes = [];
}
