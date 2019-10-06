<?php

namespace WEEEOpen\WEEEHire;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

$template = Template::create();
$db = new Database();
$ldap = new Ldap(WEEEHIRE_LDAP_URL, WEEEHIRE_LDAP_BIND_DN, WEEEHIRE_LDAP_PASSWORD, WEEEHIRE_LDAP_USERS_DN,
	WEEEHIRE_LDAP_INVITES_DN, WEEEHIRE_LDAP_STARTTLS);

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
				} elseif($user->status === false && isset($_POST['publishnow'])) {
					$db->setPublished($id, true);
					$db->saveNotes($id, $notes);
					$changed = true;
				}
			} else {
				// Published, and...
				if($user->status === true && isset($_POST['invite'])) {
					$link = $ldap->createInvite($user);
					$db->setInviteLink($id, $link);
					$changed = true;
				} elseif($user->status === true && isset($_POST['publishnow']) && !$user->emailed) {
					$email = $_POST['email'] ?? '';
					$subject = $_POST['subject'] ?? '';
					$recruiter = $_POST['recruiter'] ?? '';
					if(strlen($email) <= 0) {
						http_response_code(400);
						echo $template->render('error', ['message' => 'Write an email']);
						exit;
					}
					if(strlen($subject) <= 0) {
						http_response_code(400);
						echo $template->render('error', ['message' => 'Write a subject line']);
						exit;
					}
					if(strlen($recruiter) <= 0 || strpos($recruiter, '|') === false) {
						http_response_code(400);
						echo $template->render('error', ['message' => 'Select a recruiter']);
						exit;
					}
					$recruiter = explode('|', $recruiter, 2);
					$db->setRecruiter($id, $recruiter[1], $recruiter[0]);

					// TODO: send email

					$db->setEmailed($id, true);
					$db->setPublished($id, true);
					$changed = true;
				}
			}
		}
		if($changed) {
			http_response_code(303);
			// $_SERVER['REQUEST_URI'] is already url encoded
			header("Location: ${_SERVER['REQUEST_URI']}");
			exit;
		}
	}

	echo $template->render('candidate', ['user' => $user, 'edit' => isset($_GET['edit']), 'recruiters' => $ldap->getRecruiters()]);
	exit;
} else {
	$users = $db->getAllUsersForTable();
	echo $template->render('candidates', ['users' => $users]);
}
