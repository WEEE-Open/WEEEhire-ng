<?php

namespace WEEEOpen\WEEEHire\tests;

use PHPUnit\Framework\TestCase;
use SQLite3;
use WEEEOpen\WEEEHire\Database;

abstract class PagesTest extends TestCase {
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
		if(session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}
		$_SESSION['locale'] = 'it-it';
		session_write_close();
		$db = new SQLite3(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'weeehire.db', SQLITE3_OPEN_READWRITE);
		/** @noinspection SqlWithoutWhere */
		$db->exec("DELETE FROM evaluation");
		/** @noinspection SqlWithoutWhere */
		$db->exec("DELETE FROM users");
	}
}
