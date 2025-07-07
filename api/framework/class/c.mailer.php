<?

/**
 * Mailer Class extending PHPMailer
 */
class Mailer extends \PHPMailer\PHPMailer\PHPMailer {

    protected $mSmtp;
    protected $mWebmasterEmail;
    protected $mWebmasterAlias;
    protected $mNoreplyEmail;
    protected $mNoreplyAlias;
    protected $mDkim;
    protected $mDkimDomain;
    protected $mDkimPrivatePath;
    protected $mDkimSelector;

    const NEWLINE = 'NEWLINE';

    /**
     * @param array $pParams
     */
    public function  __construct(array $pParams = array()) {
        // Generate an Exception if something goes wrong
        try {
            $this->IsSMTP(); // Instruct to use SMTP 
            $this->SMTPDebug = false; // Disable SMTP debug information on screen

            // Set HTML email by default
            $this->isHTML(true);

            // Follow PHPMailer naming convention
            $this->Timeout          = array_key_exists('timeout', $pParams)                 ? $pParams['timeout']               : null;

            $this->Host             = array_key_exists('smtp_host', $pParams)               ? $pParams['smtp_host']             : null;
            $this->Port             = array_key_exists('smtp_port', $pParams)               ? $pParams['smtp_port']             : null;
            $this->Username         = array_key_exists('smtp_username', $pParams)           ? $pParams['smtp_username']         : null;
            $this->Password         = array_key_exists('smtp_password', $pParams)           ? $pParams['smtp_password']         : null;
            $this->SMTPAuth         = array_key_exists('smtp_authentication', $pParams)     ? $pParams['smtp_authentication']   : null;
            $this->SMTPSecure       = array_key_exists('smtp_secure', $pParams)             ? $pParams['smtp_secure']           : null;

            $this->mSmtp            = array_key_exists('smtp', $pParams)                    ? $pParams['smtp']                  : null;
            $this->mWebmasterEmail  = array_key_exists('webmaster_email', $pParams)         ? $pParams['webmaster_email']       : null;
            $this->mWebmasterAlias  = array_key_exists('webmaster_alias', $pParams)         ? $pParams['webmaster_alias']       : null;
            $this->mNoreplyEmail    = array_key_exists('noreply_email', $pParams)           ? $pParams['noreply_email']         : null;
            $this->mNoreplyAlias    = array_key_exists('noreply_alias', $pParams)           ? $pParams['noreply_alias']         : null;

            $this->mDkim            = array_key_exists('dkim', $pParams)                    ? $pParams['dkim']                  : null;
            $this->mDkimDomain      = array_key_exists('dkim_domain', $pParams)             ? $pParams['dkim_domain']           : null;
            $this->mDkimPrivatePath = array_key_exists('dkim_private_path', $pParams)       ? $pParams['dkim_private_path']     : null;
            $this->mDkimSelector    = array_key_exists('dkim_selector', $pParams)           ? $pParams['dkim_selector']         : null;

            // Set encoding
            $this->CharSet = 'UTF-8';
            // Set Sender/ReplyTo
            $this->setFrom($this->mNoreplyEmail, $this->mNoreplyAlias);
            $this->addReplyTo($this->mNoreplyEmail, $this->mNoreplyAlias);
        } catch (Exception $e) {
            // Do not email to avoid endless loop
            Log::Error(__FILE__, __METHOD__, __LINE__, $e->getMessage());
        }
    }

    /**
     * Evaluates the message and returns modifications for inline images and backgrounds
     *
     * @param string $pMessage
     * @return void
     */
    public function msgPlain(string $pMessage) {
        $vMessage = strip_tags($pMessage);
        $vMessage = str_replace("\r", '', $vMessage);
        $vMessage = str_replace("\n", '', $vMessage);
        $vMessage = str_replace(self::NEWLINE, NL, $vMessage);
        $vMessage = trim($vMessage);
        $vMessage = html_entity_decode(Util::DecodeAccents($vMessage));

        $this->AltBody = $vMessage;
    }

    /**
     * Sign email before sending
     * Check DKIM cert against server
     *
     * @return void
     */
    private function SignDkim() {
        if (!$this->mDkim)
            return;

        //This should be the same as the domain of your From address
        $this->DKIM_domain = $this->mDkimDomain;
        //See the DKIM_gen_keys.phps script for making a key pair -
        //here we assume you've already done that.
        //Path to your private key:
        $this->DKIM_private = $this->mDkimPrivatePath;
        //Set this to your own selector
        $this->DKIM_selector = $this->mDkimSelector;
        //Put your private key's passphrase in here if it has one
        $this->DKIM_passphrase = '';
        //The identity you're signing as - must match the From and SMTP account
        $this->DKIM_identity = $this->mNoreplyEmail;
        //Suppress listing signed header fields in signature, defaults to true for debugging purpose
        $this->DKIM_copyHeaderFields = false;
        //Optionally you can add extra headers for signing to meet special requirements
        $this->DKIM_extraHeaders = [];
    }

    /**
     * Compose and Send email
     *
     * @param string $pSubject
     * @return boolean
     */
    public function Mail(string $pSubject): bool {
        if (!$this->mSmtp)
            return false;

        // Add Subject
        $this->Subject = html_entity_decode(Util::DecodeAccents($pSubject));

        // Debug 
        if (DEBUG) {
            $this->clearAllRecipients();
            $this->addAddress($this->mWebmasterEmail, $this->mWebmasterAlias);
        }

        // Add DKIM signature
        $this->SignDkim();

        // Send
        if ($this->send())
            return true;
        else if ($this->ErrorInfo) {
            // Do not email to avoid endless loop 
            // Do not add error to avoid code blockage
            Log::Error(__FILE__, __METHOD__, __LINE__, $this->ErrorInfo, false, false, false);
            return false;
        } else
            return false;
    }
}
