<?php

namespace WEEEOpen\WEEEHire;

use DateTime;
use DateTimeZone;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sabre\VObject\Component\VCalendar;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\TextResponse;

class PageInterviews implements RequestHandlerInterface
{
	public function handle(ServerRequestInterface $request): ResponseInterface
	{

		$template = Template::create($request->getUri());

		Utils::requireAdmin();

		$GET = $request->getQueryParams();

		$db = new Database();
		if (isset($GET['id'])) {
			// interviews.php?id=... => page with details on a single interview
			$id = (int) $GET['id'];
			$user = $db->getUser($id);
			$interview = $db->getInterview($id);
			$notes = $db->getNotesByCandidateId($id);

			// Download button
			if (isset($GET['download'])) {
				if ($user === null) {
					return new TextResponse('User not found', 404);
				}
				if ($interview->when === null) {
					return new TextResponse('Interview not scheduled', 404);
				}

				if (count($notes) > 0) {
					$pieces = [];
					foreach ($notes as $row) {
						$pieces[] = "> {$row['note']} - {$row['uid']}\n";
					}
					$optionalNotes = "\n\nNote:\n" . implode('', $pieces);
				} else {
					$optionalNotes = "";
				}

				$ical = new VCalendar([
					'VEVENT' => [
						'SUMMARY' => "Colloquio con $user->name $user->surname",
						'UID' => $user->id,
						'DTSTART' => $interview->when,
						'DTEND' => (clone $interview->when)->add(new \DateInterval('PT30M')),
						'DESCRIPTION' => "Colloquio per $user->area.$optionalNotes",
					]
				]);
				/** @noinspection PhpUndefinedFieldInspection */
				/** @noinspection PhpMethodParametersCountMismatchInspection */
				$ical->VEVENT->add(
					'URL',
					Utils::appendQueryParametersToRelativeUrl($request->getUri(), ['download' => null]),
					[
						'VALUE' => 'URI',
					]
				);
				/** @noinspection PhpUndefinedFieldInspection */
				/** @noinspection PhpMethodParametersCountMismatchInspection */
				$ical->VEVENT->add(
					'ORGANIZER',
					'https://t.me/' . $interview->recruitertg,
					[
						'CN' => $interview->recruiter,
					]
				);

				$headers = [
					'Content-Type' => 'text/calendar; charset=utf-8',
					'Content-Description' => 'File Transfer',
					'Content-Disposition' => "attachment; filename=\"colloquio $user->name $user->surname.ics\"",
				];

				return new TextResponse($ical->serialize(), 200, $headers);
			}

			// No user?
			if ($user === null) {
				return new HtmlResponse($template->render('error', ['message' => 'Invalid user ID']), 404);
			}

			// Interview page not available?
			if (!$user->status || !$user->published) {
				$page = $template->render('error', [
					'message' => sprintf(
						__('È necessario approvare e pubblicare la candidatura di %s per accedere a questa pagina. Torna alla <a href="/candidates.php?id=%d">pagina di gestione candidato</a>.'),
						htmlspecialchars($user->name, ENT_QUOTES | ENT_HTML5),
						$user->id
					)
				]);
				return new HtmlResponse($page, 400);
			}

			$ldap = new Ldap(
				WEEEHIRE_LDAP_URL,
				WEEEHIRE_LDAP_BIND_DN,
				WEEEHIRE_LDAP_PASSWORD,
				WEEEHIRE_LDAP_USERS_DN,
				WEEEHIRE_LDAP_INVITES_DN,
				WEEEHIRE_LDAP_STARTTLS
			);

			// A form has been submitted
			if ($request->getMethod() === 'POST') {
				$POST = $request->getParsedBody();
				$changed = false;
				$note = $POST['note'] ?? '';

				if (isset($POST['edit'])) {
					// Update personal details, same as candidates.php
					// If all data is present, this method will update $user so it only has to be stored in the database
					if ($user->fromPost($POST)) {
						// Store it
						$db->updateUser($user);
						$changed = true;
					}
				} elseif (isset($POST['saveNote'])) {
					$db->saveNotes($_SESSION['uid'], $id, $note, 'interview');
					$changed = true;
				} elseif (isset($POST['updateNote'])) {
					$db->updateNote($_SESSION['uid'], $id, $note, 'interview');
					$changed = true;
				} elseif (isset($POST['invite'])) {
					// Generate invite link
					$link = $ldap->createInvite($user);
					$db->setInviteLink($id, $link);
					$changed = true;
				} elseif (isset($POST['setinterview']) && isset($POST['when1']) && isset($POST['when2']) && isset($POST['recruiter'])) {
					// Schedule an interview
					$recruiter = $POST['recruiter'];
					if (strlen($recruiter) <= 0 || strpos($recruiter, '|') === false) {
						return new HtmlResponse($template->render('error', ['message' => 'Select a recruiter']), 400);
					}
					// Split recruiter name and telegram account
					$recruiter = explode('|', $recruiter, 2);
					// Glue date and time together
					$when = DateTime::createFromFormat(
						"Y-m-d H:i",
						$POST['when1'] . ' ' . $POST['when2'],
						new DateTimeZone('Europe/Rome')
					);
					$db->setInterviewSchedule($interview->id, $recruiter[1], $recruiter[0], $when);
					$changed = true;
				} elseif (isset($POST['unsetinterview'])) {
					// Unschedule an interview
					$db->setInterviewSchedule($interview->id, null, null, null);
					$changed = true;
				} elseif (isset($POST['saveInterviewComments'])) {
					$db->setInterviewData($interview->id, $POST['answers'] ?? null);
					$changed = true;
				} elseif (isset($POST['approve'])) {
					$db->setInterviewStatus($interview->id, true);
					$db->setHold($interview->id, false);
					$changed = true;
				} elseif (isset($POST['reject'])) {
					$db->setInterviewStatus($interview->id, false);
					$db->setHold($interview->id, false);
					$changed = true;
				} elseif (isset($POST['limbo'])) {
					$db->setInterviewStatus($interview->id, null);
					$db->setHold($interview->id, false);
					$changed = true;
				} elseif (isset($POST['pushHold'])) {
					$db->setHold($interview->id, true);
					$changed = true;
				} elseif (isset($POST['popHold'])) {
					$db->setHold($interview->id, false);
					$changed = true;
				} elseif (isset($POST['setSafetyExamDate'])) {
					$date = DateTime::createFromFormat(
						"Y-m-d H:i",
						$POST['safetyExamDate1'] . ' ' . $POST['safetyExamDate2'],
						new DateTimeZone('Europe/Rome')
					);
					$db->setSafetyExamDate($interview->id, $date);
					$changed = true;
				} else if (isset($post['unsetSafetyExamDate'])) {
					$db->clearSafetyExamDate($interview->id);
					$changed = true;
				}

				if ($changed) {
					// This is a pattern: https://en.wikipedia.org/wiki/Post/Redirect/Get
					$uri = Utils::appendQueryParametersToRelativeUrl($request->getUri(), ['edit' => null]);
					return new RedirectResponse($uri, 303);
				}
			}

			// Render the page
			$page = $template->render('interview', [
				'user'       => $user,
				'interview'  => $interview,
				'edit'       => isset($GET['edit']),
				'recruiters' => $ldap->getRecruiters(),
				'notes'      => $notes,
			]);

			return new HtmlResponse($page);
		} else {
			// No id parameter => list of interviews

			// No buttons here to submit anything
			//	if($request->getMethod() === 'POST') {
			//
			//	}

			if (isset($GET['byrecruiter'])) {
				// List of interviews by recruiter
				$interviews = $db->getAllAssignedInterviewsForTable();
				$page = $template->render('interviewsbyrecruiter', ['interviews' => $interviews, 'myuser' => $_SESSION['uid'], 'myname' => $_SESSION['cn']]);
				return new HtmlResponse($page);
			} else {
				// List of all interviews in chronological order
				$interviews = $db->getAllInterviewsForTable();
				$page = $template->render('interviews', ['interviews' => $interviews, 'myuser' => $_SESSION['uid'], 'myname' => $_SESSION['cn']]);
				return new HtmlResponse($page);
			}
		}
	}
}
