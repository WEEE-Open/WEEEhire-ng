<?php

namespace WEEEOpen\WEEEHire\tests;

use Psr\Http\Message\ResponseInterface;
use WEEEOpen\WEEEHire\Database;
use WEEEOpen\WEEEHire\PageCandidates;
use WEEEOpen\WEEEHire\PageStatus;
use WEEEOpen\WEEEHire\User;
use Laminas\Diactoros\ServerRequestFactory;

class PageCandidatesAndPageStatusTest extends PagesTest
{
	private function addCandidate(string $id = 's1', string $name = 'Test', string $surname = 'Test')
	{
		$user = new User();
		$user->name = $name;
		$user->surname = $surname;
		$user->matricola = $id;
		$user->area = 'Altro';
		$user->letter = 'Lorem ipsum blah blah blah';
		$user->degreecourse = 'Communications And Computer Networks Engineering';
		$user->year = '1º Magistrale';
		$user->submitted = time();
		$db = new Database();
		/** @noinspection PhpUnhandledExceptionInspection */
		return $db->addUser($user);
	}

	public function testEmpty()
	{
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/candidates.php'], [], [], [], []);
		$response = (new PageCandidates())->handle($request);

		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringContainsStringIgnoringCase('0 candidati totali', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('0 da valutare', $response->getBody());
	}

	public function testNew()
	{
		list($id, $token) = $this->addCandidate();

		// Status page
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/candidates.php"], ['id' => $id, 'token' => $token], [], [], []);
		$response = (new PageStatus())->handle($request);
		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringContainsStringIgnoringCase('in corso', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('scarica', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('elimina', $response->getBody());
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		// Candidates page
		$response = $this->assertCandidatesPageContains(['1 da valutare']);
		$this->assertStringContainsString('Test Test', $response->getBody());

		return [$id, $token];
	}

	public function testNewTwice()
	{
		$this->addCandidate();
		$this->addCandidate('s2', 'Test', 'DellaProva');

		$response = $this->assertCandidatesPageContains(['2 da valutare']);
		$this->assertStringContainsString('Test Test', $response->getBody());
		$this->assertStringContainsString('Test DellaProva', $response->getBody());
	}

	public function testApproved()
	{
		list($id, $token) = $this->testNew();

		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], ['approve' => 'true'], [], []);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(303, $response->getStatusCode(), 'Redirect to same page');
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		$theNote = 'notes example t6PH2lQl7XFNdRrSUebA';
		$this->saveNotes($id, $theNote);

		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], [], [], []);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringContainsString('Test', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('rimanda nel limbo', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('email', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('note', $response->getBody());
		$this->assertStringContainsString($theNote, $response->getBody());
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		// Status page
		$this->assertStatusPageWait($id, $token);

		// Candidates list
		$response = $this->assertCandidatesPageContains(['0 da valutare', '1 approvato', '1 da pubblicare']);
		$this->assertStringContainsString('Test Test', $response->getBody());

		return [$id, $token];
	}

	public function testApprovedRevert()
	{
		list($id, $token) = $this->testApproved();

		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], ['limbo' => 'true'], [], []);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(303, $response->getStatusCode(), 'Redirect to same page');
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		$theNote = 'notes example os8mieh7saich4rohZ6E';
		$this->updateNotes($id, $theNote);

		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], [], [], []);
		$response = (new PageCandidates())->handle($request);

		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringContainsString('Test', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('approva', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('rifiuta', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('note', $response->getBody());
		$this->assertStringContainsString($theNote, $response->getBody());
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		// Status page
		$this->assertStatusPageWait($id, $token);

		// Candidates list
		$response = $this->assertCandidatesPageContains(['1 da valutare', '0 approvati', '0 da pubblicare']);
		$this->assertStringContainsString('Test Test', $response->getBody());
	}

	public function testRejected()
	{
		list($id, $token) = $this->addCandidate();

		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], ['reject' => 'true'], [], []);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(303, $response->getStatusCode(), 'Redirect to same page');
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		$theNote = 'notes example Aew9mahdohjee6chaese';
		$this->saveNotes($id, $theNote);

		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], [], [], []);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringContainsString('Test', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('rimanda nel limbo', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('pubblica', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('note', $response->getBody());
		$this->assertStringContainsString($theNote, $response->getBody());
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		$this->assertStatusPageWait($id, $token);

		$response = $this->assertCandidatesPageContains(['0 da valutare', '0 approvati', '1 rifiutato', '1 da pubblicare']);
		$this->assertStringContainsString('Test Test', $response->getBody());

		// Now publish it!
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], ['publishnow' => 'true', 'notes' => 'notes example uroh7reith8aet6Yoish'], [], []);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(303, $response->getStatusCode(), 'Redirect to same page');
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		$response = $this->assertCandidatePageHasPublishedWarning($id);
		$this->assertStringContainsStringIgnoringCase('note', $response->getBody());
		$this->assertStringContainsString($theNote, $response->getBody());
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		$this->assertStatusPageRejected($id, $token);

		$response = $this->assertCandidatesPageContains(['0 da valutare', '0 approvati', '1 rifiutato', '0 da pubblicare']);
		$this->assertStringContainsString('Test Test', $response->getBody());

		return [$id, $token];
	}

	public function testHold()
	{
		list($id, $token) = $this->addCandidate();

		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], ['holdon' => 'true'], [], []);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(303, $response->getStatusCode(), 'Redirect to same page');
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		$theNote = 'notes example xooh1ohqueitheW1';
		$this->saveNotes($id, $theNote);

		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], [], [], []);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringContainsString('Test', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('togli dalla lista d\'attesa', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('pubblica', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('note', $response->getBody());
		$this->assertStringContainsString($theNote, $response->getBody());
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		$this->assertStatusPageWait($id, $token);

		$response = $this->assertCandidatesPageContains(['0 da valutare', '0 approvati', '0 rifiutati', '1 da pubblicare']);
		$this->assertStringContainsString('Test Test', $response->getBody());

		// Try to publish it, but...
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], ['publishnow' => 'true'], [], []);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(400, $response->getStatusCode(), 'Cannot publish without visible notes');
		$this->assertStringContainsStringIgnoringCase('add notes visible to the candidate', $response->getBody());
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		// Try again
		$visibleNotes = 'You will be considered next semester, due to REASONS';
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], ['publishnow' => 'true', 'visiblenotes' => $visibleNotes], [], []);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(303, $response->getStatusCode(), 'Redirect to same page');
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		$response = $this->assertCandidatePageHasPublishedWarning($id);
		$this->assertStringContainsString($visibleNotes, $response->getBody());
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		$this->assertStatusPageHold($id, $token, $visibleNotes);

		$response = $this->assertCandidatesPageContains(['0 da valutare', '0 approvati', '0 rifiutati', '0 da pubblicare']);
		$this->assertStringContainsString('Test Test', $response->getBody());

		return [$id, $token];
	}

	private function assertStatusPageWait($id, $token): void
	{
		$request = ServerRequestFactory::fromGlobals(
			['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/candidates.php"],
			['id' => $id, 'token' => $token],
			[],
			[],
			[]
		);
		$response = (new PageStatus())->handle($request);
		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringContainsStringIgnoringCase('in corso', $response->getBody());
		$this->assertStringNotContainsStringIgnoringCase('ammesso/a', $response->getBody());
		$this->assertStringNotContainsStringIgnoringCase('respinta', $response->getBody());
		$this->assertStringNotContainsStringIgnoringCase('sospes', $response->getBody());

		$this->assertStringContainsString('status-waiting', $response->getBody());
		$this->assertStringNotContainsString('status-approved', $response->getBody());
		$this->assertStringNotContainsString('status-rejected', $response->getBody());
		$this->assertStringNotContainsString('status-postponed', $response->getBody());
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}
	}

	private function assertStatusPageApproved($id, $token): void
	{
		$request = ServerRequestFactory::fromGlobals(
			['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/candidates.php"],
			['id' => $id, 'token' => $token],
			[],
			[],
			[]
		);
		$response = (new PageStatus())->handle($request);
		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringNotContainsStringIgnoringCase('in corso', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('ammesso/a', $response->getBody());
		$this->assertStringNotContainsStringIgnoringCase('respinta', $response->getBody());
		$this->assertStringNotContainsStringIgnoringCase('sospes', $response->getBody());

		$this->assertStringNotContainsString('status-waiting', $response->getBody());
		$this->assertStringContainsString('status-approved', $response->getBody());
		$this->assertStringNotContainsString('status-rejected', $response->getBody());
		$this->assertStringNotContainsString('status-postponed', $response->getBody());
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}
	}

	private function assertStatusPageRejected($id, $token): void
	{
		$request = ServerRequestFactory::fromGlobals(
			['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/candidates.php"],
			['id' => $id, 'token' => $token],
			[],
			[],
			[]
		);
		$response = (new PageStatus())->handle($request);
		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringNotContainsStringIgnoringCase('in corso', $response->getBody());
		$this->assertStringNotContainsStringIgnoringCase('ammesso/a', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('respinta', $response->getBody());
		$this->assertStringNotContainsStringIgnoringCase('sospes', $response->getBody());

		$this->assertStringNotContainsString('status-waiting', $response->getBody());
		$this->assertStringNotContainsString('status-approved', $response->getBody());
		$this->assertStringContainsString('status-rejected', $response->getBody());
		$this->assertStringNotContainsString('status-postponed', $response->getBody());
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}
	}

	private function assertCandidatePageHasPublishedWarning(mixed $id, string $candidateName = 'Test'): ResponseInterface
	{
		$request = ServerRequestFactory::fromGlobals(
			['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/candidates.php'],
			['id' => $id],
			[],
			[],
			[]
		);
		$response = (new PageCandidates())->handle($request);

		// Candidate name
		$this->assertStringContainsString($candidateName, $response->getBody());

		// "Results published" warning
		$this->assertStringNotContainsStringIgnoringCase('rimanda nel limbo', $response->getBody());
		$this->assertStringContainsString('Risultati pubblicati', $response->getBody());
		$this->assertStringContainsString('alert', $response->getBody());

		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		return $response;
	}

	private function assertStatusPageHold($id, $token, $message): void
	{
		$request = ServerRequestFactory::fromGlobals(
			['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/candidates.php"],
			['id' => $id, 'token' => $token],
			[],
			[],
			[]
		);
		$response = (new PageStatus())->handle($request);
		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringNotContainsStringIgnoringCase('in corso', $response->getBody());
		$this->assertStringNotContainsStringIgnoringCase('ammesso/a', $response->getBody());
		$this->assertStringNotContainsStringIgnoringCase('respinta', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('sospes', $response->getBody());

		$this->assertStringNotContainsString('status-waiting', $response->getBody());
		$this->assertStringNotContainsString('status-approved', $response->getBody());
		$this->assertStringNotContainsString('status-rejected', $response->getBody());
		$this->assertStringContainsString('status-postponed', $response->getBody());

		$this->assertStringContainsString($message, $response->getBody());

		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}
	}

	private function assertCandidatesPageContains(array $what)
	{
		$request = ServerRequestFactory::fromGlobals(
			['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/candidates.php'],
			[],
			[],
			[],
			[]
		);
		$response = (new PageCandidates())->handle($request);
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}
		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		foreach ($what as $string) {
			$this->assertStringContainsString($string, $response->getBody());
		}
		return $response;
	}

	private function saveNotes($id, string $theNote)
	{
		$request = ServerRequestFactory::fromGlobals(
			['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/candidates.php'],
			['id' => $id],
			['saveNote' => 'true', 'note' => $theNote],
			[],
			[]
		);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(303, $response->getStatusCode(), 'Redirect to same page for notes');
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}
	}

	private function updateNotes($id, string $theNote)
	{
		$request = ServerRequestFactory::fromGlobals(
			['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/candidates.php'],
			['id' => $id],
			['updateNote' => 'true', 'note' => $theNote],
			[],
			[]
		);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(303, $response->getStatusCode(), 'Redirect to same page for notes');
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}
	}

	public function testPublishNowImpossible()
	{
		list($id, $token) = $this->addCandidate();

		$request = ServerRequestFactory::fromGlobals(
			['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/candidates.php'],
			['id' => $id],
			['publishnow' => 'true'],
			[],
			[]
		);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(303, $response->getStatusCode(), 'Redirect to same page');
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		// Candidates page
		$response = $this->assertCandidatesPageContains(['1 da valutare']);
		$this->assertStringContainsString('Test Test', $response->getBody());
	}

	public function testPublishNowApproved()
	{
		list($id, $token) = $this->testApproved();

		$request = ServerRequestFactory::fromGlobals(
			['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/candidates.php'],
			['id' => $id],
			[
				'publishnow' => 'true',
				'subject' => 'WEEElcome',
				'email' => "Hi, you're approved, blah bla blah",
				'recruiter' => "Bob|b0b",
			],
			[],
			[]
		);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(303, $response->getStatusCode(), 'Redirect to same page');
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		// Load the candidates page and check that it is published
		$this->assertCandidatePageHasPublishedWarning($id);

		// Candidate sees approved page
		$this->assertStatusPageApproved($id, $token);
	}

	public function testPublishNowApprovedMissingParts()
	{
		list($id, $token) = $this->testApproved();

		$this->assertSubmitReturnsError($id, [
			'publishnow' => 'true',
			'subject' => 'WEEElcome',
			'recruiter' => "Bob|b0b",
		], 'write an email');

		$this->assertSubmitReturnsError($id, [
			'publishnow' => 'true',
			'subject' => 'WEEElcome',
			'email' => "",
			'recruiter' => "Bob|b0b",
		], 'write an email');

		$this->assertSubmitReturnsError($id, [
			'publishnow' => 'true',
			'email' => "Hi, you're approved, blah bla blah",
			'recruiter' => "Bob|b0b",
		], 'write a subject');

		$this->assertSubmitReturnsError($id, [
			'publishnow' => 'true',
			'subject' => '',
			'email' => "Hi, you're approved, blah bla blah",
			'recruiter' => "Bob|b0b",
		], 'write a subject');

		$this->assertSubmitReturnsError($id, [
			'publishnow' => 'true',
			'subject' => 'WEEElcome',
			'email' => "Hi, you're approved, blah bla blah",
		], 'select a recruiter');

		$this->assertSubmitReturnsError($id, [
			'publishnow' => 'true',
			'subject' => 'WEEElcome',
			'email' => "Hi, you're approved, blah bla blah",
			'recruiter' => "",
		], 'select a recruiter');

		$this->assertSubmitReturnsError($id, [
			'publishnow' => 'true',
			'subject' => 'WEEElcome',
			'email' => "Hi, you're approved, blah bla blah",
			'recruiter' => "invalid",
		], 'select a recruiter');
	}

	public function testPublishNowWithRejectedStatus()
	{
		list($id, $token) = $this->testRejected();

		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], ['status' => 'rejected'], [], []);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(303, $response->getStatusCode(), 'Redirect to same page');
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		// Load the candidates page and check that it is published
		$this->assertCandidatePageHasPublishedWarning($id);

		// Candidate sees rejected page
		$this->assertStatusPageRejected($id, $token);
	}

	public function testUpdateVisibleNotes()
	{
		list($id, $token) = $this->testHold();

		$visibleNotes = 'Updated status: blah blah blah';
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], ['savevisiblenotes' => 'true', 'visiblenotes' => $visibleNotes], [], []);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(303, $response->getStatusCode(), 'Redirect to same page');
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		// Load the candidates page and check that it is still published
		$this->assertCandidatePageHasPublishedWarning($id);

		// New notes appear in the status page
		$this->assertStatusPageHold($id, $token, $visibleNotes);
	}

	private function assertSubmitReturnsError($id, $params, string $msg): ResponseInterface
	{
		$request = ServerRequestFactory::fromGlobals(
			['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/candidates.php'],
			['id' => $id],
			$params,
			[],
			[]
		);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(400, $response->getStatusCode(), 'An error is returned');
		$this->assertStringContainsStringIgnoringCase($msg, $response->getBody());
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		return $response;
	}
}
