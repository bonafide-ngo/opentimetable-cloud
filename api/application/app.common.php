<?

namespace App;

/**
 * Common methods in App namespace
 */
class Common {

    /**
     * Parse timetables into a wek or semesters
     *
     * @param object $pPeriod
     * @param array $pTimetables
     * @return array
     */
    public static function ParseTimetables(object $pPeriod, array $pTimetables): array {
        // Check if a specific period or both semesters have been requested
        if ($pPeriod->week !== null || $pPeriod->semester !== null) {
            // Generate OTT
            $vOTT = new \OTT($pTimetables);
            return array($vOTT->Get());
        } else {
            // Split each semester per timetable
            $vTimetables = array();
            $vTimetables[APP_SEMESTER_1] = array();
            $vTimetables[APP_SEMESTER_2] = array();

            $vOTTs = array();
            foreach ($pTimetables as $vIndex => $vTimetable) {
                switch ($vTimetable['tmt_semester']) {
                    case APP_SEMESTER_1:
                        $vTimetables[APP_SEMESTER_1][] = $vTimetable;
                        break;
                    case APP_SEMESTER_2:
                        $vTimetables[APP_SEMESTER_2][] = $vTimetable;
                        break;
                }
            }

            // Generate OTT
            $vOTTs[APP_SEMESTER_1] = new \OTT($vTimetables[APP_SEMESTER_1]);
            $vOTTs[APP_SEMESTER_2] = new \OTT($vTimetables[APP_SEMESTER_2]);

            return array(
                APP_SEMESTER_1 => $vOTTs[APP_SEMESTER_1]->Get(),
                APP_SEMESTER_2 => $vOTTs[APP_SEMESTER_2]->Get()
            );
        }
    }

    /**
     * Parse a period into week or semester
     *
     * @param ?string $pPeriod
     * @return object
     */
    public static function ParsePeriod(?string $pPeriod = null): object {
        $vPeriod = new \stdClass();
        $vPeriod->week = null;
        $vPeriod->semester = null;

        if (!$pPeriod)
            return $vPeriod;

        $vPrefix = substr($pPeriod, 0, \Util::GetConfig('prefix.length'));
        switch ($vPrefix) {
            case \Util::GetConfig('prefix.week'):
                $vPeriod->week = substr($pPeriod, \Util::GetConfig('prefix.length'));
                break;
            case \Util::GetConfig('prefix.semester'):
                $vPeriod->semester = substr($pPeriod, \Util::GetConfig('prefix.length'));
                break;
        }

        return $vPeriod;
    }

    /**
     * Get user from JWT
     *
     * @param string $pProperty
     * @return string
     */
    public static function GetUser(string $pProperty = 'email'): string {
        // Decode id token
        $vDecodedIdToken = \MSAL::DecodeAzureJWT(\Util::GetCookie('cookie.property.msal.id'));
        if (isset($vDecodedIdToken[$pProperty]) && $vDecodedIdToken[$pProperty])
            return $vDecodedIdToken[$pProperty];
        else
            throw new \UnexpectedException();
    }

    /**
     * Get student id
     * 
     * @return string
     */
    public static function GetStudentId(): string {
        // Query extra property via graph api, because studentId is not a standard property
        $vStudentId = \MSAL::GetSomeone_ExtraProperty(\Util::GetCookie('cookie.property.msal.id'), \Util::GetCookie('cookie.property.msal.access'), \Util::GetConfig('msal.property.studentId'));
        if (!$vStudentId)
            throw new \UnexpectedException();

        return $vStudentId;
    }


    /**
     * Check user is in groups, first using JWT, else Graph API
     *
     * @param array $pGroups
     */
    public static function CheckUserInGroups(array $pGroups) {
        $vUserGroups = array();

        // Check via JWT first, avoiding graph api call preferably 
        $vDecodedIdToken = \MSAL::DecodeAzureJWT(\Util::GetCookie('cookie.property.msal.id'));
        if (isset($vDecodedIdToken['groups']) && !empty($vDecodedIdToken['groups']))
            $vUserGroups = $vDecodedIdToken['groups'];
        else
            // Query graph api as last resort
            $vUserGroups = \MSAL::GetMe_Groups(\Util::GetCookie('cookie.property.msal.access'));

        foreach ($pGroups as $vGroup) {
            if (in_array(\Util::GetConfig('msal.groups.' . $vGroup), $vUserGroups))
                // At least one group matches
                return;
        }

        // No matching groups
        throw new \UnexpectedException();
    }

