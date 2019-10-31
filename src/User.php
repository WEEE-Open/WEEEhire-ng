<?php


namespace WEEEOpen\WEEEHire;


class User {
	public $id;

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
	 * @var $published bool Approval mail sent
	 */
	public $emailed = false;
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
	/**
	 * @var $invitelink string|null Invite link for final registration
	 */
	public $invitelink;

	public function fromPost(array $post) {
		$attrs = ['name', 'surname', 'degreecourse', 'year', 'matricola', 'area', 'letter'];
		foreach($attrs as $attr) {
			if(!isset($post[$attr])) {
				return false;
			}
		}
		foreach($attrs as $attr) {
			$this->$attr = $post[$attr];
		}

		return true;
	}
}
