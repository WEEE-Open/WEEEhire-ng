<?php

namespace WEEEOpen\WEEEHire;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;

class PageCandidates implements RequestHandlerInterface
{
	public function handle(ServerRequestInterface $request): ResponseInterface
	{

		$template = Template::create($request->getUri());

		Utils::requireAdmin();

		$db = new Database();

		$GET = $request->getQueryParams();

		if (isset($GET['id'])) {
			// candidates.php?id=... => page with details on a single candidate

			$ldap = new Ldap(
				WEEEHIRE_LDAP_URL,
				WEEEHIRE_LDAP_BIND_DN,
				WEEEHIRE_LDAP_PASSWORD,
				WEEEHIRE_LDAP_USERS_DN,
				WEEEHIRE_LDAP_INVITES_DN,
				WEEEHIRE_LDAP_STARTTLS
			);
			$id = (int) $GET['id'];
			$user = $db->getUser($id);

			if ($user === null) {
				return new HtmlResponse($template->render('error', ['message' => 'Invalid user ID']), 404);
			}

			// A form has been submitted
			if ($request->getMethod() === 'POST') {
				$POST = $request->getParsedBody();
				// Most buttons also update notes (so we can write "seems good" and press "approve")
				$notes = $POST['notes'] ?? '';
				$status = $user->getCandidateStatus();

				if (isset($POST['edit'])) {
					// If all data is present, this method will update $user so it only has to be stored in the database
					if ($user->fromPost($POST)) {
						// Store it
						$db->updateUser($user);
					}
				} elseif (isset($POST['save'])) {
					// This button is always available
					$db->saveNotes($id, $notes);
				} elseif (isset($POST['voteButton']) && isset($POST['vote'])) {
					// This button is always available
					$db->setEvaluation($id, $_SESSION['uid'], $_SESSION['cn'], $POST['vote']);
				} elseif (isset($POST['unvote']) && isset($POST["id_evaluation"])) {
					// This button is always available
					$db->removeEvaluation($POST["id_evaluation"]);
				} elseif (isset($POST['approve'])) {
					if ($status === User::STATUS_NEW) {
						$db->setStatus($id, true, $_SESSION['cn'] ?? null);
					}
				} elseif (isset($POST['reject'])) {
					if ($status === User::STATUS_NEW || $status === User::STATUS_PUBLISHED_HOLD) {
						$db->setStatus($id, false, $_SESSION['cn'] ?? null);
						$db->setHold($id, false); // Remove hold or it will mess up the status (#41)
					}
				} elseif (isset($POST['limbo'])) {
					if ($status === User::STATUS_NEW_REJECTED || $status === User::STATUS_NEW_APPROVED) {
						$db->setStatus($id, null, null);
					}
				} elseif (isset($POST['publishnow'])) {
					if ($status === User::STATUS_NEW_APPROVED) {
						$email = $POST['email'] ?? '';
						$subject = $POST['subject'] ?? '';
						$recruiter = $POST['recruiter'] ?? '';
						if (strlen($email) <= 0) {
							return new HtmlResponse($template->render('error', ['message' => 'Write an email']), 400);
						}
						if (strlen($subject) <= 0) {
							return new HtmlResponse(
								$template->render('error', ['message' => 'Write a subject line']),
								400
							);
						}
						if (strlen($recruiter) <= 0 || strpos($recruiter, '|') === false) {
							return new HtmlResponse(
								$template->render('error', ['message' => 'Select a recruiter']),
								400
							);
						}
						$recruiter = explode('|', $recruiter, 2);
						$db->setRecruiter($id, $recruiter[1], $recruiter[0]);
						Email::sendMail(Utils::politoMail($user->matricola), $subject, $email);
						$db->setEmailed($id, true);
						$db->setPublished($id, true);
					} elseif ($status === User::STATUS_NEW_REJECTED) {
						$db->setPublished($id, true);
					} elseif ($status === User::STATUS_NEW_HOLD) {
						// TODO: send mail
						$db->saveVisibleNotes($id, $POST['visiblenotes']);
						$db->setPublished($id, true);
					}
				} elseif (isset($POST['savevisiblenotes'])) {
					if ($status === User::STATUS_PUBLISHED_HOLD || $status === User::STATUS_NEW_HOLD) {
						$db->saveVisibleNotes($id, $POST['visiblenotes']);
					}
				} elseif (isset($POST['approvefromhold'])) {
					if ($status === User::STATUS_PUBLISHED_HOLD) {
						// Unpublish so we can choose a recruiter
						// The end result should be STATUS_NEW_APPROVED
						$db->setPublished($id, false);
						$db->setStatus($id, true, $_SESSION['cn'] ?? null);
						$db->setEmailed($id, false);
						$db->setHold($id, false); // Remove hold or it will mess up the status (#41)
					}
				} elseif (isset($POST['holdon'])) {
					if ($status === User::STATUS_NEW || $status === User::STATUS_PUBLISHED_REJECTED) {
						$db->setHold($id, true);
					}
				} elseif (isset($POST['holdoff'])) {
					if ($status === User::STATUS_NEW_HOLD) {
						$db->setHold($id, false);
					}
				}

				// TODO: is it really necessary to have $changed?
				// This is a pattern: https://en.wikipedia.org/wiki/Post/Redirect/Get
				$uri = Utils::appendQueryParametersToRelativeUrl($request->getUri(), ['edit' => null]);
				return new RedirectResponse($uri, 303);
			} // "if this is a POST request"

			// Render the page
			$page = $template->render(
				'candidate',
				[
					'user'        => $user,
					'edit'        => isset($GET['edit']),  // candidates.php?id=123&edit, allows editing of personal data
					'recruiters'  => $ldap->getRecruiters(),
					'evaluations' => $db->getEvaluation($id),
					'uid'         => $_SESSION['uid'],
					'cn'          => $_SESSION['cn']
				]
			);
			return new HtmlResponse($page);
		} else {
			// no ?id=... parameter => render the page with a candidates list
			if ($request->getMethod() === 'POST') {
				$POST = $request->getParsedBody();
				// This is a form submission
				if (isset($POST['publishallrejected'])) {
					$db->publishRejected();
					return new RedirectResponse('/candidates.php', 303);
				} elseif (isset($POST['deleteolderthan']) && isset($POST['days'])) {
					$days = (int) $POST['days'];
					if ($days <= 0) {
						$days = 0;
					}
					$db->deleteOlderThan($days);
					return new RedirectResponse('/candidates.php', 303);
				}
			}

			$users = $db->getallusersfortable($_SESSION['uid']);
			return new HtmlResponse($template->render('candidates', ['users' => $users, 'myuser' => $_SESSION['uid'], 'myname' => $_SESSION['cn']]));
		}
	}
}
