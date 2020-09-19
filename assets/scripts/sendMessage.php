<?php
	$to = 'jd.calvo@dctprime.com';
	$subject = "Te han enviado un mensaje.";
	$message = '<div>
		<p>Nombre:'.$_POST["name"].'</p>
		<p>E-correo:'.$_POST["email"].'</p>
		<p>Contacto:'.$_POST["phone"].'</p>
		<p>Asunto:'.$_POST["subject"].'</p>
		<p>Mensaje:'.$_POST["message"].'</p>
	</div>';
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	$headers .= 'From: <no-reply@dctprime.com>' . "\r\n";
    mail( $to, $subject , $message, $headers );
?>
