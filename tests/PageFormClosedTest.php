<?php


namespace WEEEOpen\WEEEHire\tests;


use WEEEOpen\WEEEHire\Database;
use WEEEOpen\WEEEHire\PageForm;
use WEEEOpen\WEEEHire\PageIndex;
use Laminas\Diactoros\ServerRequestFactory;

class PageFormClosedTest extends PagesTest {

	protected function setUp(): void {
		parent::setUp();
		$db = new Database();
		$db->setConfigValue('expiry', '1000');
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\PageIndex
	 */
	public function testIndex() {
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/'], [], [], [], []);
		$response = (new PageIndex())->handle($request);
		$output = $response->getBody();

		$this->assertEquals(200, $response->getStatusCode(), 'Status is 200');
		$this->assertStringContainsStringIgnoringCase('candidature chiuse', $output);
		$this->assertStringContainsString('WEEE Open', $output);
		$this->assertStringContainsString('weee.png', $output);
		$this->assertStringNotContainsStringIgnoringCase('Inizia', $output);
		$this->assertStringNotContainsStringIgnoringCase('Begin', $output);
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\PageForm
	 */
	public function testForm() {
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/form.php'], [], [], [], []);
		$response = (new PageForm())->handle($request);
		$output = $response->getBody();

		$this->assertEquals(200, $response->getStatusCode(), 'Status is 200 (some pa)');
		$this->assertStringContainsString('WEEE Open', $output);
		$this->assertStringContainsString('weee.png', $output);
		$this->assertStringContainsStringIgnoringCase('candidature chiuse', $output);
		$this->assertStringNotContainsStringIgnoringCase('Nome', $output);
		$this->assertStringNotContainsStringIgnoringCase('Cognome', $output);
		$this->assertStringNotContainsStringIgnoringCase('Lettera motivazionale', $output);
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\PageForm
	 */
	public function testFormSubmission() {
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/form.php'], [
			'name' => 'Test',
			'surname' => 'Test',
			'degreecourse' => 'Communications And Computer Networks Engineering',
			'year' => '2º Magistrale',
			'matricola' => 's1',
			'area' => 'Sviluppo software PHP',
			'letter' => 'asddasasdasd',
			'mandatorycheckbox_1' => 'true',
			'mandatorycheckbox_0' => 'true',
		], [], [], []);
		$response = (new PageForm())->handle($request);
		$output = $response->getBody();

		$this->assertEquals(400, $response->getStatusCode(), 'Bad request');
		$this->assertStringContainsStringIgnoringCase('candidature chiuse', $output);
		$this->assertStringNotContainsStringIgnoringCase('Nome', $output);
		$this->assertStringNotContainsStringIgnoringCase('Cognome', $output);
		$this->assertStringNotContainsStringIgnoringCase('Lettera motivazionale', $output);
	}


}
