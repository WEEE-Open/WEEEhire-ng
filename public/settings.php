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
    $id = (int)$_GET['id'];
    $user = $db->getUser($id);

    if ($user === null) {
        http_response_code(404);
        echo $template->render('error', ['message' => 'Invalid user ID']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scadenzaNew = strtotime($_POST['scadenzaDate']);
    $db->setScadenzaCandidature($scadenzaNew);
}

//TODO: Controllare tutti i controlli di accesso lato admin da candidates per esempio (chiedi a Ludo)
$unixOld = $db->getCandidature();
$scadenzaOld = new \DateTime("@$unixOld");

echo $template->render('settings', ['users' => $users, 'myuser' => $_SESSION['uid'], 'myname' => $_SESSION['cn'], 'scadenzaOld' => $scadenzaOld]);