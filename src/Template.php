<?php

namespace WEEEOpen\WEEEHire;

use League\Plates\Engine;
use Negotiation\LanguageNegotiator;
use PhpMyAdmin\MoTranslator\Loader;

class Template {
	public static function create(): Engine {
		Loader::loadFunctions();

		_setlocale(LC_MESSAGES, self::getLocale());
		_textdomain('weeehire');
		_bindtextdomain('weeehire', __DIR__ . '/data/locale/');
		_bind_textdomain_codeset('weeehire', 'UTF-8');

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
			return 'en-US';
		}

		$negotiator = new LanguageNegotiator();

		$priorities = ['en-US', 'it-IT'];

		$bestLanguage = $negotiator->getBest($_SERVER['HTTP_ACCEPT_LANGUAGE'], $priorities);

		/** @noinspection PhpUndefinedMethodInspection */
		return $bestLanguage->getType();
	}
}
