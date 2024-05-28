<?php

require "config.php";
require "mailer/EmailSender.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $project_type = filter_input(INPUT_POST, 'project_type', FILTER_SANITIZE_STRING);
    $name_address = filter_input(INPUT_POST, 'name_address', FILTER_SANITIZE_STRING);
    $phone_number = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);
    $email_id = filter_input(INPUT_POST, 'email_id', FILTER_SANITIZE_EMAIL);
    $sanctioned_load = $_POST["sanctioned_load"];
    $avg_monthly_bill = $_POST["avg_monthly_bill"];


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
        'project_type' => htmlspecialchars($project_type),
        'name_address' => htmlspecialchars($name_address),
        'phone_number' => htmlspecialchars($phone_number),
        'email_id' => htmlspecialchars($email_id),
        'sanctioned_load' => htmlspecialchars($sanctioned_load),
        'avg_monthly_bill' => htmlspecialchars($avg_monthly_bill)
    ];

    $server_config = [
        'host' => SMTP_HOST,
        'port' => SMTP_PORT_TLS,
        'username' => SMTP_USERNAME,
        'password' => SMTP_PASSWORD,
        'from_email' => SMTP_FROM,
        'from_name' => SMTP_FROM_NAME,
        'reply_to' => '',
        'smtp_debug' => 0,
    ];

    $body = array(
        "template_file" => "home/contact-us.html",
        "placeholders" => $placeholders
    );

    // Create EmailSender instance
    $emailSender = new EmailSender($server_config, $body, true);

    // Send email
    $response = $emailSender->send();
    // Send email
    echo json_encode($response);

}else{
    http_response_code(400);
}