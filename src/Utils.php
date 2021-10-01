<?php


namespace WEEEOpen\WEEEHire;


// Same as the good ol' functions.php...
use Psr\Http\Message\UriInterface;

class Utils {
	/**
	 * Add or remove query parameters from a URL
	 *
	 * @param UriInterface $uri Current URI, may contain query parameters
	 * @param array $parameters Parameters to modify. Key is name, value is the value or null to delete it. Other parameters left untouched.
	 *
	 * @return string resulting URL
	 */
	public static function appendQueryParametersToRelativeUrl(UriInterface $uri, array $parameters): string {
		parse_str($uri->getQuery(), $query);
		$query = $query ?? [];
		foreach($parameters as $param => $value) {
			if($value === null) {
				unset($query[$param]);
			} else {
				$query[$param] = $value;
			}
		}
		$newQuery = http_build_query($query);
		return $uri->withQuery($newQuery);
	}

	/**
	 * Obtain the polito email address from an ID number (matricola).
	 * They are different if the ID number starts with "s" or "d".
	 *
	 * @param string $matricola ID number
	 *
	 * @return string Email address
	 */
	public static function politoMail(string $matricola): string {
		$first = strtolower(substr($matricola, 0, 1));
		if($first === 'd') {
			return "$matricola@polito.it";
		} else {
			return "$matricola@studenti.polito.it";
		}
	}

	public static function normalizeCase(string $name): string {
		// This is insensitive to other cultures where names are not capitalized,
		// I'm sure, but it looks prettier...
		if($name === strtoupper($name) || $name === strtolower($name)) {
			return ucwords(strtolower($name), ' \'-');
		}
		return $name;
	}

	/**
	 * Is the current session valid, according to the ID token?
	 *
	 * @return bool valid or not
	 */
	private static function sessionValid(): bool {
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

	/**
	 * Is APCu installed, configured and enabled?
	 *
	 * @return bool Available or not
	 */
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
	 * If they aren't, this method will redirect them to the authentication page and terminates the entire PHP script.
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


	public static function endsWith(string $haystack, string $needle): bool {
		return substr($haystack, -strlen($needle)) === $needle;
	}
}
