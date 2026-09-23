<?php

$to = "info@gi.by";
$subject = "Test Email"; 
$message = "Приут 312"; 
$headers = "From: oprosnik@gi.by\r\n"; 
if (mail($to, $subject, $message)) {
    echo "Mail sent successfully!";
} else {
    echo "Failed to send mail.";
}

?>
