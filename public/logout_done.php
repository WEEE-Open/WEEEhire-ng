<?php

namespace WEEEOpen\WEEEHire;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if(isset($_GET['l']) && in_array($_GET['l'], Template::allowedLocales)) {
	$locale = $_GET['l'];
} else {
	$locale = Template::allowedLocales[0];
}

$template = Template::createWithoutSession($locale);
echo $template->render('logout');
