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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scadenzaNew = strtotime($_POST['scadenzaDate']);
    $db->setScadenzaCandidature($scadenzaNew);
}else if ($_GET['reset'] == 1) {
    $db->clearScadenza();
}

$scadenzaOld = $db->getCandidature();

echo $template->render('settings', ['users' => $users, 'myuser' => $_SESSION['uid'], 'myname' => $_SESSION['cn'], 'scadenzaOld' => $scadenzaOld]);