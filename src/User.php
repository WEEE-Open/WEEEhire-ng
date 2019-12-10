<?php


namespace WEEEOpen\WEEEHire;


class User {
	const STATUS_NEW = 0;
	const STATUS_NEW_APPROVED = 1;
	const STATUS_NEW_REJECTED = 2;
	const STATUS_NEW_HOLD = 3;
	const STATUS_PUBLISHED_APPROVED = 4;
	const STATUS_PUBLISHED_REJECTED = 5;
	const STATUS_PUBLISHED_HOLD = 6;
	const STATUS_PUBLISHED_REJECTED_HOLD = 7;

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
	/**
	 * @var $hold int Hold for the future
	 */
	public $hold;
	/**
	 * @var $hold|null text Why were you rejected/put on hold
	 */
	public $visiblenotes;

	/**
	 * Create User from POST data.
	 *
	 * @param array $post $_POST
	 *
	 * @return bool True if all attributes are available, false otherwise
	 */
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

	public function getCandidateStatus(): int {
		return $this->computeCandidateStatus($this->published, $this->status, $this->hold);
	}

	public static function computeCandidateStatus(bool $published, ?bool $status, bool $hold): int {
		if($published) {
			if($status === true) {
				return self::STATUS_PUBLISHED_APPROVED;
			} elseif($status === false && $hold === false) {
				return self::STATUS_PUBLISHED_REJECTED;
			} elseif($status === false && $hold === true) {
				return self::STATUS_PUBLISHED_REJECTED_HOLD;
			} elseif($status === null && $hold === true) {
				return self::STATUS_PUBLISHED_HOLD;
			}
		} else {
			if($status === true) {
				return self::STATUS_NEW_APPROVED;
			} elseif($status === false) {
				return self::STATUS_NEW_REJECTED;
			} elseif($hold === true) {
				return self::STATUS_NEW_HOLD;
			}
		}

		return self::STATUS_NEW;
	}
}
