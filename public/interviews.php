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
	$id = (int) $_GET['id'];
	$user = $db->getUser($id);

	if(!$user->status || !$user->published) {
		http_response_code(400);
		echo $template->render('error', ['message' => sprintf(__('È necessario approvare e pubblicare la candidatura di %s per accedere a questa pagina. Torna alla <a href="/candidates.php?id=%d">pagina di gestione candidato</a>.'), $user->name, $user->id)]);
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
		}

		if($changed) {
			http_response_code(303);
			$url = Utils::appendQueryParametersToRelativeUrl($_SERVER['REQUEST_URI'], ['edit' => null]);
			header("Location: $url");
			exit;
		}
	}

	echo $template->render('interview', ['user' => $user, 'edit' => isset($_GET['edit']), 'recruiters' => $ldap->getRecruiters()]);
	exit;
} else {
	if($_SERVER['REQUEST_METHOD'] === 'POST') {

	}

	$users = $db->getAllUsersForTable();
	echo $template->render('interviews', ['users' => $users, 'myuser' => $_SESSION['uid'], 'myname' => $_SESSION['cn']]);
}
