<?php

namespace WEEEOpen\WEEEHire;

use Zend\Diactoros\Uri;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$template = Template::create(new Uri($_SERVER['REQUEST_URI']));
echo $template->render('privacy');
