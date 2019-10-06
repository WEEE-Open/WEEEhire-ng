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
}
