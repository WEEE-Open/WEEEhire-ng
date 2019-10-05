<?php

namespace WEEEOpen\WEEEHire;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$template = Template::create();
echo $template->render('form');
