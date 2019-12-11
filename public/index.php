<?php

namespace WEEEOpen\WEEEHire;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

$template = Template::create();

$db = new Database();

$expiry = $db->getConfigValue('expiry');

// Get from DB -> if "unixtime.now >= expiry date" then candidate_close : else show the form
if($expiry !== null && time() >= $expiry) {
	echo $template->render('candidate_close');
} else {
	echo $template->render('index', ['expiry' => $expiry]);
}
