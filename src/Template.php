<?php

namespace WEEEOpen\WEEEHire;

use League\Plates\Engine;
use Negotiation\LanguageNegotiator;
use PhpMyAdmin\MoTranslator\Loader;
use Psr\Http\Message\UriInterface;

class Template
{
	private const SUPPORTED_LOCALES = ['en-US', 'it-IT'];


	public static function getNormalizedLocale(string $locale): ?string
	{
		if (in_array($locale, self::SUPPORTED_LOCALES)) {
			return $locale;
		}
		return null;
	}

	public static function getNormalizedLocaleOrDefault(string $locale): string
	{
		if (in_array($locale, self::SUPPORTED_LOCALES)) {
			return $locale;
		}
		return $locale[0];
	}

	/**
	 * Prepare the template engine and configure Motranslator if no session is available
	 *
	 * @param string $locale Template locale (language)
	 * @param UriInterface $uri Request URI, needed by almost every template
	 *
	 * @return Engine Plates template engine
	 */
	public static function createWithoutSession(string $locale, UriInterface $uri): Engine
	{
		Loader::loadFunctions();

		_setlocale(LC_MESSAGES, $locale);
		_textdomain('messages');
		_bindtextdomain(
			'messages',
			__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'locale'
		);
		//_bind_textdomain_codeset('messages', 'UTF-8');

		$engine = new Engine('..' . DIRECTORY_SEPARATOR . 'templates');
		$engine->addData(['globalRequestUri' => $uri]);

		return $engine;
	}

	/**
	 * Prepare the template engine and configure Motranslator
	 *
	 * @param UriInterface $uri Request URI, needed by almost every template
	 *
	 * @return Engine Plates template engine
	 */
	public static function create(UriInterface $uri): Engine
	{
		Loader::loadFunctions();

		_setlocale(LC_MESSAGES, self::getLocale());
		_textdomain('messages');
		_bindtextdomain(
			'messages',
			__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'locale'
		);
		//_bind_textdomain_codeset('messages', 'UTF-8');

		$engine = new Engine(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'templates');
		$engine->addData(['globalRequestUri' => $uri]);

		return $engine;
	}

	/**
	 * Get locale (language) from session.
	 * If none is set, negotiate and set it.
	 *
	 * @return string
	 */
	private static function getLocale(): string
	{
		// Must be here, or $_SESSION is not available
		session_start();
		if (isset($_SESSION['locale'])) {
			return $_SESSION['locale'];
		}

		$locale = self::getLocaleNotCached();
		$_SESSION['locale'] = $locale;
		session_write_close();

		return $locale;
	}

	/**
	 * Get locale (language) from request headers and negotiation.
	 *
	 * @return string Negotiated locale
	 */
	private static function getLocaleNotCached(): string
	{
		if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			return 'en-US';
		}

		$negotiator = new LanguageNegotiator();

		$priorities = self::SUPPORTED_LOCALES;

		$bestLanguage = $negotiator->getBest($_SERVER['HTTP_ACCEPT_LANGUAGE'], $priorities);

		// If the browser provides en-GB, LanguageNegotiator chooses NULL...
		if ($bestLanguage === null) {
			return 'en-US';
		}

		/** @noinspection PhpUndefinedMethodInspection */
		$lowercaseLocale = $bestLanguage->getType();
		if (strlen($lowercaseLocale) == 5 && $lowercaseLocale[2] == '-') {
			// gettext (or motranslator) expects the part after the dash to be uppercase
			return substr($lowercaseLocale, 0, 3) . strtoupper(substr($lowercaseLocale, 3));
		} else {
			return $lowercaseLocale;
		}
	}
}
