<?php

namespace WEEEOpen\WEEEHire;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

$db = new Database();
$candidature_eta = 0;
$today_time = time();

try {
    $candidature_eta = $db->getCandidature();
} catch(DatabaseException $e) {
    http_response_code(500);
    echo $template->render('form', ['error' => 'database']);
    exit;
} catch(Exception $e) {
    http_response_code(500);
    echo $template->render('form', ['error' => 'wtf']);
    exit;
}

$template = Template::create();
// Get da DB -> se unixtime.now > candidature allora candidate_close : se no form
if( $candidature_eta <= $today_time ){
    echo $template->render('candidate_close');
}else{
    echo $template->render('index',['candidatureEta' => $candidature_eta]);
}
