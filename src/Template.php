<?php

namespace WEEEOpen\WEEEHire;

use League\Plates\Engine;
use Negotiation\LanguageNegotiator;
use PhpMyAdmin\MoTranslator\Loader;

class Template {
	const allowedLocales = ['en-us', 'it-it'];

	public static function createWithoutSession(string $locale): Engine {
		Loader::loadFunctions();

		_setlocale(LC_MESSAGES, $locale);
		_textdomain('messages');
		_bindtextdomain('messages',
			__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'locale');
		//_bind_textdomain_codeset('messages', 'UTF-8');

		$engine = new Engine('..' . DIRECTORY_SEPARATOR . 'templates');

		return $engine;
	}

	public static function create(): Engine {
		Loader::loadFunctions();

		_setlocale(LC_MESSAGES, self::getLocale());
		_textdomain('messages');
		_bindtextdomain('messages',
			__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'locale');
		//_bind_textdomain_codeset('messages', 'UTF-8');

		$engine = new Engine('..' . DIRECTORY_SEPARATOR . 'templates');

		return $engine;
	}

	private static function getLocale(): string {
		// Must be here, or $_SESSION is not available
		session_start();
		if(isset($_SESSION['locale'])) {
			return $_SESSION['locale'];
		}

		$locale = self::getLocaleNotCached();
		$_SESSION['locale'] = $locale;
		session_write_close();

		return $locale;
	}


	private static function getLocaleNotCached(): string {
		if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			return 'en-us';
		}

		$negotiator = new LanguageNegotiator();

		$priorities = self::allowedLocales;

		$bestLanguage = $negotiator->getBest($_SERVER['HTTP_ACCEPT_LANGUAGE'], $priorities);

		/** @noinspection PhpUndefinedMethodInspection */
		return $bestLanguage->getType();
	}
}
