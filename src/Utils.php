<?php


namespace WEEEOpen\WEEEHire;


// Same as the good ol' functions.php...
class Utils {
	public static function appendQueryParametersToRelativeUrl(string $url, array $parameters): string {
		$queryString = parse_url($url, PHP_URL_QUERY);
		if($queryString === null) {
			$query = [];
		} else {
			// Remove query parameters from URL
			$url = str_replace('?' . $queryString, '', $url);
			// Split them
			parse_str($queryString, $query);
		}
		foreach($parameters as $param => $value) {
			if($value === null) {
				unset($query[$param]);
			} else {
				$query[$param] = $value;
			}
		}
		$newQuery = http_build_query($query);
		return "$url?$newQuery";
	}

	public static function politoMail(string $matricola): string {
		$first = strtolower(substr($matricola, 0, 1));
		if($first === 'd') {
			return "$matricola@polito.it";
		} else {
			return "$matricola@studenti.polito.it";
		}
	}

	public static function sessionValid(): bool {
		$valid = true;
		if(session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		if(isset($_SESSION['expires'])) {
			if($_SESSION['expires'] <= time()) {
				// Grace time, only once
				if($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['expires'] <= time() + 600) {
					// Set to 0 to avoid loops with OIDC client
					$_SESSION['expires'] = 0;
					$valid = true;
				} else {
					$_SESSION['expires'] = 0;
					$valid = false;
				}
			}
		} else {
			$_SESSION['expires'] = 0;
			$valid = false;
		}
		if(!$valid && isset($_SESSION['previousPage'])) {
			// We're about to enter a series of redirects...
			$_SESSION['previousPage'] = $_SERVER['REQUEST_URI'];
		} else {
			unset($_SESSION['previousPage']);
		}
		session_write_close();
		return $valid;
	}
}
