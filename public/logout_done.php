<?php

namespace WEEEOpen\WEEEHire;

use Laminas\Diactoros\Uri;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Logout completed, show a page telling users just that

$locale = Template::getNormalizedLocaleOrDefault($_GET['l'] ?? '');

$template = Template::createWithoutSession($locale, new Uri($_SERVER['REQUEST_URI']));
echo $template->render('logout');
