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
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/candidates.php'], [], [], [], []);
		$response = (new PageCandidates())->handle($request);

		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringContainsString('Test Test', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('1 da valutare', $response->getBody());
	}

	public function testNewTwice() {
		$this->addCandidate();
		$this->addCandidate('s2', 'Test', 'DellaProva');

		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/candidates.php'], [], [], [], []);
		$response = (new PageCandidates())->handle($request);

		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringContainsString('Test Test', $response->getBody());
		$this->assertStringContainsString('Test DellaProva', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('2 da valutare', $response->getBody());
	}

	public function testApproved() {
		list($id, $token) = $this->addCandidate();

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
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/candidates.php"], ['id' => $id, 'token' => $token], [], [], []);
		$response = (new PageStatus())->handle($request);
		$this->assertEquals(200, $response->getStatusCode(), '200 OK');
		$this->assertStringContainsStringIgnoringCase('in corso', $response->getBody());
		$this->assertStringContainsString('status-waiting', $response->getBody());
		$this->assertStringNotContainsString('status-approved', $response->getBody());
		$this->assertStringNotContainsString('status-rejected', $response->getBody());
		$this->assertStringNotContainsString('status-postponed', $response->getBody());
		if(session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}
	}
}
