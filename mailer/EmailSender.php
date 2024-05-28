<?php

require "EmailConfig.php";

class EmailSender extends EmailConfig
{

    private $body;
    private $isHTMLFile;
    private $attachments;

    /**
     * EmailSender constructor.
     * @param array $config The SMTP configuration parameters.
     *                      Expected keys: host, port, username, password, from_email, from_name, reply_to, smtp_debug (optional).
     * @param string|array $body If it's a string, it represents inline HTML content. If it's an array,
     *                            it should contain the path to the HTML template file and placeholders.
     *                            Example: ['template_file' => 'path/to/template.html', 'placeholders' => ['name' => 'John', 'email' => 'example@example.com']]
     * @param bool $isHTMLFile Determines if $body is an HTML file or inline HTML.
     * @param array $attachments Array containing file paths and names for attachments. Example: [['path' => 'path/to/file.pdf', 'name' => 'attachment.pdf']].
     */
    public function __construct(array $config, $body, $isHTMLFile, array $attachments = [])
    {
        parent::__construct($config);
        $this->body = $body;
        $this->isHTMLFile = $isHTMLFile;
        $this->attachments = $attachments;
    }

    /**
     * @throws Exception
     */
    protected function loadTemplate($templateFile, array $placeholders)
    {
        if (!file_exists($templateFile)) {
            throw new Exception("HTML Template file not found");
        }

        $template = file_get_contents($templateFile);

        foreach ($placeholders as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }

        return $template;
    }

    protected function formatMessage($to, $subject, $body, array $attachments)
    {
        try {
            $this->mail->addAddress($to);

            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;

            $this->mail->msgHTML($body);

            foreach ($attachments as $attachment) {
                $this->mail->addAttachment($attachment['path'], $attachment['name']);
            }
        } catch (Exception $e) {
            echo "Failed to format HTML Template. Error: {$this->mail->ErrorInfo}";
        }
    }

    public function send()
    {
        try {
            if ($this->isHTMLFile) {
                $this->body = $this->loadTemplate($this->body["template_file"], $this->body['placeholders']);
            }
            $this->formatMessage($this->config['to'], $this->config['subject'], $this->body, $this->attachments);
            $this->mail->send();
            return [
                "status" => true,
                "message" => "Email Sent"
            ];
        } catch (Exception $e) {
            return [
                "status" => false,
                "message" => "Failed to send email. Error: {$this->mail->ErrorInfo}"
            ];
        }
    }
}


