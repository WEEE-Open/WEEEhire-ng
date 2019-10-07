<?php

namespace WEEEOpen\WEEEHire;

use Jumbojett\OpenIDConnectClient;
use Jumbojett\OpenIDConnectClientException;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

$template = Template::create();

if(defined('TEST_MODE') && TEST_MODE) {
	error_log('Test mode, bypassing authentication');
} else {
	try {
		if(!Utils::sessionValid()) {
			$oidc = new OpenIDConnectClient(WEEEHIRE_OIDC_ISSUER, WEEEHIRE_OIDC_CLIENT_KEY, WEEEHIRE_OIDC_CLIENT_SECRET);
			$oidc->setRedirectURL(WEEEHIRE_SELF_LINK . '/candidates.php');
			$oidc->addScope('openid');
			$oidc->addScope('profile');
			$oidc->addScope('roles');
			$oidc->authenticate();
			var_dump($oidc);
			$uid = $oidc->getVerifiedClaims('preferred_username');
			$cn = $oidc->getVerifiedClaims('name');
			$groups = $oidc->requestUserInfo('groups');  // TODO: can we do this with getVerifiedClaims?
			$exp = $oidc->getVerifiedClaims('exp');
			$refresh_token = $oidc->getRefreshToken();
			$id_token = $oidc->getIdToken();

			if(session_status() === PHP_SESSION_NONE) {
				session_start();
			}
			$_SESSION['uid'] = $uid;
			$_SESSION['cn'] = $cn;
			$_SESSION['groups'] = $groups;
			$_SESSION['expires'] = $exp;
			$_SESSION['refresh_token'] = $refresh_token;
			$_SESSION['id_token'] = $id_token;
			$authorized = false;
			foreach(WEEEHIRE_OIDC_ALLOWED_GROUPS as $group) {
				if(in_array($group, $groups)) {
					$authorized = true;
					break;
				}
			}

			if($authorized) {
				http_response_code(303);
				if(isset($_SESSION['previousPage'])) {
					header('Location: ' . $_SESSION['previousPage']);
					unset($_SESSION['previousPage']);
				} else {
					header('Location: /candidate.php');
				}
				exit;
			} else {
				session_destroy();
				http_response_code(403);
				echo $template->render('error', ['message' => 'You are not authorized to view this page.']);
				exit;
			}
		}
	} catch(OpenIDConnectClientException $e) {
		session_destroy();
		error_log($e);
		http_response_code(500);
		echo $template->render('error', ['message' => 'Authentication failed']);
		exit;
	}
}


$db = new Database();
$ldap = new Ldap(WEEEHIRE_LDAP_URL, WEEEHIRE_LDAP_BIND_DN, WEEEHIRE_LDAP_PASSWORD, WEEEHIRE_LDAP_USERS_DN,
	WEEEHIRE_LDAP_INVITES_DN, WEEEHIRE_LDAP_STARTTLS);

if(isset($_GET['id'])) {
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

		if(isset($_POST['name']) && isset($_POST['surname']) && isset($_POST['degreecourse']) && isset($_POST['year']) && isset($_POST['matricola']) && isset($_POST['area']) && isset($_POST['letter'])) {
			foreach(['name', 'surname', 'degreecourse', 'year', 'matricola', 'area', 'letter'] as $attr) {
				$user->$attr = $_POST[$attr];
			}
			$db->updateUser($user);
			$changed = true;
		} elseif(isset($_POST['save'])) {
			$db->saveNotes($id, $notes);
			$changed = true;
		} else {
			if(!$user->published) {
				// Not published, and...
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
			} else {
				// Published, and...
				if($user->status === true && isset($_POST['invite'])) {
					$link = $ldap->createInvite($user);
					$db->setInviteLink($id, $link);
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
