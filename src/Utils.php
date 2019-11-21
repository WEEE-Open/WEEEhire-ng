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
		if(!$enabled && !TEST_MODE) {
			error_log('APCu is not enabled, please enable it, I beg you!');
		}

		return $enabled;
	}

	/**
	 * Users must be admins beyond this point.
	 * If they aren't, this method will redirect them to the authentication page.
	 * If TEST_MODE is defined, the check is bypassed and example data is returned, instead.
	 */
	public static function requireAdmin() {
		if(defined('TEST_MODE') && TEST_MODE) {
			error_log('Test mode, bypassing authentication');
			if(session_status() === PHP_SESSION_NONE) {
				session_start();
			}
			$_SESSION['uid'] = 'test.test';
			$_SESSION['cn'] = 'Test Administrator';
			$_SESSION['groups'] = ['Admin', 'Foo', 'Bar'];
			$_SESSION['isAdmin'] = true;
		} else {
			if(!Utils::sessionValid()) {
				if(session_status() === PHP_SESSION_NONE) {
					// We need to write
					session_start();
				}
				$_SESSION['previousPage'] = $_SERVER['REQUEST_URI'];
				$_SESSION['needsAuth'] = true;
				http_response_code(303);
				header('Location: /auth.php');
				exit;
			}
		}
	}
}
