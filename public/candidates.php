<?php

namespace WEEEOpen\WEEEHire;


require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

$template = Template::create();

Utils::requireAdmin();

$db = new Database();

if(isset($_GET['id'])) {
	// candidates.php?id=... => page with details on a single candidate

	$ldap = new Ldap(WEEEHIRE_LDAP_URL, WEEEHIRE_LDAP_BIND_DN, WEEEHIRE_LDAP_PASSWORD, WEEEHIRE_LDAP_USERS_DN,
		WEEEHIRE_LDAP_INVITES_DN, WEEEHIRE_LDAP_STARTTLS);
	$id = (int) $_GET['id'];
	$user = $db->getUser($id);

	if($user === null) {
		http_response_code(404);
		echo $template->render('error', ['message' => 'Invalid user ID']);
		exit;
	}

	// A form has been submitted
	if($_SERVER['REQUEST_METHOD'] === 'POST') {
		$changed = false;
		// Most buttons also update notes (so we can write "seems good" and press "approve")
		$notes = $_POST['notes'] ?? '';

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
		} elseif(isset($_POST['voteButton']) && isset($_POST['vote'])) {
			$db->setEvaluation($id, $_SESSION['uid'], $_SESSION['cn'], $_POST['vote']);
			$changed = true;
		} elseif(isset($_POST['unvote']) && isset($_POST["id_evaluation"])) {
			$db->removeEvaluation($_POST["id_evaluation"]);
			$changed = true;
		} else {
			if($user->published) {
				// Only for published evaluations
				if(isset($_POST['approvefromhold'])) {
					// Unpublish so we can choose a recruiter
					$db->setPublished($id, false);
					$db->setStatus($id, true, $_SESSION['cn'] ?? null);
					// Leave on hold (so the application cannot be deleted)
					//$db->setHold($id, false);
					// Should have already been false
					$db->setEmailed($id, false);
					$db->saveNotes($id, $notes);
					$changed = true;
				} elseif(isset($_POST['holdon'])) {
					$db->saveNotes($id, $notes);
					$db->setHold($id, true);
					$changed = true;
				} elseif(isset($_POST['holdoff'])) {
					$db->saveNotes($id, $notes);
					$db->setHold($id, false);
					$changed = true;
				}
			} else {
				// Only for not published evaluations
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
			// This is a pattern: https://en.wikipedia.org/wiki/Post/Redirect/Get
			http_response_code(303);
			// $_SERVER['REQUEST_URI'] is already url encoded
			$url = Utils::appendQueryParametersToRelativeUrl($_SERVER['REQUEST_URI'], ['edit' => null]);
			header("Location: $url");
			exit;
		}
	} // "if this is a POST request"

	// Render the page
	echo $template->render('candidate',
		[
			'user'        => $user,
			'edit'        => isset($_GET['edit']),  // candidates.php?id=123&edit, allows editing of personal data
			'recruiters'  => $ldap->getRecruiters(),
			'evaluations' => $db->getEvaluation($id),
			'uid'         => $_SESSION['uid'],
			'cn'          => $_SESSION['cn']
		]);
	exit;

} else {
	// no ?id=... parameter => render the page with a candidates list
	if($_SERVER['REQUEST_METHOD'] === 'POST') {
		// This is a form submission
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
