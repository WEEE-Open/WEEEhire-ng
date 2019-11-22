<?php

namespace WEEEOpen\WEEEHire;

use DateTime;
use DateTimeZone;
use Exception;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

$template = Template::create();

Utils::requireAdmin();

$db = new Database();

$error = null;

if($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Form submission
	$changed = false;
	if(isset($_POST['noexpiry'])) {
		// Unset form expiry
		$db->unsetConfigValue('expiry');
		$changed = true;
	} elseif(isset($_POST['expiry'])) {
		// Set form expiry
		try {
			$expiryNew = new DateTime($_POST['expiry'], new DateTimeZone('Europe/Rome'));
			$db->setConfigValue('expiry', (string) $expiryNew->getTimestamp());
			$changed = true;
		} catch(Exception $e) {
			$error = $e->getMessage();
		}
	} elseif(isset($_POST['rolesReset'])) {
		// Unset unavailable roles
		$db->unsetConfigValue('rolesUnavailable');
		$changed = true;
	} elseif(isset($_POST['roles'])) {
		// Set available roles
		$rolesRule = implode('|', $_POST['roles']);
		$db->setConfigValue('rolesUnavailable', $rolesRule);
		$changed = true;
	} elseif(isset($_POST['notifyEmail'])) {
		if($_POST['notifyEmail'] === 'false') {
			$email = '0';
		} else {
			$email = '1';
		}
		$db->setConfigValue('notifyEmail', $email);
		$changed = true;
	}

	if($changed) {
		// This is a pattern: https://en.wikipedia.org/wiki/Post/Redirect/Get
		http_response_code(303);
		// $_SERVER['REQUEST_URI'] is already url encoded
		header('Location: ' . $_SERVER['REQUEST_URI']);
		exit;
	}
}

$expiry = $db->getConfigValue('expiry');

// Get the timestamp in correct format
if($expiry !== null) {
	/** @noinspection PhpUnhandledExceptionInspection */
	$expiry = (new DateTime('now', new DateTimeZone('Europe/Rome')))->setTimestamp($expiry);
}

echo $template->render('settings',
	[
		'myuser'           => $_SESSION['uid'],
		'myname'           => $_SESSION['cn'],
		'expiry'           => $expiry,
		'error'            => $error,
		'sendMail'         => (int) $db->getConfigValue('notifyEmail'),
		'rolesUnavailable' => $db->getConfigValue('rolesUnavailable')
	]);
