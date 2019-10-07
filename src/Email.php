<?php


namespace WEEEOpen\WEEEhire;


use PHPMailer\PHPMailer\PHPMailer;

class Email {
	public static function sendMail(string $to, string $subject, string $body) {
		$mail = new PHPMailer(true);
		$mail->isMail();

		try {
			$mail->setFrom('noreply@join.weeeopen.it', 'WEEE Open');
			$mail->addAddress($to);

			$mail->isHTML(false);
			$mail->Subject = $subject;
			$mail->Body = $body;
			$mail->send();
		} catch(\Exception $e) {
			throw new MailException();
		}
	}
}