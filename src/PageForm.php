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
		$positions = $db->getPositions(Template::getLocale() ?? 'en_US'); // [ ['id' => 1, 'name' => 'name', 'description' => 'description', 'available' => 1], ...]]

		if (count($positions) === 0) {
			$expiry = 1;
		} else {
			// check that there is at least one position available
			$isAtLeastOneAvailable = false;
			for ($i = 0; $i < count($positions); $i++) {
				if ($positions[$i]['available'] == 1) {
					$isAtLeastOneAvailable = true;
					break;
				}
			}
			if (!$isAtLeastOneAvailable) {
				$expiry = 1;
			}
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
			];
			foreach ($checkboxes as $attr) {
				if (!isset($POST[$attr]) || $POST[$attr] !== 'true') {
					return new HtmlResponse($template->render('form', ['error' => 'consent', 'positions' => $positions]), 400);
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
					if ($attr === 'area') {
						$indexs = array_column($positions, 'id');
						$index = array_search($POST[$attr], $indexs);
						if ($index !== false) {
							$POST[$attr] = $positions[$index]['name'];
						} else {
							// You are a special one, aren't you? this was a challange, if you found out that you can submit any value for the area field, you'll get extra points during the interview, use this box to have fun with your position name, eg. "I'm a hacker", "fsociety"
						}
					}
					$user->$attr = $POST[$attr];
					if (is_string($user->$attr)) {
						$user->$attr = trim($user->$attr);
					}
				} else {
					return new HtmlResponse($template->render('form', ['error' => 'form', 'positions' => $positions]), 400);
				}
			}
			$user->submitted = time();
			$user->matricola = strtolower($user->matricola);
			if (preg_match('#^[sd]\d+$#', $user->matricola) !== 1) {
				return new HtmlResponse($template->render('form', ['error' => 'form', 'positions' => $positions]), 400);
			}

			try {
				list($id, $token) = $db->addUser($user);
			} catch (DuplicateUserException $e) {
				return new HtmlResponse($template->render('form', ['error' => 'duplicate', 'positions' => $positions]), 400);
			} catch (DatabaseException $e) {
				return new HtmlResponse($template->render('form', ['error' => 'database', 'positions' => $positions]), 500);
			} catch (Exception $e) {
				return new HtmlResponse($template->render('form', ['error' => 'wtf', 'positions' => $positions]), 500);
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

		return new HtmlResponse($template->render('form', ['positions' => $positions]));
	}
}
