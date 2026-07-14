<?

$name  = $_REQUEST["name"];
$email = $_REQUEST["email"];
$message = $_REQUEST["message"];
$to    = "you@yourdomain.com"; // ENTER YOUR EMAIL ADDRESS
if (isset($email) && isset($name)) {
    $email_subject = "$name sent you a comment via YOUR SITE NAME"; // ENTER YOUR EMAIL SUBJECT
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/plain; charset=UTF-8" . "\r\n";
	$headers = 'From: ' . $name ."\r\n" .
        'Reply-To: ' . $email . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
	$message     = "
	From: $name,
	Email: $email,
	Message: $message";
	
    $mail =  mail($to, $email_subject, $message, $headers);

<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo 'method_not_allowed';
	exit;
}

function clean_input(?string $v): string
{
	if ($v === null) return '';
	$v = trim($v);
	$v = preg_replace('/[\r\n]+/', ' ', $v);
	return filter_var($v, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
}

$name    = clean_input(filter_input(INPUT_POST, 'name'));
$email   = clean_input(filter_input(INPUT_POST, 'email'));
$message = clean_input(filter_input(INPUT_POST, 'message'));
$to      = 'you@yourdomain.com';

if (empty($email) || empty($name)) {
	http_response_code(400);
	echo 'missing_fields';
	exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	http_response_code(400);
	echo 'invalid_email';
	exit;
}

$email_subject = sprintf('%s sent you a comment via YOUR SITE NAME', $name);
$headers  = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/plain; charset=UTF-8" . "\r\n";
$headers .= 'From: ' . $name . "\r\n" .
	'Reply-To: ' . $email . "\r\n" .
	'X-Mailer: PHP/' . phpversion();

$body = "From: $name\nEmail: $email\nMessage: $message";

$sent = @mail($to, $email_subject, $body, $headers);

if ($sent) {
	echo 'success';
} else {
	http_response_code(500);
	echo 'failed';
}

?>