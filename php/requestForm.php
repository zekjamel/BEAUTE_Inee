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

$email = clean_input(filter_input(INPUT_POST, 'email'));
$to    = 'you@yourdomain.com';

if (empty($email)) {
	http_response_code(400);
	echo 'missing_fields';
	exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	http_response_code(400);
	echo 'invalid_email';
	exit;
}

$email_subject = sprintf('This message was sent via YOUR SITE NAME to get the discount for user with %s (Request Form)', $email);
$headers  = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/plain; charset=UTF-8" . "\r\n";
$headers .= 'Reply-To: ' . $email . "\r\n" .
	'X-Mailer: PHP/' . phpversion();

$msg = "Email: $email";

$sent = @mail($to, $email_subject, $msg, $headers);

if ($sent) {
	echo 'success';
} else {
	http_response_code(500);
	echo 'failed';
}

?>