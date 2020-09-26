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

class PageSettings implements RequestHandlerInterface {
	public function handle(ServerRequestInterface $request): ResponseInterface {
		$template = Template::create($request->getUri());

		Utils::requireAdmin();

		$db = new Database();

		$error = null;

		if($request->getMethod() === 'POST') {
			$POST = $request->getParsedBody();
			// Form submission
			$changed = false;
			if(isset($POST['noexpiry'])) {
				// Unset form expiry
				$db->unsetConfigValue('expiry');
				$changed = true;
			} elseif(isset($POST['expiry'])) {
				// Set form expiry
				try {
					$expiryNew = new DateTime($POST['expiry'], new DateTimeZone('Europe/Rome'));
					$db->setConfigValue('expiry', (string) $expiryNew->getTimestamp());
					$changed = true;
				} catch(Exception $e) {
					$error = $e->getMessage();
				}
			} elseif(isset($POST['rolesReset'])) {
				// Unset unavailable roles
				$db->unsetConfigValue('rolesUnavailable');
				$changed = true;
			} elseif(isset($POST['roles'])) {
				// Set available roles
				$rolesRule = implode('|', $POST['roles']);
				$db->setConfigValue('rolesUnavailable', $rolesRule);
				$changed = true;
			} elseif(isset($POST['notifyEmail'])) {
				if($POST['notifyEmail'] === 'false') {
					$email = '0';
				} else {
					$email = '1';
				}
				$db->setConfigValue('notifyEmail', $email);
				$changed = true;
			}

			if($changed) {
				// This is a pattern: https://en.wikipedia.org/wiki/Post/Redirect/Get
				// $_SERVER['REQUEST_URI'] is already url encoded
				return new RedirectResponse($_SERVER['REQUEST_URI'], 303);
			}
		}

		$expiry = $db->getConfigValue('expiry');

		// Get the timestamp in correct format
		if($expiry !== null) {
			/** @noinspection PhpUnhandledExceptionInspection */
			$expiry = (new DateTime('now', new DateTimeZone('Europe/Rome')))->setTimestamp($expiry);
		}

		return new HtmlResponse($template->render('settings', [
				'myuser'           => $_SESSION['uid'],
				'myname'           => $_SESSION['cn'],
				'expiry'           => $expiry,
				'error'            => $error,
				'sendMail'         => (int) $db->getConfigValue('notifyEmail'),
				'rolesUnavailable' => $db->getConfigValue('rolesUnavailable')
			]));
	}
}