<?php

namespace WEEEOpen\WEEEHire;

use Jumbojett\OpenIDConnectClient;
use Jumbojett\OpenIDConnectClientException;
use Laminas\Diactoros\Uri;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

$template = Template::create(new Uri($_SERVER['REQUEST_URI']));

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (!isset($_SESSION['needsAuth']) || !$_SESSION['needsAuth']) {
	http_response_code(303);
	header('Location: /candidates.php');
	exit;
}

try {
	$oidc = new OpenIDConnectClient(WEEEHIRE_OIDC_ISSUER, WEEEHIRE_OIDC_CLIENT_KEY, WEEEHIRE_OIDC_CLIENT_SECRET);
	$oidc->setRedirectURL(WEEEHIRE_SELF_LINK . '/auth.php');
	$oidc->addScope(['openid', 'profile']);
	$oidc->authenticate();

	// For Keycloak go to clients > weeehire > mappers > add builtin > groups
	$uid = $oidc->getVerifiedClaims('preferred_username');
	$cn = $oidc->getVerifiedClaims('name');
	$groups = $oidc->getVerifiedClaims('groups');
	$exp = $oidc->getVerifiedClaims('exp');
	$refresh_token = $oidc->getRefreshToken();
	$id_token = $oidc->getIdToken();
	$_SESSION['uid'] = $uid;
	$_SESSION['cn'] = $cn;
	$_SESSION['groups'] = $groups;
	$_SESSION['expires'] = $exp;
	$_SESSION['refresh_token'] = $refresh_token;
	$_SESSION['id_token'] = $id_token;
	$_SESSION['isAdmin'] = true;
	$authorized = false;
	foreach (WEEEHIRE_OIDC_ALLOWED_GROUPS as $group) {
		if (in_array($group, $groups, true)) {
			$authorized = true;
			break;
		}
	}

	if ($authorized) {
		$_SESSION['isAdmin'] = true;
		http_response_code(303);
		if (isset($_SESSION['previousPage'])) {
			header('Location: ' . $_SESSION['previousPage']);
		} else {
			header('Location: /candidates.php');
		}
	} else {
		$_SESSION['isAdmin'] = false;
		http_response_code(403);
		echo $template->render('error', ['message' => 'You are not authorized to view this page.']);
	}
} catch (OpenIDConnectClientException $e) {
	error_log($e);
	http_response_code(500);
	echo $template->render('error', ['message' => 'Authentication failed']);
} finally {
	unset($_SESSION['needsAuth']);
	unset($_SESSION['previousPage']);
}
