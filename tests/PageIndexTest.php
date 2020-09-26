<?php


namespace WEEEOpen\WEEEHire\tests;


use WEEEOpen\WEEEHire\PageIndex;
use Laminas\Diactoros\ServerRequestFactory;

class PageIndexTest extends PagesTest {

	/**
	 * @covers \WEEEOpen\WEEEHire\PageIndex
	 */
	public function testIndex() {
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/'], [], [], [], []);
		$response = (new PageIndex())->handle($request);
		$output = $response->getBody();

		$this->assertEquals(200, $response->getStatusCode(), 'Status is 200');
		$this->assertStringContainsString('WEEE Open', $output);
		$this->assertStringContainsString('weee.png', $output);
		$this->assertStringContainsStringIgnoringCase('Inizia', $output);
		$this->assertStringContainsStringIgnoringCase('Begin', $output);
	}
}
