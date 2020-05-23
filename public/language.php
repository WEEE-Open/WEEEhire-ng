<?php

// Set language via cookies, then redirect to the previous page

if(!isset($_GET['l'])) {
	// No language parameter
	http_response_code(400);
	echo 'Invalid l parameter';
	exit(0);
}

switch($_GET['l']) {
	// Set a language
	case 'it-it':
	case 'en-us':
		session_start();
		$_SESSION['locale'] = $_GET['l'];
		session_write_close();
		break;
	default:
		http_response_code(400);
		echo 'Invalid l parameter';
		exit(0);
}

// Now redirect
http_response_code(303);
if(isset($_GET['from'])) {
	// Location parameter in the GET query
	$url = rawurldecode($_GET['from']);
	if(substr($url, 0, 1) === '/') {
		header("Location: $url");
		exit(0);
	}
}
// No location parameter
header('Location: /');
