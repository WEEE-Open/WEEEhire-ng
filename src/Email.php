<?php


namespace WEEEOpen\WEEEhire;


use Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Email {
	public static function sendMail(string $to, string $subject, string $body) {
		if(defined('TEST_MODE') && TEST_MODE) {
			error_log("Test mode enabled, NOT sending email to $to, subject: $subject\nBody:\n$body");
			return;
		}

		$mail = new PHPMailer(true);
		$mail->isMail();
		$mail->CharSet = 'utf-8';

		try {
			$mail->setFrom('noreply@join.weeeopen.it', 'WEEE Open');
			$mail->addAddress($to);

			$mail->isHTML(false);
			$mail->Subject = $subject;
			$mail->Body = $body;

			$mail->send();
		} catch(Exception $e) {
			throw new MailException();
		}
	}
}