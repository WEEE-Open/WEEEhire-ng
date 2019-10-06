<?php


namespace WEEEOpen\WEEEHire;


class User {
	public $uuid;

	public $name;
	public $surname;
	public $studycourse;
	public $year;
	public $matricola;
	public $area;
	public $letter;

	/**
	 * @var $published bool Result published or not
	 */
	public $published;
	/**
	 * @var $status bool|null Approved or rejected
	 */
	public $status;
	/**
	 * @var $recruiter string|null Recruiter name
	 */
	public $recruiter;
	/**
	 * @var $recruitertg string|null Recruiter Telegram username (without @)
	 */
	public $recruitertg;
	/**
	 * @var $submitted int Form submission timestamp
	 */
	public $submitted;
}