    /**
     * Send an email
     *
     * @param string $pSubject
     * @param string $pBody
     * @param string $pEmail
     * @param string|null $pXname
     * @return void
     */
    public static function Send_Email(string $pSubject, string $pBody, string $pEmail, ?string $pXname = null) {
        global $gMailer;

        $vDay       = gmdate("d", NOW);
        $vMonth     = gmdate("m", NOW);
        $vYear      = gmdate("Y", NOW);

        $vHours     = gmdate("H", NOW);
        $vMinutes   = gmdate("i", NOW);
        $vSeconds   = gmdate("s", NOW);

        $vParams  = array();
        $vParams['txt_title']       = \Lang::Get('instance.i-title');
        $vParams['txt_header']      = $pSubject;
        $vParams['txt_body']        = $pBody;
        $vParams['txt_date']        = "$vDay/$vMonth/$vYear";
        $vParams['txt_time']        = "$vHours:$vMinutes:$vSeconds";

        $gMailer->clearAllRecipients();
        $gMailer->addAddress($pEmail, $pXname ? $pXname : '');
        $gMailer->msgHTML(\Util::ParseTemplate(PATH_TEMPLATE . 't.mail.wrapper.html', PATH_TEMPLATE . 't.mail.body.html', $vParams));
        $gMailer->msgPlain(\Util::ParseTemplate(PATH_TEMPLATE . 't.mail.wrapper.txt', PATH_TEMPLATE . 't.mail.body.txt', $vParams));
        $gMailer->Mail($pSubject);
    }

    /**
     * Send an SMS
     *
     * @param string $pMobile
     * @param string $pText
     * @param [type] $pSender
     * @return void
     */
    public static function Send_SMS(string $pMobile, string $pText, string $pSender = SMS_SENDER) {
        $pMobile = DEBUG ? SMS_DEBUG_MOBILE : $pMobile;

        // Sanitise mobile
        // N.B. The mobile number must contain only numbers, strip the leading +
        $vMobile = str_replace('+', '', $pMobile);

        switch (SMS_PROVIDER) {
            case APP_SMS_PROVIDER_VONAGE:
                if (DEBUG) {
                    // Use the Vonage Sandbox and hardcode the 'base_api_url' and the 'from' parameters
                    // https://developer.vonage.com/en/messages/code-snippets/whatsapp/send-text?source=messages
                    // https://github.com/Vonage/vonage-php-sdk-core
                    try {
                        $vCredentials  = new \Vonage\Client\Credentials\Basic(SMS_CREDENTIALS[SMS_PROVIDER][APP_SMS_API_KEY], SMS_CREDENTIALS[SMS_PROVIDER][APP_SMS_API_SECRET]);
                        $vClient = new \Vonage\Client($vCredentials, ['base_api_url' => 'https://messages-sandbox.nexmo.com']);
                        $vMessage = new \Vonage\Messages\Channel\WhatsApp\WhatsAppText($vMobile, '14157386102', $pText);
                        $vResponse = $vClient->messages()->send($vMessage);

                        // All good, log for debugging only
                        \Log::Debug(__FILE__, __METHOD__, __LINE__, array('WhatsApp', $vMobile, $pText), true);
                    } catch (\Throwable $e) {
                        \Log::Error(__FILE__, __METHOD__, __LINE__, array('WhatsApp', $e->getMessage()), true, false, false);
                    }
                } else {
                    try {
                        // https://dashboard.nexmo.com/getting-started/sms
                        // https://github.com/Vonage/vonage-php-sdk-core
                        $vCredentials  = new \Vonage\Client\Credentials\Basic(SMS_CREDENTIALS[SMS_PROVIDER][APP_SMS_API_KEY], SMS_CREDENTIALS[SMS_PROVIDER][APP_SMS_API_SECRET]);
                        $vClient = new \Vonage\Client($vCredentials);
                        $vMessage = new \Vonage\SMS\Message\SMS($vMobile, $pSender, $pText);

                        $vSMS = $vClient->sms()->send($vMessage);
                        $vResponse = $vSMS->current();

                        if ($vResponse->getStatus())
                            // Something went wrong if different from 0
                            \Log::Error(__FILE__, __METHOD__, __LINE__, array('SMS', $vResponse->getStatus()), true, false, false);
                        else
                            // All good, log for debugging only
                            \Log::Debug(__FILE__, __METHOD__, __LINE__, array('SMS', $vMobile, $pText), true);
                    } catch (\Throwable $e) {
                        \Log::Error(__FILE__, __METHOD__, __LINE__, array('SMS', $e->getMessage()), true, false, false);
                    }
                }
                break;

            default:
                \Log::Error(__FILE__, __METHOD__, __LINE__, array('Invalid SMS provider', SMS_PROVIDER), true, false, false);
                break;
        }
    }
}
