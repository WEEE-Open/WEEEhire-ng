<?php

namespace WEEEOpen\WEEEHire;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

$template = Template::create();
$db = new Database();

if($_GET['id']) {
	$id = (int) $_GET['id'];
	$user = $db->getUser($id);
	echo $template->render('candidate', ['user' => $user, 'edit' => isset($_GET['edit'])]);
	exit;
} else {
	$users = $db->getAllUsersForTable();
	echo $template->render('candidates', ['users' => $users]);
}
