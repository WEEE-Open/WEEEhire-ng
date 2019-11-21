<?php

namespace WEEEOpen\WEEEHire;


use DateTime;
use DateTimeZone;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

$template = Template::create();

Utils::requireAdmin();

$db = new Database();
if(isset($_GET['id'])) {
	$id = (int) $_GET['id'];
	$user = $db->getUser($id);
	$interview = $db->getInterview($id);

	if(!$user->status || !$user->published) {
		http_response_code(400);
		echo $template->render('error', [
			'message' => sprintf(__('È necessario approvare e pubblicare la candidatura di %s per accedere a questa pagina. Torna alla <a href="/candidates.php?id=%d">pagina di gestione candidato</a>.'),
				$user->name, $user->id)
		]);
		exit;
	}

	$ldap = new Ldap(WEEEHIRE_LDAP_URL, WEEEHIRE_LDAP_BIND_DN, WEEEHIRE_LDAP_PASSWORD, WEEEHIRE_LDAP_USERS_DN,
		WEEEHIRE_LDAP_INVITES_DN, WEEEHIRE_LDAP_STARTTLS);

	if($user === null) {
		http_response_code(404);
		echo $template->render('error', ['message' => 'Invalid user ID']);
		exit;
	}

	if($_SERVER['REQUEST_METHOD'] === 'POST') {
		$changed = false;

		if(isset($_POST['edit'])) {
			// If all data is present, this method will update $user so it only has to be stored in the database
			if($user->fromPost($_POST)) {
				// Store it
				$db->updateUser($user);
				$changed = true;
			}
		} elseif(isset($_POST['invite'])) {
			$link = $ldap->createInvite($user);
			$db->setInviteLink($id, $link);
			$changed = true;
		} elseif(isset($_POST['setinterview']) && isset($_POST['when1']) && isset($_POST['when2']) && isset($_POST['recruiter'])) {
			$recruiter = $_POST['recruiter'];
			if(strlen($recruiter) <= 0 || strpos($recruiter, '|') === false) {
				http_response_code(400);
				echo $template->render('error', ['message' => 'Select a recruiter']);
				exit;
			}
			$recruiter = explode('|', $recruiter, 2);
			$when = DateTime::createFromFormat("Y-m-d H:i", $_POST['when1'] . ' ' . $_POST['when2'],
				new DateTimeZone('Europe/Rome'));
			$db->setInterviewSchedule($interview->id, $recruiter[1], $recruiter[0], $when);
			$changed = true;
		} elseif(isset($_POST['unsetinterview'])) {
			$db->setInterviewSchedule($interview->id, null, null, null);
			$changed = true;
		} elseif(isset($_POST['approve']) || isset($_POST['reject']) || isset($_POST['save']) || isset($_POST['limbo']) || isset($_POST['pushHold']) || isset($_POST['popHold'])) {
			$db->setInterviewData($interview->id, $_POST['questions'] ?? null, $_POST['answers'] ?? null);
			if(isset($_POST['approve'])) {
				$db->setInterviewStatus($interview->id, true);
			} elseif(isset($_POST['reject'])) {
				$db->setInterviewStatus($interview->id, false);
			} elseif(isset($_POST['limbo'])) {
				$db->setInterviewStatus($interview->id, null);
			} elseif(isset($_POST['pushHold'])) {
				$db->setHold($interview->id, 1);
			} elseif(isset($_POST['popHold'])) {
				$db->setHold($interview->id, 0);
			}
			$changed = true;
		}

		if($changed) {
			http_response_code(303);
			$url = Utils::appendQueryParametersToRelativeUrl($_SERVER['REQUEST_URI'], ['edit' => null]);
			header("Location: $url");
			exit;
		}
	}

	echo $template->render('interview', [
		'user'       => $user,
		'interview'  => $interview,
		'edit'       => isset($_GET['edit']),
		'recruiters' => $ldap->getRecruiters()
	]);
	exit;
} else {
	//	if($_SERVER['REQUEST_METHOD'] === 'POST') {
	//
	//	}

	if(isset($_GET['byrecruiter'])) {
		$interviews = $db->getAllAssignedInterviewsForTable();
		echo $template->render('interviewsbyrecruiter',
			['interviews' => $interviews, 'myuser' => $_SESSION['uid'], 'myname' => $_SESSION['cn']]);
		exit;
	} else {
		$interviews = $db->getAllInterviewsForTable();
		echo $template->render('interviews',
			['interviews' => $interviews, 'myuser' => $_SESSION['uid'], 'myname' => $_SESSION['cn']]);
		exit;
	}
}
