<?php

namespace WEEEOpen\WEEEHire;

use Zend\Diactoros\Uri;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Logout completed, show a page telling users just that

if(isset($_GET['l']) && in_array($_GET['l'], Template::allowedLocales)) {
	$locale = $_GET['l'];
} else {
	$locale = Template::allowedLocales[0];
}

$template = Template::createWithoutSession($locale, new Uri($_SERVER['REQUEST_URI']));
echo $template->render('logout');
