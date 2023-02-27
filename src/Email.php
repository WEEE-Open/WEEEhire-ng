<?php

namespace WEEEOpen\WEEEhire;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Email
{
	/**
	 * Send an email from noreply@join.weeeopen.it
	 * If TEST_MODE is enabled, no email is sent and this method prints the contents on the console instead.
	 *
	 * @param string $to Address
	 * @param string $subject Subject line
	 * @param string $body Email body, plain text only
	 */
	public static function sendMail(string $to, string $subject, string $body)
	{
		if (defined('TEST_MODE') && TEST_MODE) {
			error_log("Test mode enabled, NOT sending email to $to, subject: $subject\nBody:\n$body");
			return;
		}

		$mail = new PHPMailer(true);
		$mail->isSMTP();
		$mail->CharSet = 'utf-8';

		$mail->setFrom('noreply@join.weeeopen.it', 'WEEE Open');
		$mail->addAddress($to);

		// Quirks mode! That mail server converts 8bit to quoted-printable, which is not a problem.
		// Unless it has to forward the email to another address, then the DKIM validation fails and nothing is
		// forwarded. Let's hope that this prevents the mail server from needlessly tampering with the content.
		if (Utils::endsWith(strtolower($to), '@polito.it')) {
			$mail->Encoding = PHPMailer::ENCODING_QUOTED_PRINTABLE;
		}

		$mail->isHTML(false);
		$mail->Subject = $subject;
		$mail->Body = $body;

		// Talk to the SMTP server in a sibling container over the podman network.
		// Do not use any TLS or Auth as the entire process is internal.
		$mail->Host = 'mail';
		$mail->SMTPSecure = false;
		$mail->SMTPAutoTLS = false;

		$mail->send();
	}
}
