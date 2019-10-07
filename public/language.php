<?php

if(!isset($_GET['l'])) {
	http_response_code(400);
	echo 'Invalid l parameter';
	exit(0);
}

switch($_GET['l']) {
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

http_response_code(303);
if(isset($_GET['from'])) {
	$url = rawurldecode($_GET['from']);
	if(substr($url, 0, 1) === '/') {
		header("Location: $url");
		exit(0);
	}
}
header('Location: /');

