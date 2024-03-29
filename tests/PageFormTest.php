<?php


namespace WEEEOpen\WEEEHire\tests;


use WEEEOpen\WEEEHire\Database;
use WEEEOpen\WEEEHire\PageForm;
use Laminas\Diactoros\ServerRequestFactory;

class PageFormTest extends PagesTest {

	/**
	 * @covers \WEEEOpen\WEEEHire\PageForm
	 */
	public function testForm() {
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/form.php'], [], [], [], []);
		$response = (new PageForm())->handle($request);
		$output = $response->getBody();

		$this->assertEquals(200, $response->getStatusCode(), 'Status is 200');
		$this->assertStringContainsString('WEEE Open', $output);
		$this->assertStringContainsString('weee.png', $output);
		$this->assertStringContainsStringIgnoringCase('Nome', $output);
		$this->assertStringContainsStringIgnoringCase('Cognome', $output);
		$this->assertStringContainsStringIgnoringCase('Lettera motivazionale', $output);
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\PageForm
	 */
	public function testFormSubmission() {
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/form.php'], [], [
			'name' => 'Test',
			'surname' => 'Test',
			'degreecourse' => 'Communications And Computer Networks Engineering',
			'year' => '2º Magistrale',
			'matricola' => 's1',
			'area' => 'Sviluppo software PHP',
			'letter' => 'asddasasdasd',
			'mandatorycheckbox_0' => 'true',
		], [], []);
		$response = (new PageForm())->handle($request);

		$this->assertEquals(303, $response->getStatusCode(), 'Redirect to another page');
		$this->assertStringContainsStringIgnoringCase('id=', $response->getHeaderLine('Location'));
		$this->assertStringContainsStringIgnoringCase('token=', $response->getHeaderLine('Location'));
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\PageForm
	 */
	public function testFormSubmissionWithEmailToUs() {
		$db = new Database();
		$db->setConfigValue('notifyEmail', '1');

		$this->testFormSubmission();

		$db->unsetConfigValue('notifyEmail');
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\PageForm
	 */
	public function testFormSubmissionMissingField() {
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/form.php'], [], [
			'name' => 'Test',
			// missing surname
			'degreecourse' => 'Communications And Computer Networks Engineering',
			'year' => '2º Magistrale',
			'matricola' => 's1',
			'area' => 'Sviluppo software PHP',
			'letter' => 'asddasasdasd',
			'mandatorycheckbox_0' => 'true',
		], [], []);
		$response = (new PageForm())->handle($request);

		$this->assertEquals(400, $response->getStatusCode(), 'Bad request');
		$this->assertStringContainsStringIgnoringCase('class="alert alert-danger"', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('Riempi tutti i campi', $response->getBody());
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\PageForm
	 */
	public function testFormSubmissionMissingCheckbox0() {
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/form.php'], [], [
			'name' => 'Test',
			'surname' => 'Test',
			'degreecourse' => 'Communications And Computer Networks Engineering',
			'year' => '2º Magistrale',
			'matricola' => 's1',
			'area' => 'Sviluppo software PHP',
			'letter' => 'asddasasdasd',
		], [], []);
		$response = (new PageForm())->handle($request);

		$this->assertEquals(400, $response->getStatusCode(), 'Bad request');
		$this->assertStringContainsStringIgnoringCase('class="alert alert-danger"', $response->getBody());
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\PageForm
	 */
	public function testFormSubmissionEmptyField() {
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/form.php'], [], [
			'name' => 'Test',
			'surname' => '',
			'degreecourse' => 'Communications And Computer Networks Engineering',
			'year' => '2º Magistrale',
			'matricola' => 's1',
			'area' => 'Sviluppo software PHP',
			'letter' => 'asddasasdasd',
			'mandatorycheckbox_0' => 'true',
		], [], []);
		$response = (new PageForm())->handle($request);

		$this->assertEquals(400, $response->getStatusCode(), 'Bad request');
		$this->assertStringContainsStringIgnoringCase('class="alert alert-danger"', $response->getBody());
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\PageForm
	 */
	public function testFormSubmissionInvalidMatricola() {
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/form.php'], [], [
			'name' => 'Test',
			'surname' => 'Test',
			'degreecourse' => 'Communications And Computer Networks Engineering',
			'year' => '2º Magistrale',
			'matricola' => 'x12345',
			'area' => 'Sviluppo software PHP',
			'letter' => 'asddasasdasd',
			'mandatorycheckbox_0' => 'true',
		], [], []);
		$response = (new PageForm())->handle($request);

		$this->assertEquals(400, $response->getStatusCode(), 'Bad request');
		$this->assertStringContainsStringIgnoringCase('class="alert alert-danger"', $response->getBody());
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\PageForm
	 */
	public function testFormSubmissionDuplicate() {

		$this->testFormSubmission();
		if(session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/form.php'], [], [
			'name' => 'Test2',
			'surname' => 'Test2',
			'degreecourse' => 'Communications And Computer Networks Engineering',
			'year' => '2º Magistrale',
			'matricola' => 's1',
			'area' => 'Sviluppo software Python',
			'letter' => 'boh',
			'mandatorycheckbox_0' => 'true',
		], [], []);
		$response = (new PageForm())->handle($request);

		$this->assertEquals(400, $response->getStatusCode(), 'Bad request');
		$this->assertStringContainsStringIgnoringCase('class="alert alert-danger"', $response->getBody());
		$this->assertStringContainsStringIgnoringCase('Hai già inviato una candidatura con questa matricola', $response->getBody());
	}
}
