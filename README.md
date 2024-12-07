# Email Sender Class Documentation

## EmailSender Constructor

The `EmailSender` class is designed to send emails with various customization options, such as inline HTML content, HTML templates with placeholders, and attachments.

### Constructor

```php
/**
 * EmailSender constructor.
 * 
 * @param array $config The SMTP configuration parameters.
 *        Expected keys: host, port, username, password,
 *                        from_email, from_name, reply_to, smtp_debug (optional).
 *
 * @param string|array $body
 *        - If a string, it represents inline HTML content.
 *        - If an array, it should contain the path to the HTML template file and placeholders.
 *          Example: [
 *            'template_file' => 'path/to/template.html',
 *            'placeholders' => ['name' => 'John'],
 *        ]
 *
 * @param bool $isHTMLFile
 *        Defines whether the `$body` is an HTML file (true) or inline HTML content (false).
 *
 * @param array $attachments (Optional)
 *        Array of attachments with file paths and names.
 *        Example: [['path' => 'path/to/file.pdf', 'name' => 'attachment.pdf']]
 */
```

---

## SMTP Configuration: `config.php`

Set up your SMTP server configuration in a separate `config.php` file.

```php
// SMTP Configuration Constants
const SMTP_HOST = 'hostname'; // SMTP server address
const SMTP_PORT_SSL = 465;    // SSL port
const SMTP_PORT_TLS = 587;    // TLS port
const SMTP_USERNAME = 'smtp_username'; // SMTP username
const SMTP_PASSWORD = 'smtp_password'; // SMTP password
const SMTP_FROM = 'smtp_email'; // "From" email address
const SMTP_FROM_NAME = 'sender_name_to_display'; // Display name for sender
```

---

## Usage Example with HTML Template

Here's how to use the `EmailSender` with an HTML template and placeholders:

```php
<?php

require "config.php";
require "mailer/EmailSender.php";

// Helper function to fetch JSON values with default fallback
function getJsonValue($json_data, $key, $default = '')
{
    return isset($json_data[$key]) ? $json_data[$key] : $default;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and decode the incoming JSON data
    $post_data = file_get_contents("php://input");
    $json_data = json_decode($post_data, true);

    if ($json_data === null) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data.']);
        exit;
    }

    // Extract relevant fields
    $project_type = getJsonValue($json_data, 'project_type');
    $name_address = getJsonValue($json_data, 'name_address');
    $phone_number = getJsonValue($json_data, 'phone_number');
    $email_id = getJsonValue($json_data, 'email_id');
    $sanctioned_load = getJsonValue($json_data, 'sanctioned_load');
    $avg_monthly_bill = getJsonValue($json_data, 'avg_monthly_bill');

    // Validation
    $errors = [];
    if (empty($phone_number) || !preg_match('/^\d{10}$/', $phone_number)) {
        $errors[] = 'A valid 10-digit phone number is required.';
    }
    if (empty($email_id) || !filter_var($email_id, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    if (!empty($errors)) {
        echo json_encode(['status' => false, 'message' => 'Validation failed.', 'error' => $errors]);
        exit;
    }

    // Prepare placeholders for the HTML template
    $placeholders = [
        'project_type' => $project_type,
        'name_address' => $name_address,
        'phone_number' => $phone_number,
        'email_id' => $email_id,
        'sanctioned_load' => $sanctioned_load,
        'avg_monthly_bill' => $avg_monthly_bill
    ];

    // Server configuration (from config.php)
    $server_config = [
        'host' => SMTP_HOST,
        'port' => SMTP_PORT_TLS,
        'username' => SMTP_USERNAME,
        'password' => SMTP_PASSWORD,
        'from_email' => SMTP_FROM,
        'from_name' => SMTP_FROM_NAME,
        'to' => $email_id,
        'subject' => 'Solar Project Enquiry',
        'smtp_debug' => 0, // Optional: Set to 1 for debugging
    ];

    // Set up the body content and placeholders for the email template
    $body = [
        "template_file" => dirname(__FILE__) . '/template/contact-us.html',
        "placeholders" => $placeholders
    ];

    // Create an EmailSender instance
    $emailSender = new EmailSender($server_config, $body, true);

    // Send the email and capture the response
    $response = $emailSender->send();

    // Output the response as JSON
    echo json_encode($response);

} else {
    // If not a POST request, send a 400 Bad Request response
    http_response_code(400);
}
```

---

## Usage Example with Inline HTML Content

If you prefer to send inline HTML content rather than using a template, here's how you can do it:

```php
// Inline HTML content
$body = "Inline HTML content here..."; // Your HTML content goes here
$isHTMLFile = false; // Indicating inline HTML

// Create an EmailSender instance with inline HTML
$emailSender = new EmailSender($server_config, $body, $isHTMLFile);
```

---

## Usage Example with Email Attachments

To send attachments with your email, simply pass an array of file paths and names as shown below:

```php
// Attachments to send
$attachments = [
    ['path' => 'path/to/attachment1.pdf', 'name' => 'Attachment1.pdf'],
    ['path' => 'path/to/attachment2.docx', 'name' => 'Attachment2.docx'],
];

// Create an EmailSender instance with attachments
$emailSender = new EmailSender($server_config, $body, true, $attachments);

// Send the email
$response = $emailSender->send();

// Output the response
echo json_encode($response);
```

---

## Features:

- **SMTP Configuration**: Easily configurable to send emails via your SMTP server.
- **HTML Templates**: Support for dynamic HTML templates with placeholders.
- **Inline HTML**: Option to send inline HTML content directly.
- **Attachments**: Send multiple attachments with your email.
- **Validation**: Built-in validation for email and phone number.
- **Customizable**: Easily extendable with your own configurations and custom templates.

---

The `EmailSender` class provides a flexible and powerful way to send emails with different types of content and configurations. Whether you're sending an email with inline HTML, using an HTML template, or attaching files, this class has you covered.


