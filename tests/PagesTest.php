<?php

namespace WEEEOpen\WEEEHire;

use PHPUnit\Framework\TestCase;

class PagesTest extends TestCase {
	public static function setUpBeforeClass(): void {
		// Ensure that the form is open
		require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
		$db = new Database();
		$db->unsetConfigValue('expiry');
		$db->unsetConfigValue('rolesUnavailable');
	}

	protected function assertPreConditions(): void {
		$this->assertTrue(defined('TEST_MODE'), 'TEST_MODE is defined');
		$this->assertTrue(TEST_MODE, 'TEST_MODE is enalbed');
	}

	protected function setUp(): void {
		session_start();
		$_SESSION['locale'] = 'it-it';
		session_write_close();
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\User
	 */
	public function testIndex() {
		$_SERVER['REQUEST_URI'] = '/';

		ob_start();
		require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php';
		$output = ob_get_clean();

		$this->assertStringContainsStringIgnoringCase('WEEE Open', $output);
		$this->assertStringContainsStringIgnoringCase('weee.png', $output);
		$this->assertStringContainsStringIgnoringCase('Inizia', $output);
		$this->assertStringContainsStringIgnoringCase('Begin', $output);
	}
}
