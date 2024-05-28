<?php

require "smtp\src\PHPMailer.php";
require "smtp\src\Exception.php";
require "smtp\src\SMTP.php";

use smtp\src\Exception;
use smtp\src\PHPMailer;

class EmailConfig
{
    protected $mail;
    protected $config;

    /**
     * EmailConfig constructor.
     * @param array $config The SMTP configuration parameters.
     *                      Expected keys: host, port, username, password, from_email, from_name, reply_to, smtp_debug (optional).
     */
    public function __construct(array $config) {
        $this->config = $config;
        $this->mail = new PHPMailer(true);
        $this->setup();
    }

    protected function setup() {
        try {
            $this->mail->isSMTP();
            $this->mail->Host       = $this->config['host'];
            $this->mail->Port       = $this->config['port'];
            $this->mail->Username   = $this->config['username'];
            $this->mail->Password   = $this->config['password'];

            $this->mail->SMTPAuth   = true;
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->SMTPDebug = isset($this->config['smtp_debug']) ? $this->config['smtp_debug'] : 0;

            $this->mail->setFrom($this->config['from_email'], $this->config['from_name']);
            if (isset($this->config['reply_to']) && !empty($this->config['reply_to'])) {
                $this->mail->addReplyTo($this->config['reply_to']);
            }
        } catch (Exception $e) {
            echo "Mailer Error: {$this->mail->ErrorInfo} \n {$e->errorMessage()}";
        }
    }
}

