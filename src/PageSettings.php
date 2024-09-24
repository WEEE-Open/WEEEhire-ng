<?php

namespace WEEEOpen\WEEEHire;

use DateTime;
use DateTimeZone;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;

class PageSettings implements RequestHandlerInterface
{
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$template = Template::create($request->getUri());

		Utils::requireAdmin();

		$db = new Database();

		$error = null;

		if ($request->getMethod() === 'POST') {
			$POST = $request->getParsedBody();
			// Form submission
			$changed = false;
			if (isset($POST['noexpiry'])) {
				// Unset form expiry
				$db->unsetConfigValue('expiry');
				$changed = true;
			} elseif (isset($POST['expiry'])) {
				// Set form expiry
				try {
					$expiryNew = new DateTime($POST['expiry'], new DateTimeZone('Europe/Rome'));
					$db->setConfigValue('expiry', (string) $expiryNew->getTimestamp());
					$changed = true;
				} catch (Exception $e) {
					$error = $e->getMessage();
				}
			} elseif (isset($POST['positions'])) {
				// Positions
				$positions = $db->getPositions();
				foreach ($positions as $position) {
					$available = isset($POST['position-' . $position['id']]);
					if ($available == ($position['available'] == 1)) {
						continue;
					}
					$db->setPositionAvailability($position['id'], $available ? 1 : 0);
				}
				$changed = true;
			} elseif (isset($POST['newPositionName'])) {
				$id = preg_replace('/[^a-z-]/', '', preg_replace('/ /', '-', strtolower($POST['newPositionName'])));
				$db->addPosition($id);
				foreach (Template::SUPPORTED_LOCALES as $locale) {
					$db->updateTranslation('position.' . $id . '.name', $locale, $POST['newPositionName']);
					$db->updateTranslation('position.' . $id . '.description', $locale, '');
				} // setting translations to a default string, we'll edit it right after
				return new RedirectResponse('position.php?id=' . $id, 303);
			} elseif (isset($POST['notifyEmail'])) {
				if ($POST['notifyEmail'] === 'false') {
					$email = '0';
				} else {
					$email = '1';
				}
				$db->setConfigValue('notifyEmail', $email);
				$changed = true;
			}

			if ($changed) {
				// This is a pattern: https://en.wikipedia.org/wiki/Post/Redirect/Get
				// $_SERVER['REQUEST_URI'] is already url encoded
				return new RedirectResponse($_SERVER['REQUEST_URI'], 303);
			}
		}

		$expiry = $db->getConfigValue('expiry');

		// Get the timestamp in correct format
		if ($expiry !== null) {
			/**
	   * @noinspection PhpUnhandledExceptionInspection
*/
			$expiry = (new DateTime('now', new DateTimeZone('Europe/Rome')))->setTimestamp($expiry);
		}

		return new HtmlResponse(
			$template->render(
				'settings',
				[
				'myuser'           => $_SESSION['uid'],
				'myname'           => $_SESSION['cn'],
				'expiry'           => $expiry,
				'error'            => $error,
				'sendMail'         => (int) $db->getConfigValue('notifyEmail'),
				'positions' => $db->getPositions(Template::getLocale() ?? 'en_US')
				]
			)
		);
	}
}
