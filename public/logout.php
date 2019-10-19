<?php

namespace WEEEOpen\WEEEHire;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

use Jumbojett\OpenIDConnectClient;

session_start();

$oidc = new OpenIDConnectClient(WEEEHIRE_OIDC_ISSUER, WEEEHIRE_OIDC_CLIENT_KEY, WEEEHIRE_OIDC_CLIENT_SECRET);
//$oidc->setRedirectURL(WEEEHIRE_SELF_LINK . '/auth.php');
//$oidc->addScope(['openid', 'profile', 'roles']);
$token = $_SESSION['id_token'];
$locale = $_SESSION['locale'];
session_destroy();
$oidc->signOut($token, WEEEHIRE_SELF_LINK . '/logout_done.php?l=' . rawurlencode($locale));
exit;
