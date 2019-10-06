<?php


namespace WEEEOpen\WEEEHire;


class User {
	public $uuid;

	public $name;
	public $surname;
	public $degreecourse;
	public $year;
	public $matricola;
	public $area;
	public $letter;

	/**
	 * @var $published bool Result published or not
	 */
	public $published = false;
	/**
	 * @var $status bool|null Approved or rejected
	 */
	public $status = null;
	/**
	 * @var $recruiter string|null Recruiter name
	 */
	public $recruiter = null;
	/**
	 * @var $recruitertg string|null Recruiter Telegram username (without @)
	 */
	public $recruitertg = null;
	/**
	 * @var $submitted int Form submission timestamp
	 */
	public $submitted;
}
