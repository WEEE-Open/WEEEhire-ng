<?php

namespace WEEEOpen\WEEEHire;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Page for candidates to see their own status (approved/rejected)

$template = Template::create();

if(!$_GET['id'] || !$_GET['token']) {
	http_response_code(400);
	echo $template->render('error', ['message' => 'Missing id or token']);
	exit;
}

$db = new Database();

try {
	$id = (int) $_GET['id'];
	if($db->validateToken($id, $_GET['token'])) {
		$user = $db->getUser($id);
	} else {
		http_response_code(404);
		echo $template->render('error', ['message' => 'Invalid id or token']);
		exit;
	}
} catch(DatabaseException $e) {
	http_response_code(500);
	echo $template->render('error', ['message' => 'Database error']);
	exit;
}

if(isset($_GET['download'])) {
	// GDPR data download button
	$attributes = (array) $user;
	$downloadable = [
		'name',
		'surname',
		'degreecourse',
		'year',
		'matricola',
		'area',
		'letter'
	];
	// Filter out other attributes
	$attributes = array_intersect_key($attributes, array_combine($downloadable, $downloadable));

	header('Content-Type: application/json');
	header('Content-Transfer-Encoding: Binary');
	header('Content-Description: File Transfer');
	header("Content-Disposition: attachment; filename=weeehire.json");
	echo json_encode($attributes, JSON_PRETTY_PRINT);
	exit;
} elseif(isset($_GET['delete'])) {
	// Delete button
	try {
		$db->deleteUser($id);
		http_response_code(303);
		header('Location: /');
		exit;
	} catch(DatabaseException $e) {
		http_response_code(500);
		echo $template->render('error', ['message' => 'Database error']);
		exit;
	}
}

echo $template->render('status', ['user' => $user]);
