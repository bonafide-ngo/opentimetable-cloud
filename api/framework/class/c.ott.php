<?

/**
 * OpenTimetable Class
 */
class OTT {

    protected $mOTT = array();

    /**
     * Constructor
     *
     * @param array $pResults
     */
    public function __construct(array $pResults) {
        // Init OTT
        $this->mOTT =
            array(
                "metadata" => array(
                    "version" => "1.0",
                    "timezone" => Util::GetConfig('php.date.timezone'),
                    "timezone" => Util::GetConfig('ott.author'),
                    "timestamp" => NOW
                ),
                "order" => Util::GetConfig('ott.order'),
                "cues" => array(
                    "periods" => Util::GetConfig('ott.periods'),
                    "recesses" => array()
                ),
                "days" => array()
            );

        // Set days 
        self::SetDays();
        // Set classes
        self::SetClasses($pResults);
    }
    /**
     * Get OTT
     *
     * @return array
     */
    public function Get(): array {
        return $this->mOTT;
    }

    /**
     * Get OTT as JSON
     *
     * @return string|false
     */
    public function GetJSON(): string|bool {
        return json_encode($this->Get());
    }

    /**
     * Set days
     *
     * @return void
     */
    protected function SetDays(): void {
        foreach (Util::GetConfig('ott.days') as $vDay) {
            $this->mOTT['days'][\Lang::get('static.' . $vDay)] = array(
                "classes" => array(),
                "events" => array(),
                "dayevents" => array()
            );
        }
    }

    /**
     * Set classes
     *
     * @param array $pResults
     * @return void
     */
    protected function SetClasses(array $pResults): void {
        foreach (Util::GetConfig('ott.days') as $vDayKey => $vDayValue) {
            // N.B. Day index starts from 1, while DayKey starts from 0
            $vDayIndex = ++$vDayKey;
            $vDayLabel = \Lang::get('static.' . $vDayValue);

            foreach ($pResults as $vResult) {
                if ($vResult['tmt_day'] == $vDayIndex) {
                    if (!isset($this->mOTT['days'][$vDayLabel]))
                        $this->mOTT['days'][$vDayLabel] = array(
                            "classes" => array(),
                            "events" => array(),
                            "dayevents" => array()
                        );

                    // Set classes for selected day and period
                    $this->mOTT['days'][$vDayLabel]['classes'][$vResult['tmt_period']][] = array(
                        "substitution" => false,
                        "examination" => false,
                        "canceled" => false,
                        "name" => $vResult['tmt_activity_name'],
                        "abbreviation" => $vResult['tmt_module'],
                        "location" => $vResult['tmt_vnx_code'],
                        "hosts" => array(),
                        // Extension
                        "extension" => array(
                            "group" => $vResult['tmt_display_class_group'] ? $vResult['tmt_class_group'] : null,
                            "link" => $vResult['tmt_module_link']
                        )
                    );
                }
            }
        }
    }
}
