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
if($mail)
	{
		echo 'success';
	}

else
	{
		echo 'failed';
	}
}

?>