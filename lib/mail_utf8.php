<?php
//function to send utf-8 encoded plain text mails
function mail_utf8($to, $from, $subject = '(No subject)', $message = ''){	
	$subject = "=?UTF-8?B?".base64_encode($subject)."?=";
	$headers = array();
	$headers[] = "From: $from";
	$headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/plain; charset=UTF-8";
	$headers = implode ("\r\n", $headers) . "\r\n";
     	return mail($to, $subject, $message, $headers);
   }
?>
