<?php


namespace WEEEOpen\WEEEHire;


// Same as the good ol' functions.php...
class Utils {
	public static function appendQueryParametersToRelativeUrl(string $url, array $parameters): string {
		$query = parse_url($url, PHP_URL_QUERY);
		if($query === null) {
			$querySplit = [];
		} else {
			// Remove query parameters from URL
			$url = str_replace('?' . $query, '', $url);
			// Split them
			parse_str($query, $querySplit);
		}
		$parameters = array_merge($querySplit, $parameters);
		$newQuery = http_build_query($parameters);
		return "$url?$newQuery";
	}
}
