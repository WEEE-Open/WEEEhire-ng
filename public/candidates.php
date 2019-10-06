<?php

namespace WEEEOpen\WEEEHire;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

$template = Template::create();
$db = new Database();

if($_GET['id']) {
	$id = (int) $_GET['id'];
	$user = $db->getUser($id);

	if($user === null) {
		http_response_code(404);
		echo $template->render('error', ['message' => 'Invalid user ID']);
		exit;
	}

	if($_SERVER['REQUEST_METHOD'] === 'POST') {
		$changed = false;
		$notes = $_POST['notes'] ?? '';
		if(isset($_POST['save'])) {
			$db->saveNotes($id, $notes);
			$changed = true;
		} else {
			if(!$user->published) {
				if(isset($_POST['approve'])) {
					$db->setStatus($id, true);
					$db->saveNotes($id, $notes);
					$changed = true;
				} elseif(isset($_POST['reject'])) {
					$db->setStatus($id, false);
					$db->saveNotes($id, $notes);
					$changed = true;
				} elseif($user->status !== null && isset($_POST['limbo'])) {
					$db->setStatus($id, null);
					$db->saveNotes($id, $notes);
					$changed = true;
				} elseif($user->status !== null && isset($_POST['publishnow'])) {
					$db->setPublished($id, true);
					$db->saveNotes($id, $notes);
					$changed = true;
				}
			}
		}
		if($changed) {
			http_response_code(303);
			// $_SERVER['REQUEST_URI'] is already url encoded
			header("Location: ${_SERVER['REQUEST_URI']}");
		}
	}

	echo $template->render('candidate', ['user' => $user, 'edit' => isset($_GET['edit'])]);
	exit;
} else {
	$users = $db->getAllUsersForTable();
	echo $template->render('candidates', ['users' => $users]);
}
