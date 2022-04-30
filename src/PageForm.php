<?php

namespace WEEEOpen\WEEEHire;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;

class PageForm implements RequestHandlerInterface
{
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$template = Template::create($request->getUri());

		$db = new Database();

		$expiry = $db->getConfigValue('expiry');
		$rolesAvailable = $db->getConfigValue('rolesAvailable');
		$rolesAvailableCount = $rolesAvailable ? count(explode('|', $rolesAvailable)) : 0;

		if ($rolesAvailableCount === 0) {
			$expiry = 1;
		}

		// Get from DB -> if "unixtime.now >= expiry date" then candidate_close : else show the form
		if ($expiry !== null && time() >= $expiry) {
			if ($request->getMethod() === 'POST') {
				return new HtmlResponse($template->render('candidate_close'), 400);
			} else {
				return new HtmlResponse($template->render('candidate_close'));
			}
		}

		if ($request->getMethod() === 'POST') {
			$POST = $request->getParsedBody();
			$checkboxes = [
				'mandatorycheckbox_0',
				'mandatorycheckbox_1',
			];
			foreach ($checkboxes as $attr) {
				if (!isset($POST[$attr]) || $POST[$attr] !== 'true') {
					return new HtmlResponse($template->render('form', ['error' => 'consent', 'rolesAvailable' => $rolesAvailable]), 400);
				}
			}

			$attrs = [
				'name',
				'surname',
				'degreecourse',
				'year',
				'matricola',
				'area',
				'letter'
			];
			$user = new User();
			foreach ($attrs as $attr) {
				if (isset($POST[$attr]) && $POST[$attr] !== '') {
					$user->$attr = $POST[$attr];
				} else {
					return new HtmlResponse($template->render('form', ['error' => 'form', 'rolesAvailable' => $rolesAvailable]), 400);
				}
			}
			$user->submitted = time();
			$user->matricola = strtolower($user->matricola);
			if (preg_match('#^[sd]\d+$#', $user->matricola) !== 1) {
				return new HtmlResponse($template->render('form', ['error' => 'form', 'rolesAvailable' => $rolesAvailable]), 400);
			}

			try {
				list($id, $token) = $db->addUser($user);
			} catch (DuplicateUserException $e) {
				return new HtmlResponse($template->render('form', ['error' => 'duplicate', 'rolesAvailable' => $rolesAvailable]), 400);
			} catch (DatabaseException $e) {
				return new HtmlResponse($template->render('form', ['error' => 'database', 'rolesAvailable' => $rolesAvailable]), 500);
			} catch (Exception $e) {
				return new HtmlResponse($template->render('form', ['error' => 'wtf', 'rolesAvailable' => $rolesAvailable]), 500);
			}

			$query = http_build_query(['id' => $id, 'token' => $token]);

			// Send confirmation email to candidate
			Email::sendMail(
				Utils::politoMail($user->matricola),
				$template->render('confirm_email', ['subject' => true]),
				$template->render('confirm_email', ['link' => WEEEHIRE_SELF_LINK . "/status.php?$query", 'subject' => false])
			);

			// Send email to us
			if ((int) $db->getConfigValue('notifyEmail') !== 0) {
				Email::sendMail(
					WEEEHIRE_EMAIL_FALLBACK,
					'Nuova candidatura - ' . $user->area,
					$template->render(
						'notification_email',
						['user' => $user, 'link' => WEEEHIRE_SELF_LINK . "/candidates.php?id=$id"]
					)
				);
			}

			return new RedirectResponse("/status.php?$query", 303);
		}

		return new HtmlResponse($template->render('form', ['rolesAvailable' => $rolesAvailable]));
	}
}
