<?php


namespace WEEEOpen\WEEEHire\tests;


use PHPUnit\Framework\TestCase;
use WEEEOpen\WEEEHire\Utils;

class UtilsTest extends TestCase {

	/**
	 * @covers \WEEEOpen\WEEEHire\Utils
	 */
	public function testMailMatricolaS() {
		$mail = Utils::politoMail('s123456');
		$this->assertEquals('s123456@studenti.polito.it', $mail);
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\Utils
	 */
	public function testMailMatricolaD() {
		$mail = Utils::politoMail('d123456');
		$this->assertEquals('d123456@polito.it', $mail);
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\Utils
	 */
	public function testNormalizeCase1() {
		$name = Utils::normalizeCase('mario');
		$this->assertEquals('Mario', $name);
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\Utils
	 */
	public function testNormalizeCase2() {
		$name = Utils::normalizeCase('MARIO');
		$this->assertEquals('Mario', $name);
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\Utils
	 */
	public function testNormalizeCase3() {
		$name = Utils::normalizeCase('Mario');
		$this->assertEquals('Mario', $name);
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\Utils
	 */
	public function testNormalizeCase4() {
		$name = Utils::normalizeCase('DeQualcosa');
		$this->assertEquals('DeQualcosa', $name);
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\Utils
	 */
	public function testNormalizeCase5() {
		$name = Utils::normalizeCase('McIntosh');
		$this->assertEquals('McIntosh', $name);
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\Utils
	 */
	public function testNormalizeCase6() {
		$name = Utils::normalizeCase('place-holder');
		$this->assertEquals('Place-Holder', $name);
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\Utils
	 */
	public function testNormalizeCase7() {
		$name = Utils::normalizeCase('test case');
		$this->assertEquals('Test Case', $name);
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\Utils
	 */
	public function testNormalizeCase8() {
		$name = Utils::normalizeCase('PLACE-HOLDER');
		$this->assertEquals('Place-Holder', $name);
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\Utils
	 */
	public function testNormalizeCase9() {
		$name = Utils::normalizeCase('TEST CASE');
		$this->assertEquals('Test Case', $name);
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\Utils
	 */
	public function testNormalizeCase10() {
		$name = Utils::normalizeCase('TEST Case');
		$this->assertEquals('TEST Case', $name);
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\Utils
	 */
	public function testNormalizeCase11() {
		$name = Utils::normalizeCase('TEST CaSE');
		$this->assertEquals('TEST CaSE', $name);
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\Utils
	 */
	public function testNormalizeCase12() {
		$name = Utils::normalizeCase('L\'ollone');
		$this->assertEquals('L\'ollone', $name);
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\Utils
	 */
	public function testNormalizeCase13() {
		$name = Utils::normalizeCase('l\'ollone');
		$this->assertEquals('L\'Ollone', $name);
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\Utils
	 */
	public function testNormalizeCase14() {
		$name = Utils::normalizeCase('L\'OLLONE');
		$this->assertEquals('L\'Ollone', $name);
	}

	/**
	 * @covers \WEEEOpen\WEEEHire\Utils
	 */
	public function testNormalizeCase15() {
		$name = Utils::normalizeCase('L\'Ollone');
		$this->assertEquals('L\'Ollone', $name);
	}
}
