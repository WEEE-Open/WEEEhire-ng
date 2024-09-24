<?php

namespace WEEEOpen\WEEEHire;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\Response\JsonResponse;

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
				$note = $POST['note'] ?? '';
				$status = $user->getCandidateStatus();

				if (isset($POST['edit'])) {
					// If all data is present, this method will update $user so it only has to be stored in the database
					if ($user->fromPost($POST)) {
						// Store it
						$db->updateUser($user);
					}
				} elseif (isset($POST['saveNote'])) {
					// This button is always available
					$db->saveNotes($_SESSION['uid'], $id, $note);
				} elseif (isset($POST['updateNote'])) {
					// This button is always available
					$db->updateNote($_SESSION['uid'], $id, $note);
				} elseif (isset($POST['voteButton']) && isset($POST['vote'])) {
					// This button is always available
					$db->setEvaluation($id, $_SESSION['uid'], $_SESSION['cn'], $POST['vote']);
				} elseif (isset($POST['unvote']) && isset($POST["id_evaluation"])) {
					// This button is always available
					$db->removeEvaluation($POST["id_evaluation"]);
				} elseif (isset($POST['resendemail'])) {
					if ($status === User::STATUS_NEW) {
						try {
								  $token = $db->regenerateToken($id);
						} catch (DatabaseException $e) {
							   return new HtmlResponse($template->render('error', ['message' => 'Database error']), 500);
						} catch (\Exception $e) {
							return new HtmlResponse($template->render('error', ['message' => 'User does not exists']), 404);
						}

						Email::sendMail(
							Utils::politoMail($user->matricola),
							$template->render('confirm_email', ['subject' => true]),
							$template->render('confirm_email', ['link' => WEEEHIRE_SELF_LINK . "/status.php?id=" . $id . "&token=" . $token, 'subject' => false, 'resend' => true]),
						);

						return new RedirectResponse("/candidates.php?id=" . $GET['id'], 303); // prevent resending email on refresh
					}
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
						if (!isset($POST['visiblenotes'])) {
							return new HtmlResponse(
								$template->render('error', ['message' => 'Add notes visible to the candidate']),
								400
							);
						}
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

			// Get additional data
			$user = $db->getPrevAndNextUser($user, $_SESSION['uid']);

			// Render the page
			$page = $template->render(
				'candidate',
				[
				'user'        => $user,
				'edit'        => isset($GET['edit']),  // candidates.php?id=123&edit, allows editing of personal data
				'recruiters'  => $ldap->getRecruiters(),
				'evaluations' => $db->getEvaluation($id),
				'uid'         => $_SESSION['uid'],
				'cn'          => $_SESSION['cn'],
				'notes'       => $db->getNotesByCandidateId($id)
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

			$users = $db->getUsersWithNotesAndEvaluations($_SESSION['uid']);
			if (isset($GET['format'])) {
				$usersWithDetails = [];
				foreach ($users as $user) {
					$userInfo = $db->getUser($user["id"]); // 'id', 'name', 'surname', 'degreecourse', 'year', 'matricola','area', 'letter', 'published', 'status', 'hold', 'recruiter', 'recruitertg', 'submitted', 'visiblenotes', 'emailed', 'invitelink'
					$interviewInfo = $db->getInterview($user["id"]); // "id","when","status","recruiter","recruitertg","answers","hold","safetyTestDate"
					$details = [
						"id" => $userInfo->id,
						"name" => $userInfo->name,
						"surname" => $userInfo->surname,
						"degreeCourse" => $userInfo->degreecourse,
						"year" => $userInfo->year,
						"matricola" => $userInfo->matricola,
						"area" => $userInfo->area,
						"letter" => $userInfo->letter,
						"submitted" => $userInfo->submitted,
						"hold" => $userInfo->hold ? 'Yes' : 'No',
						"published" => $userInfo->published ? 'Yes' : 'No',
						"recruiter" => $userInfo->recruiter,
						"recruiterTg" => $userInfo->recruitertg,
						"visibleNotes" => $userInfo->visiblenotes,
						"emailed" => $userInfo->emailed ? 'Yes' : 'No',
						"inviteLink" => $userInfo->invitelink,
						"interviewNotes" => $interviewInfo->answers,
						"interviewHold" => $interviewInfo->hold ? 'Yes' : 'No',
						"saftyTestDate" => $interviewInfo->safetyTestDate,
					];
					if ($userInfo->status === true) {
						$details["applicationStatus"] = 'Approved';
					} elseif ($userInfo->status === false) {
						$details["applicationStatus"] = 'Rejected';
					} else {
						$details["applicationStatus"] = 'Pending';
					}
					if ($interviewInfo->status === true) {
						$details["interviewStatus"] = 'Approved';
					} elseif ($interviewInfo->status === false) {
						$details["interviewStatus"] = 'Rejected';
					} else {
						$details["interviewStatus"] = 'Pending';
					}
					if ($interviewInfo->when !== null) {
						$details["interviewDate"] = $interviewInfo->when->format('Y-m-d H:i');
					} else {
						$details["interviewDate"] = null;
					}
					$usersWithDetails[] = $details;
				}
				if ($GET['format'] == 'csv') {
					$csv = "Id, Name, Surname, Degree Course, Year, Matricola, Area, Letter, Submitted, Hold, Published, Recruiter, Recruiter Telegram, Visible Notes, Emailed, Invite Link, Interview Notes, Interview Hold, Safety Test Date, Application Status, Interview Status, Interview Date\n";
					foreach ($usersWithDetails as $user) {
						$csv .= '"' . $user['id'] . '","' . $user['name'] . '","' . $user['surname'] . '","' . $user['degreeCourse'] . '","' . $user['year'] . '","' . $user['matricola'] . '","' . $user['area'] . '","' . $user['letter'] . '","' . $user['submitted'] . '","' . $user['hold'] . '","' . $user['published'] . '","' . $user['recruiter'] . '","' . $user['recruiterTg'] . '","' . $user['visibleNotes'] . '","' . $user['emailed'] . '","' . $user['inviteLink'] . '","' . $user['interviewNotes'] . '","' . $user['interviewHold'] . '","' . $user['saftyTestDate'] . '","' . $user['applicationStatus'] . '","' . $user['interviewStatus'] . '","' . $user['interviewDate'] . "\"\n";
					}
					return new TextResponse($csv, 200, ['Content-Type' => 'text/csv']);
				} elseif ($GET['format'] == 'json') {
					return new JsonResponse($usersWithDetails);
				}
			}
			return new HtmlResponse($template->render('candidates', ['users' => $users, 'myuser' => $_SESSION['uid'], 'myname' => $_SESSION['cn']]));
		}
	}
}
