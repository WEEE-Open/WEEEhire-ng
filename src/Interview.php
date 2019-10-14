<?php


namespace WEEEOpen\WEEEHire;


class Interview {
	public $id;

	/**
	 * @var $when \DateTime|null Date of the interview
	 */
	public $when = null;

	/**
	 * @var $status bool|null Approved or rejected after the interview
	 */
	public $status = null;
	/**
	 * @var $recruiter string|null Recruiter name
	 */
	public $recruiter = null;
	public $recruitertg = null;
	/**
	 * @var $questions string|null
	 */
	public $questions;
	/**
	 * @var $answers string|null
	 */
	public $answers;
}
