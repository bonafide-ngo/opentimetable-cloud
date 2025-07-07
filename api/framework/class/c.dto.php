<?
class DTO {

    /**
     * Constructor
     *
     * @param mixed $pParams
     * @param boolean $pGC
     */
    function __construct(object &$pParams = null, bool $pGC = true) {
        if (!empty($pParams)) {
            // Sanitise the DTO
            array_walk($pParams, array('Security', 'SanitizeInput'));

            try {
                // Initialise the DTO
                foreach ($this as $vKey => $vValue) {
                    if (isset($pParams->$vKey)) {
                        // Set value
                        $this->$vKey = $pParams->$vKey;
                    }
                }
            } catch (\Throwable $e) {

                // Catch any casting issue
                throw new ValidateException();
            }
        }

        // Validate the DTO
        $this->Validate();

        // Garbace collection
        if ($pGC) {
            $pParams = null;
            unset($pParams);
        }
    }

    /**
     * Validate DTO, to be overridden
     *
     * @return void
     */
    public function Validate() {
    }
}
