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
			// Just read the contents
			session_write_close();
		}
		if(isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] && isset($_SESSION['expires'])) {
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
		return $valid;
	}

	public static function hasApcu() {
		// Yes one is apcu and the other apc...
		$enabled = extension_loaded('apcu') && boolval(ini_get('apc.enabled'));
		if(!$enabled) {
			error_log('APCu is not enabled, please enable it, I beg you!');
		}
		return $enabled;
	}
}
