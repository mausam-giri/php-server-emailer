### Email Sender Constructor

```php
    /**
     * EmailSender constructor.
     * @param array $config The SMTP configuration parameters.
     *       Expected keys: host, port, username, password,
     *                      from_email, from_name, reply_to, smtp_debug (optional).

     * @param string|array $body
     *       If it's a string, it represents inline HTML content. If it's an array,
     *       it should contain the path to the HTML template file and placeholders.
     *       Example: [
     *                  'template_file' => 'path/to/template.html',
     *                  'placeholders' => ['name' => 'John',
     *                ]

     * @param bool $isHTMLFile
     *       Determines if $body is an HTML file or inline HTML.

     * @param array $attachments
     *       Array containing file paths and names for attachments.
     *       Example: ['path' => 'path/to/file.pdf', 'name' => 'attachment.pdf'].
     */
```

### Setting Config file : config.php

```php

const SMTP_HOST = 'hostname';
const SMTP_PORT_SSL = 465; // ssl
const SMTP_PORT_TLS = 587; // tls
const SMTP_USERNAME = 'smtp_username';
const SMTP_PASSWORD = 'smtp_password';
const SMTP_FROM = 'smtp_email';
const SMTP_FROM_NAME = "sender_name_to_display";
```

### Usage Example with HTML Template

```php

<?php

require "config.php";
require "mailer/EmailSender.php";

function getJsonValue($json_data, $key, $default = '')
{
    return isset($json_data[$key]) ? $json_data[$key] : $default;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $post_data = file_get_contents("php://input");

    $json_data = json_decode($post_data, true);

    if ($json_data === null) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data.'
        ]);
        exit;
    }

    $project_type = getJsonValue($json_data, 'project_type');
    $name_address = getJsonValue($json_data, 'name_address');
    $phone_number = getJsonValue($json_data, 'phone_number');
    $email_id = getJsonValue($json_data, 'email_id');
    $sanctioned_load = getJsonValue($json_data, 'sanctioned_load');
    $avg_monthly_bill = getJsonValue($json_data, 'avg_monthly_bill');


    $errors = [];


    if (empty($phone_number) || !preg_match('/^\d{10}$/', $phone_number)) {
        $errors[] = 'A valid 10-digit phone number is required.';
    }
    if (empty($email_id) || !filter_var($email_id, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    if (!empty($errors)) {
        echo json_encode([
            'status' => false,
            'message' => 'Validation failed.',
            'error' => $errors
        ]);
        exit;
    }

    // Prepare email placeholders
    $placeholders = [
        'project_type' => $project_type,
        'name_address' => $name_address,
        'phone_number' => $phone_number,
        'email_id' => $email_id,
        'sanctioned_load' => $sanctioned_load,
        'avg_monthly_bill' => $avg_monthly_bill
    ];

    // Using config from config.php
    $server_config = [
        'host' => SMTP_HOST,
        'port' => SMTP_PORT_TLS,
        'username' => SMTP_USERNAME,
        'password' => SMTP_PASSWORD,
        'from_email' => SMTP_FROM,
        'from_name' => SMTP_FROM_NAME,
        'to' => $email_id,
        'subject' => 'Solar Project Enquiry',
        'smtp_debug' => 0, // optional if you want to get debug information
    ];

    // Set body content for the HTML Template and
    // variable to replace in HTML Template
    $body = array(
        "template_file" => dirname(__FILE__). '\template\contact-us.html',
        "placeholders" => $placeholders
    );

    // Create EmailSender instance
    $emailSender = new EmailSender($server_config, $body, true);

    // Send email
    $response = $emailSender->send();
    // Send Response
    echo json_encode($response);

}else{
    http_response_code(400);
}

```

### Usage Example with inline HTML

```php
    $body = "Inline HTML content";
    $isHTMLFile = false;
    $emailSender = new EmailSender($config, $body, $isHTMLFile);
```

### Usage example with Email Attachments

```php
    // Attachments to send
    $attachments = [
        ['path' => 'path/to/attachment1.pdf', 'name' => 'Attachment1.pdf'],
        ['path' => 'path/to/attachment2.docx', 'name' => 'Attachment2.docx'],
    ];
    $emailSender = new EmailSender($server_config, $body, true, $attachments);
```
