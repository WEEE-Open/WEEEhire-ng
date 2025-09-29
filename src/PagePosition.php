<?php

namespace WEEEOpen\WEEEHire;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;

class PagePosition implements RequestHandlerInterface
{
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$template = Template::create($request->getUri());

		Utils::requireAdmin();

		if (!isset($_GET['id'])) {
			return new RedirectResponse('settings.php', 303);
		}

		$db = new Database();

		$position = $db->getPosition($_GET['id']);

		if ($position == false) {
			return new RedirectResponse('settings.php', 303);
		}

		$nameTranslations = [];
		$summaryTranslations = [];
		$descriptionTranslations = [];

		foreach (Template::SUPPORTED_LOCALES as $locale) {
			$nameTranslations[$locale] = $db->getTranslation("position." . $_GET['id'] . ".name", $locale);
			if ($nameTranslations[$locale] !== false) {
				$nameTranslations[$locale] = $nameTranslations[$locale]['value'];
			}
			$summaryTranslations[$locale] = $db->getTranslation("position." . $_GET['id'] . ".summary", $locale);
			if ($summaryTranslations[$locale] !== false) {
				$summaryTranslations[$locale] = $summaryTranslations[$locale]['value'];
			}
			$descriptionTranslations[$locale] = $db->getTranslation("position." . $_GET['id'] . ".description", $locale);
			if ($descriptionTranslations[$locale] !== false) {
				$descriptionTranslations[$locale] = $descriptionTranslations[$locale]['value'];
			}
		}

		$error = null;

		if ($request->getMethod() === 'POST') {
			$POST = $request->getParsedBody();
			// Form submission
			$changed = false;
			if (isset($POST['delete'])) {
				// Delete position
				$db->deletePosition($_GET['id']);
				return new RedirectResponse('settings.php', 303);
			}
			if (isset($POST['id'])) {
				// Update the id of the position
				// Make sure the id is url safe (aka keep only lowercase letters and dashes) and replace spaces with dashes
				$newId = preg_replace('/[^a-z-]/', '', preg_replace('/ /', '-', strtolower($POST['id'])));
				if ($newId !== $_GET['id']) {
					$existingPosition = $db->getPosition($newId);
					if ($existingPosition !== false) {
						$error = 'Position with id ' . $newId . ' already exists';
					} else {
						$db->updatePositionId($_GET['id'], $newId);
						return new RedirectResponse('position.php?id=' . $newId, 303);
					}
				}
			}
			if (isset($POST['index'])) {
				// Update the index of the position
				$db->updatePositionIndex($_GET['id'], $POST['index']);
				$changed = true;
			}
			if (isset($POST['translation'])) {
				// Figure out which translation has been changed and update it
				foreach (Template::SUPPORTED_LOCALES as $locale) {
					if (isset($POST['name-' . $locale]) && $POST['name-' . $locale] !== $nameTranslations[$locale]) {
						$db->updateTranslation("position." . $_GET['id'] . ".name", $locale, $POST['name-' . $locale]);
					}
					if (isset($POST['summary-' . $locale]) && $POST['summary-' . $locale] !== $summaryTranslations[$locale]) {
						$db->updateTranslation("position." . $_GET['id'] . ".summary", $locale, $POST['summary-' . $locale]);
					}
					if (isset($POST['description-' . $locale]) && $POST['description-' . $locale] !== $descriptionTranslations[$locale]) {
						$db->updateTranslation("position." . $_GET['id'] . ".description", $locale, $POST['description-' . $locale]);
					}
				}
				$changed = true;
			}

			if ($changed) {
				// This is a pattern: https://en.wikipedia.org/wiki/Post/Redirect/Get
				// $_SERVER['REQUEST_URI'] is already url encoded
				return new RedirectResponse($_SERVER['REQUEST_URI'], 303);
			}
		}

		return new HtmlResponse(
			$template->render(
				'position',
				[
				'myuser'           => $_SESSION['uid'],
				'myname'           => $_SESSION['cn'],
				'position' => $position,
				'nameTranslations' => $nameTranslations,
				'summaryTranslations' => $summaryTranslations,
				'descriptionTranslations' => $descriptionTranslations,
				'error' => $error
				]
			)
		);
	}
}
