<?php


namespace WEEEOpen\WEEEHire\tests;


use WEEEOpen\WEEEHire\Database;
use WEEEOpen\WEEEHire\PageCandidates;
use WEEEOpen\WEEEHire\PageStatus;
use WEEEOpen\WEEEHire\User;
use Zend\Diactoros\ServerRequestFactory;

class PageCandidatesAndPageStatusTest extends PagesTest {
	private function addCandidate(string $id = 's1', string $name = 'Test', string $surname = 'Test') {
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

	public function testEmpty() {
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/candidates.php'], [], [], [], []);
		$response = (new PageCandidates())->handle($request);

		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringContainsStringIgnoringCase('0 candidati totali', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('0 da valutare', $response->getBody());
	}

	public function testNew() {
		list($id, $token) = $this->addCandidate();

		// Status page
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/candidates.php"], ['id' => $id, 'token' => $token], [], [], []);
		$response = (new PageStatus())->handle($request);
		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringContainsStringIgnoringCase('in corso', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('scarica', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('elimina', $response->getBody());
		if(session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		// Candidates page
		$response = $this->assertCandidatesPageContains(['1 da valutare']);
		$this->assertStringContainsString('Test Test', $response->getBody());

		return [$id, $token];
	}

	public function testNewTwice() {
		$this->addCandidate();
		$this->addCandidate('s2', 'Test', 'DellaProva');

		$response = $this->assertCandidatesPageContains(['2 da valutare']);
		$this->assertStringContainsString('Test Test', $response->getBody());
		$this->assertStringContainsString('Test DellaProva', $response->getBody());
	}

	public function testApproved() {
		list($id, $token) = $this->testNew();

		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], ['approve' => 'true', 'notes' => 'notes example os8mieh7saich4rohZ6E'], [], []);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(303, $response->getStatusCode(), 'Redirect to same page');
		if(session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], [], [], []);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringContainsString('Test', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('rimanda nel limbo', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('email', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('note', $response->getBody());
		$this->assertStringContainsString('notes example os8mieh7saich4rohZ6E', $response->getBody());
		if(session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		// Status page
		$this->assertStatusPageWait($id, $token);

		// Candidates list
		$response = $this->assertCandidatesPageContains(['0 da valutare', '1 approvato', '1 da pubblicare']);
		$this->assertStringContainsString('Test Test', $response->getBody());

		return [$id, $token];
	}

	public function testApprovedRevert() {
		list($id, $token) = $this->testApproved();

		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], ['limbo' => 'true', 'notes' => 'notes example os8mieh7saich4rohZ6E'], [], []);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(303, $response->getStatusCode(), 'Redirect to same page');
		if(session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], [], [], []);
		$response = (new PageCandidates())->handle($request);

		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringContainsString('Test', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('approva', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('rifiuta', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('note', $response->getBody());
		if(session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		// Status page
		$this->assertStatusPageWait($id, $token);

		// Candidates list
		$response = $this->assertCandidatesPageContains(['1 da valutare', '0 approvati', '0 da pubblicare']);
		$this->assertStringContainsString('Test Test', $response->getBody());
	}

	public function testRejected() {
		list($id, $token) = $this->addCandidate();

		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], ['reject' => 'true', 'notes' => 'notes example Aew9mahdohjee6chaese'], [], []);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(303, $response->getStatusCode(), 'Redirect to same page');
		if(session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], [], [], []);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringContainsString('Test', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('rimanda nel limbo', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('pubblica', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('note', $response->getBody());
		$this->assertStringContainsString('notes example Aew9mahdohjee6chaese', $response->getBody());
		if(session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		$this->assertStatusPageWait($id, $token);

		$response = $this->assertCandidatesPageContains(['0 da valutare', '0 approvati', '1 rifiutato', '1 da pubblicare']);
		$this->assertStringContainsString('Test Test', $response->getBody());

		// Now publish it!
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], ['publishnow' => 'true', 'notes' => 'notes example uroh7reith8aet6Yoish'], [], []);
		$response = (new PageCandidates())->handle($request);
		$this->assertEquals(303, $response->getStatusCode(), 'Redirect to same page');
		if(session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/candidates.php'], ['id' => $id], [], [], []);
		$response = (new PageCandidates())->handle($request);
		$this->assertStringContainsString('Test', $response->getBody());
		$this->assertStringNotContainsStringIgnoringCase('rimanda nel limbo', $response->getBody());
		$this->assertStringContainsString('Risultati pubblicati', $response->getBody());
		$this->assertStringContainsString('danger', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('note', $response->getBody());
		$this->assertStringContainsString('notes example uroh7reith8aet6Yoish', $response->getBody());
		if(session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		$this->assertStatusPageRejected($id, $token);

		$response = $this->assertCandidatesPageContains(['0 da valutare', '0 approvati', '1 rifiutato', '0 da pubblicare']);
		$this->assertStringContainsString('Test Test', $response->getBody());

		return [$id, $token];
	}

	private function assertStatusPageWait($id, $token): void {
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/candidates.php"],
			['id' => $id, 'token' => $token], [], [], []);
		$response = (new PageStatus())->handle($request);
		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringContainsStringIgnoringCase('in corso', $response->getBody());
		$this->assertStringNotContainsStringIgnoringCase('ammesso/a', $response->getBody());
		$this->assertStringNotContainsStringIgnoringCase('respinta', $response->getBody());
		$this->assertStringNotContainsStringIgnoringCase('rimandat', $response->getBody());

		$this->assertStringContainsString('status-waiting', $response->getBody());
		$this->assertStringNotContainsString('status-approved', $response->getBody());
		$this->assertStringNotContainsString('status-rejected', $response->getBody());
		$this->assertStringNotContainsString('status-postponed', $response->getBody());
		if(session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}
	}

	private function assertStatusPageApproved($id, $token): void {
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/candidates.php"],
			['id' => $id, 'token' => $token], [], [], []);
		$response = (new PageStatus())->handle($request);
		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringNotContainsStringIgnoringCase('in corso', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('ammesso/a', $response->getBody());
		$this->assertStringNotContainsStringIgnoringCase('respinta', $response->getBody());
		$this->assertStringNotContainsStringIgnoringCase('rimandat', $response->getBody());

		$this->assertStringNotContainsString('status-waiting', $response->getBody());
		$this->assertStringContainsString('status-approved', $response->getBody());
		$this->assertStringNotContainsString('status-rejected', $response->getBody());
		$this->assertStringNotContainsString('status-postponed', $response->getBody());
		if(session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}
	}

	private function assertStatusPageRejected($id, $token): void {
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/candidates.php"],
			['id' => $id, 'token' => $token], [], [], []);
		$response = (new PageStatus())->handle($request);
		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringNotContainsStringIgnoringCase('in corso', $response->getBody());
		$this->assertStringNotContainsStringIgnoringCase('ammesso/a', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('respinta', $response->getBody());
		$this->assertStringNotContainsStringIgnoringCase('rimandat', $response->getBody());

		$this->assertStringNotContainsString('status-waiting', $response->getBody());
		$this->assertStringNotContainsString('status-approved', $response->getBody());
		$this->assertStringContainsString('status-rejected', $response->getBody());
		$this->assertStringNotContainsString('status-postponed', $response->getBody());
		if(session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}
	}

	private function assertStatusPageHold($id, $token): void {
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/candidates.php"],
			['id' => $id, 'token' => $token], [], [], []);
		$response = (new PageStatus())->handle($request);
		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringNotContainsStringIgnoringCase('in corso', $response->getBody());
		$this->assertStringNotContainsStringIgnoringCase('ammesso/a', $response->getBody());
		$this->assertStringNotContainsStringIgnoringCase('respinta', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('rimandat', $response->getBody());

		$this->assertStringNotContainsString('status-waiting', $response->getBody());
		$this->assertStringNotContainsString('status-approved', $response->getBody());
		$this->assertStringNotContainsString('status-rejected', $response->getBody());
		$this->assertStringContainsString('status-postponed', $response->getBody());
		if(session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}
	}

	private function assertCandidatesPageContains(array $what) {
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/candidates.php'],
			[], [], [], []);
		$response = (new PageCandidates())->handle($request);
		if(session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}
		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		foreach($what as $string) {
			$this->assertStringContainsString($string, $response->getBody());
		}
		return $response;
	}
}
