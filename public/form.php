<?php

namespace WEEEOpen\WEEEHire;

use Exception;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

$template = Template::create();

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
	$user->submitted = time();
	$user->matricola = strtolower($user->matricola);
	foreach($attrs as $attr) {
		if(isset($_POST[$attr])) {
			$user->$attr = $_POST[$attr];
		} else {
			http_response_code(400);
			echo $template->render('form', ['error' => 'form']);
			exit;
		}
	}

	$db = new Database();
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
}

echo $template->render('form');
