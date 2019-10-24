<?php

namespace WEEEOpen\WEEEHire;


require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

$template = Template::create();

if(defined('TEST_MODE') && TEST_MODE) {
	error_log('Test mode, bypassing authentication');
	if(session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	$_SESSION['uid'] = 'test.test';
	$_SESSION['cn'] = 'Test Administrator';
	$_SESSION['groups'] = ['Admin', 'Foo', 'Bar'];
	$_SESSION['isAdmin'] = true;
} else {
	if(!Utils::sessionValid()) {
		if(session_status() === PHP_SESSION_NONE) {
			// We need to write
			session_start();
		}
		$_SESSION['previousPage'] = $_SERVER['REQUEST_URI'];
		$_SESSION['needsAuth'] = true;
		http_response_code(303);
		header('Location: /auth.php');
		exit;
	}
}

$db = new Database();
if(isset($_GET['id'])) {
	$ldap = new Ldap(WEEEHIRE_LDAP_URL, WEEEHIRE_LDAP_BIND_DN, WEEEHIRE_LDAP_PASSWORD, WEEEHIRE_LDAP_USERS_DN,
		WEEEHIRE_LDAP_INVITES_DN, WEEEHIRE_LDAP_STARTTLS);
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

		// These HAVE to be in mutual exclusion, or you have to check $changed in "sequential" ifs

		if(isset($_POST['edit'])) {
			// If all data is present, this method will update $user so it only has to be stored in the database
			if($user->fromPost($_POST)) {
				// Store it
				$db->updateUser($user);
				$changed = true;
			}
		} elseif(isset($_POST['save'])) {
			$db->saveNotes($id, $notes);
			$changed = true;
		} else {
			if(!$user->published) {
				// Not published, and...
				if(isset($_POST['approve'])) {
					$db->setStatus($id, true, $_SESSION['cn'] ?? null);
					$db->saveNotes($id, $notes);
					$changed = true;
				} elseif(isset($_POST['reject'])) {
					$db->setStatus($id, false, $_SESSION['cn'] ?? null);
					$db->saveNotes($id, $notes);
					$changed = true;
				} elseif($user->status !== null && isset($_POST['limbo'])) {
					$db->setStatus($id, null, null);
					$db->saveNotes($id, $notes);
					$changed = true;
				} elseif($user->status === false && isset($_POST['publishnow'])) {
					$db->setPublished($id, true);
					$db->saveNotes($id, $notes);
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
					Email::sendMail(Utils::politoMail($user->matricola), $subject, $email);
					$db->setEmailed($id, true);
					$db->setPublished($id, true);
					$changed = true;
				}
			}
		}
		if($changed) {
			http_response_code(303);
			// $_SERVER['REQUEST_URI'] is already url encoded
			$url = Utils::appendQueryParametersToRelativeUrl($_SERVER['REQUEST_URI'], ['edit' => null]);
			header("Location: $url");
			exit;
		}
	}

	if (isset($_POST['voted'])) {
        $db->setEvaluation($id,$_SESSION['uid'],$_SESSION['cn'],$_POST['vote']);
        header ('Location: ' . $_SERVER['REQUEST_URI']);
        exit(); //TODO: Ho trovato questa come soluzione per evitare il submit del form via refreshing, vedi se trovi qualcosa di meglio
	}

	$evaluations = $db->getEvaluation($id);

	echo $template->render('candidate',
		['user' => $user, 'edit' => isset($_GET['edit']), 'recruiters' => $ldap->getRecruiters(), 'evaluations' => $evaluations, 'uid' => $_SESSION['uid']]);
	exit;
} else {
	if($_SERVER['REQUEST_METHOD'] === 'POST') {
		if(isset($_POST['publishallrejected'])) {
			$db->publishRejected();
			http_response_code(303);
			header('Location: /candidates.php');
			exit;
		} elseif(isset($_POST['deleteolderthan']) && isset($_POST['days'])) {
			$days = (int) $_POST['days'];
			if($days <= 0) {
				$days = 0;
			}
			$db->deleteOlderThan($days);
			http_response_code(303);
			header('Location: /candidates.php');
			exit;
		}
	}

	$users = $db->getAllUsersForTable();
	echo $template->render('candidates',
		['users' => $users, 'myuser' => $_SESSION['uid'], 'myname' => $_SESSION['cn']]);
}
