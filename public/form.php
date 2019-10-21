<?php

namespace WEEEOpen\WEEEHire;

use Exception;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

$template = Template::create();

$db = new Database();

$expiry = $db->getConfigValue('expiry');

// Get from DB -> if "unixtime.now >= expiry date" then candidate_close : else show the form
if($expiry !== null && time() >= $expiry) {
	echo $template->render('candidate_close');
	exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
	$checkboxes = [
		'mandatorycheckbox_0',
		'mandatorycheckbox_1',
	];
	foreach($checkboxes as $attr) {
		if(!isset($_POST[$attr]) || $_POST[$attr] !== 'true') {
			http_response_code(400);
			echo $template->render('form', ['error' => 'consent']);
			exit;
		}
	}

	$attrs = [
		'name',
		'surname',
		'degreecourse',
		'year',
		'matricola',
		'area',
		'letter'
	];
	$user = new User();
	foreach($attrs as $attr) {
		if(isset($_POST[$attr])) {
			$user->$attr = $_POST[$attr];
		} else {
			http_response_code(400);
			echo $template->render('form', ['error' => 'form']);
			exit;
		}
	}
	$user->submitted = time();
	$user->matricola = strtolower($user->matricola);
	if(preg_match('#^(s|d)\d+$#', $user->matricola) !== 1) {
		http_response_code(400);
		echo $template->render('form', ['error' => 'form']);
		exit;
	}

	try {
		list($id, $token) = $db->addUser($user);
	} catch(DuplicateUserException $e) {
		http_response_code(400);
		echo $template->render('form', ['error' => 'duplicate']);
		exit;
	} catch(DatabaseException $e) {
		http_response_code(500);
		echo $template->render('form', ['error' => 'database']);
		exit;
	} catch(Exception $e) {
		http_response_code(500);
		echo $template->render('form', ['error' => 'wtf']);
		exit;
	}
	http_response_code(303);
	$query = http_build_query(['id' => $id, 'token' => $token]);
	header("Location: /status.php?$query");
	Email::sendMail(Utils::politoMail($user->matricola), $template->render('confirm_email', ['subject' => true]), $template->render('confirm_email', ['link' => WEEEHIRE_SELF_LINK . "/status.php?$query", 'subject' => false]));
	exit;
}

echo $template->render('form');
