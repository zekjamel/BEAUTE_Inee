<?

$firstname  = $_REQUEST["firstname"];
$lastname  = $_REQUEST["lastname"];
$email = $_REQUEST["email"];
$phone  = $_REQUEST["phone"];
$service = $_REQUEST["service"];
$staff  = $_REQUEST["staff"];
$date   = $_REQUEST["date"];
$to    = "you@yourdomain.com"; // ENTER YOUR EMAIL ADDRESS
if (isset($firstname) && isset($email)) {
    $email_subject = "$firstname $lastname sent you a message via YOUR SITE NAME"; // ENTER YOUR EMAIL SUBJECT
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/plain; charset=iso-8859-1" . "\r\n";
	$headers = 'From: ' . $firstname ."\r\n" .
        'Reply-To: ' . $email . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
	$msg     = "
	Service: $service,
	Staff: $staff,
	Appointment Date: $date,
	First Name: $firstname,
	Last Name: $lastname,
	Email: $email,
	Phone Number: $phone";
	
   $mail =  mail($to, $email_subject, $msg, $headers);
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

$firstname = clean_input(filter_input(INPUT_POST, 'firstname'));
$lastname  = clean_input(filter_input(INPUT_POST, 'lastname'));
$email     = clean_input(filter_input(INPUT_POST, 'email'));
$phone     = clean_input(filter_input(INPUT_POST, 'phone'));
$service   = clean_input(filter_input(INPUT_POST, 'service'));
$staff     = clean_input(filter_input(INPUT_POST, 'staff'));
$date      = clean_input(filter_input(INPUT_POST, 'date'));
$to        = 'you@yourdomain.com';

if (empty($firstname) || empty($email)) {
	http_response_code(400);
	echo 'missing_fields';
	exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	http_response_code(400);
	echo 'invalid_email';
	exit;
}

$email_subject = sprintf('%s %s sent you a message via YOUR SITE NAME', $firstname, $lastname);
$headers  = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/plain; charset=UTF-8" . "\r\n";
$headers .= 'From: ' . $firstname . "\r\n" .
	'Reply-To: ' . $email . "\r\n" .
	'X-Mailer: PHP/' . phpversion();

$msg = "Service: $service\nStaff: $staff\nAppointment Date: $date\nFirst Name: $firstname\nLast Name: $lastname\nEmail: $email\nPhone Number: $phone";

$sent = @mail($to, $email_subject, $msg, $headers);

if ($sent) {
	echo 'success';
} else {
	http_response_code(500);
	echo 'failed';
}

?>