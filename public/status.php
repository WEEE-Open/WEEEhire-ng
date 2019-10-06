<?php

namespace WEEEOpen\WEEEHire;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$user = new User();
$user->submitted = 1424143211;
$user->published = false;
$user->status = null;
$user->recruiter = 'That Dude';
$user->recruitertg = 'test';

if(isset($_GET['download'])) {
	$attributes = (array) $user;
	$secrets = ['status', 'published', 'recruiter', 'recruitertg'];
	$attributes = array_diff_key($attributes, array_combine($secrets, $secrets));

	header('Content-Type: application/json');
	header('Content-Transfer-Encoding: Binary');
	header('Content-Description: File Transfer');
	header("Content-Disposition: attachment; filename=weeehire.json");
	echo json_encode($attributes, JSON_PRETTY_PRINT);
	exit;
} elseif(isset($_GET['delete'])) {
	// TODO: this
}

$template = Template::create();
echo $template->render('status', ['user' => $user]);
