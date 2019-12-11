<?php

namespace WEEEOpen\WEEEHire;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$template = Template::create();
echo $template->render('privacy');
