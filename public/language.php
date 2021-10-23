<?php

// Set language via cookies, then redirect to the previous page

use WEEEOpen\WEEEHire\Template;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

if (!isset($_GET['l'])) {
	// No language parameter
	http_response_code(400);
	echo 'Invalid l parameter';
	exit(0);
}
$locale = Template::getNormalizedLocale($_GET['l']);
if ($locale) {
	session_start();
	$_SESSION['locale'] = $locale;
	session_write_close();
} else {
	http_response_code(400);
	echo 'Invalid l parameter';
	exit(0);
}

// Now redirect
http_response_code(303);
if (isset($_GET['from'])) {
	// Location parameter in the GET query
	$url = rawurldecode($_GET['from']);
	if (substr($url, 0, 1) === '/') {
		header("Location: $url");
		exit(0);
	}
}
// No location parameter
header('Location: /');
