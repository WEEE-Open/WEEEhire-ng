<?php


namespace WEEEOpen\WEEEHire;


use DateTime;
use DateTimeZone;
use Exception;
use SQLite3;
use SQLite3Result;

class Database {
	private $db;

	public function __construct() {
		$this->db = new SQLite3('..' . DIRECTORY_SEPARATOR . 'weeehire.db', SQLITE3_OPEN_READWRITE);
	}

	/**
	 * @param User $user
	 *
	 * @return array ID and token, in this order
	 * @throws Exception If random token cannot be generated
	 */
	public function addUser(User $user): array {
		$token = bin2hex(random_bytes(10));

		$stmt = $this->db->prepare('INSERT INTO users (token, name, surname, degreecourse, year, matricola, area, letter, submitted) VALUES (:token, :namep, :surname, :degreecourse, :yearp, :matricola, :area, :letter, :submitted)');
		$stmt->bindValue(':token', password_hash($token, PASSWORD_DEFAULT), SQLITE3_TEXT);
		$stmt->bindValue(':namep', $user->name, SQLITE3_TEXT);
		$stmt->bindValue(':surname', $user->surname, SQLITE3_TEXT);
		$stmt->bindValue(':degreecourse', $user->degreecourse, SQLITE3_TEXT);
		$stmt->bindValue(':yearp', $user->year, SQLITE3_TEXT);
		$stmt->bindValue(':matricola', $user->matricola, SQLITE3_TEXT);
		$stmt->bindValue(':area', $user->area, SQLITE3_TEXT);
		$stmt->bindValue(':letter', $user->letter, SQLITE3_TEXT);
		$stmt->bindValue(':submitted', $user->submitted);
		if(!$stmt->execute()) {
			if($this->db->lastErrorCode() === 19 && stristr($this->db->lastErrorMsg(), 'matricola')) {
				throw new DuplicateUserException();
			} else {
				throw new DatabaseException();
			}
		}
		$id = $this->db->lastInsertRowID();

		return [$id, $token];
	}

	public function getUser(string $id): ?User {
		$stmt = $this->db->prepare('SELECT id, name, surname, degreecourse, year, matricola, area, letter, published, status, hold, recruiter, recruitertg, submitted, notes, emailed, invitelink FROM users WHERE id = :id LIMIT 1');
		$stmt->bindValue(':id', $id, SQLITE3_TEXT);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$result->finalize();
		if($row === false) {
			return null;
		}
		$user = new User();
		foreach(
			[
				'id',
				'name',
				'surname',
				'degreecourse',
				'year',
				'matricola',
				'area',
				'letter',
				'published',
				'status',
				'hold',
				'recruiter',
				'recruitertg',
				'submitted',
				'notes',
				'emailed',
				'invitelink'
			] as $attr
		) {
			$user->$attr = $row[$attr];
		}
		$user->published = (bool) $user->published;
		$user->emailed = (bool) $user->emailed;
		$user->status = $user->status === null ? null : (bool) $user->status;
		$user->invitelink = $user->invitelink === null ? null : $user->invitelink;

		return $user;
	}

	public function getConfigValue(string $option) {
		$stmt = $this->db->prepare("SELECT value FROM config WHERE id = :id");
		$stmt->bindValue(':id', $option, SQLITE3_TEXT);
		$result = $stmt->execute();
		if($result instanceof SQLite3Result) {
			$row = $result->fetchArray(SQLITE3_ASSOC);
			$result->finalize();

			if($row === false) {
				throw new DatabaseException("Config value $option not found");
			}

			return $row['value'];
		} else {
			throw new DatabaseException();
		}
	}

	public function unsetConfigValue(string $option) {
		$stmt = $this->db->prepare('UPDATE config SET value = null WHERE id = :id');
		$stmt->bindValue(':id', $option, SQLITE3_TEXT);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}

	public function setConfigValue(string $option, string $value) {
		$stmt = $this->db->prepare('UPDATE config SET value = :value WHERE id = :id');
		$stmt->bindValue(':value', $value, SQLITE3_TEXT);
		$stmt->bindValue(':id', $option, SQLITE3_TEXT);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}

	public function getEvaluation(int $userId) {
		$stmt = $this->db->prepare("SELECT id_evaluation, ref_user_id, id_evaluator, desc_evaluator, date, vote FROM evaluation WHERE ref_user_id = :id");
		$stmt->bindValue(':id', $userId, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if($result instanceof SQLite3Result) {
			$compact = [];
			while($row = $result->fetchArray(SQLITE3_ASSOC)) {

				$compact[] = [
					'id_evaluation'  => $row['id_evaluation'],
					'id_user'        => $row['ref_user_id'],
					'id_evaluator'   => $row['id_evaluator'],
					'name_evaluator' => $row['desc_evaluator'],
					'date'           => $row['date'],
					'vote'           => $row['vote'],
				];
			}

			return $compact;

		} else {
			throw new DatabaseException();
		}
	}

	public function setEvaluation(int $userId, string $idEvaluator, string $descEvaluator, int $vote) {
		$stmt = $this->db->prepare("INSERT INTO evaluation (ref_user_id, id_evaluator, desc_evaluator, date, vote) VALUES (:id_user, :id_eval, :desc_eval, :time, :vote)");
		$stmt->bindValue(':id_user', $userId, SQLITE3_INTEGER);
		$stmt->bindValue(':id_eval', $idEvaluator, SQLITE3_TEXT);
		$stmt->bindValue(':desc_eval', $descEvaluator, SQLITE3_TEXT);
		$stmt->bindValue(':time', time(), SQLITE3_INTEGER);
		$stmt->bindValue(':vote', $vote, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}

	public function removeEvaluation(int $id) {
		$stmt = $this->db->prepare("DELETE FROM evaluation WHERE id_evaluation = :id");
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}

	public function validateToken(int $id, string $token): bool {
		$stmt = $this->db->prepare('SELECT token FROM users WHERE id = :id LIMIT 1');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if($result instanceof SQLite3Result) {
			$row = $result->fetchArray(SQLITE3_ASSOC);
			$result->finalize();

			return $row !== false && password_verify($token, $row['token']);
		} else {
			throw new DatabaseException();
		}
	}

	public function deleteUser(int $id) {
		$stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}

	public function getAllUsersForTable() {
		$votes = $this->getAllEvaluationsAverage();

		$result = $this->db->query('SELECT id, name, surname, area, recruiter, published, status, submitted, hold, IFNULL(LENGTH(notes), 0) as notesl FROM users ORDER BY submitted DESC');
		$compact = [];
		while($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$compact[] = [
				'id'         => $row['id'],
				'name'       => $row['name'] . ' ' . $row['surname'],
				'area'       => $row['area'],
				'recruiter'  => $row['recruiter'],
				'hold'       => (bool) $row['hold'],
				'notes'      => (bool) $row['notesl'],
				'published'  => (bool) $row['published'],
				'status'     => $row['status'] === null ? null : (bool) $row['status'],
				'submitted'  => $row['submitted'],
				'evaluation' => $votes[$row['id']] ?? null,
			];
		}

		return $compact;
	}

	public function saveNotes(int $id, string $notes) {
		$stmt = $this->db->prepare('UPDATE users SET notes = :notes WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		if($notes === '') {
			$stmt->bindValue(':notes', null, SQLITE3_NULL);
		} else {
			$stmt->bindValue(':notes', $notes, SQLITE3_TEXT);
		}
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}

	public function setStatus(int $id, ?bool $status, ?string $recruiter) {
		$stmt = $this->db->prepare('UPDATE users SET status = :statusp, recruiter = :recruiter WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		if($status === null) {
			$stmt->bindValue(':statusp', null, SQLITE3_NULL);
		} else {
			$stmt->bindValue(':statusp', (int) $status, SQLITE3_INTEGER);
		}
		if($recruiter === null) {
			$stmt->bindValue(':recruiter', null, SQLITE3_NULL);
		} else {
			$stmt->bindValue(':recruiter', $recruiter, SQLITE3_TEXT);
		}
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}

	public function setHold(int $id, int $hold) {
		$stmt = $this->db->prepare('UPDATE users SET hold = :hold WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(':hold', $hold, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}

	public function setPublished(int $id, bool $published) {
		$stmt = $this->db->prepare('UPDATE users SET published = :pub WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(':pub', (int) $published, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}

	public function setRecruiter(int $id, string $name, string $tgid) {
		$stmt = $this->db->prepare('UPDATE users SET recruiter = :recruiter, recruitertg = :tgid WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(':recruiter', $name, SQLITE3_TEXT);
		$stmt->bindValue(':tgid', $tgid, SQLITE3_TEXT);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}

	public function setInviteLink(int $id, string $invite) {
		$stmt = $this->db->prepare('UPDATE users SET invitelink = :invite WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(':invite', $invite, SQLITE3_TEXT);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}

	public function setEmailed(int $id, bool $emailed) {
		$stmt = $this->db->prepare('UPDATE users SET emailed = :emailed WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(':emailed', (int) $emailed, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}

	public function updateUser(?User $user) {
		$stmt = $this->db->prepare('UPDATE users SET name = :namep, surname = :surname, degreecourse = :degreecourse, year = :yearp, matricola = :matricola, area = :area, letter = :letter WHERE id = :id');
		$stmt->bindValue(':id', $user->id, SQLITE3_INTEGER);
		$stmt->bindValue(':namep', $user->name, SQLITE3_TEXT);
		$stmt->bindValue(':surname', $user->surname, SQLITE3_TEXT);
		$stmt->bindValue(':degreecourse', $user->degreecourse, SQLITE3_TEXT);
		$stmt->bindValue(':yearp', $user->year, SQLITE3_TEXT);
		$stmt->bindValue(':matricola', $user->matricola, SQLITE3_TEXT);
		$stmt->bindValue(':area', $user->area, SQLITE3_TEXT);
		$stmt->bindValue(':letter', $user->letter, SQLITE3_TEXT);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}

	public function publishRejected() {
		$result = $this->db->query('UPDATE users SET published = 1 WHERE status = 0');
		if($result === false) {
			throw new DatabaseException();
		}
	}

	public function deleteOlderThan(int $days, bool $deleteHold = false) {
		if($deleteHold) {
			$stmt = $this->db->prepare("DELETE FROM users WHERE published = 1 AND strftime('%s','now') - submitted >= :diff");
		} else {
			$stmt = $this->db->prepare("DELETE FROM users WHERE published = 1 AND hold = 0 AND strftime('%s','now') - submitted >= :diff");
		}
		$stmt->bindValue(':diff', $days * 24 * 60 * 60, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}


	public function getInterview(string $id): ?Interview {
		$stmt = $this->db->prepare('SELECT interview, interviewer, interviewertg, notes AS questions, answers, interviewstatus FROM users WHERE id = :id LIMIT 1');
		$stmt->bindValue(':id', $id, SQLITE3_TEXT);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$result->finalize();
		if($row === false) {
			return null;
		}
		$interview = new Interview();
		$interview->id = (int) $id;
		$interview->recruiter = $row['interviewer'];
		$interview->recruitertg = $row['interviewertg'];
		if($row['interview'] === null) {
			$interview->when = null;
		} else {
			$dt = $this->timestampToTime((int) $row['interview']);
			$interview->when = $dt;
		}
		$interview->questions = $row['questions'];
		$interview->answers = $row['answers'];
		$interview->status = $row['interviewstatus'] === null ? null : (bool) $row['interviewstatus'];

		return $interview;
	}

	public function setInterviewSchedule(int $id, ?string $recruiter, ?string $recruitertg, ?DateTime $when) {
		$stmt = $this->db->prepare('UPDATE users SET interview = :interview, interviewer = :interviewer, interviewertg = :interviewertg WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(':interview', $when === null ? null : $when->getTimestamp(),
			$when === null ? SQLITE3_NULL : SQLITE3_TEXT);
		$stmt->bindValue(':interviewer', $recruiter, $when === null ? SQLITE3_NULL : SQLITE3_TEXT);
		$stmt->bindValue(':interviewertg', $recruitertg, $when === null ? SQLITE3_NULL : SQLITE3_TEXT);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}

	public function setInterviewData(int $id, ?string $questions, ?string $answers) {
		$stmt = $this->db->prepare('UPDATE users SET notes = :q, answers = :a WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(':q', $questions, $questions === null ? SQLITE3_NULL : SQLITE3_TEXT);
		$stmt->bindValue(':a', $answers, $answers === null ? SQLITE3_NULL : SQLITE3_TEXT);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}

	public function setInterviewStatus(int $id, ?bool $status) {
		$stmt = $this->db->prepare('UPDATE users SET interviewstatus = :statusp WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		if($status === null) {
			$stmt->bindValue(':statusp', null, SQLITE3_NULL);
		} else {
			$stmt->bindValue(':statusp', (int) $status, SQLITE3_INTEGER);
		}
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}

	public function getAllInterviewsForTable() {
		$dtz = new DateTimeZone('Europe/Rome');
		$result = $this->db->query('SELECT id, name, surname, area, interviewer, recruiter, interview, interviewstatus, IFNULL(LENGTH(notes), 0) as ql, IFNULL(LENGTH(answers), 0) as al, IFNULL(LENGTH(invitelink), 0) as il FROM users WHERE status >= 1 AND published >= 1 ORDER BY interview DESC, surname ASC, name ASC');
		$compact = [];
		while($row = $result->fetchArray(SQLITE3_ASSOC)) {
			if($row['interview'] === null) {
				$when = null;
			} else {
				$dt = $this->timestampToTime((int) $row['interview'], $dtz);
				$when = $dt;
			}

			$compact[] = [
				'id'              => $row['id'],
				'name'            => $row['name'] . ' ' . $row['surname'],
				'area'            => $row['area'],
				'interviewer'     => $row['interviewer'],
				'recruiter'       => $row['recruiter'],
				'interviewstatus' => $row['interviewstatus'] === null ? null : (bool) $row['interviewstatus'],
				'questions'       => (bool) $row['ql'],
				'answers'         => (bool) $row['al'],
				'when'            => $when,
				'invite'          => (bool) $row['il'],
			];
		}

		return $compact;
	}

	public function getAllAssignedInterviewsForTable() {
		$dtz = new DateTimeZone('Europe/Rome');
		$result = $this->db->query('SELECT id, name, surname, area, interviewer, interview, interviewstatus AS status FROM users WHERE status >= 1 AND published >= 1 AND interviewer IS NOT NULL and interview IS NOT NULL ORDER BY interviewer ASC, interview ASC, surname ASC, name ASC');
		$compact = [];

		while($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$dt = $this->timestampToTime((int) $row['interview'], $dtz);

			if(!isset($compact[$row['interviewer']])) {
				$compact[$row['interviewer']] = [];
			}
			$compact[$row['interviewer']][] = [
				'id'     => $row['id'],
				'name'   => $row['name'] . ' ' . $row['surname'],
				'area'   => $row['area'],
				'when'   => $dt,
				'status' => $row['status'] === null ? null : (bool) $row['status'],
			];
		}

		return $compact;
	}

	/** @noinspection PhpDocMissingThrowsInspection */
	/**
	 * @param int $timestamp Unix Timestamp
	 *
	 * @param DateTimeZone|null $dtz Timezone, null for default
	 *
	 * @return DateTime
	 */
	private function timestampToTime(int $timestamp, ?DateTimeZone $dtz = null): DateTime {
		$dtz = $dtz ?? new DateTimeZone('Europe/Rome');
		/** @noinspection PhpUnhandledExceptionInspection */
		$dt = new DateTime('now', $dtz);
		$dt->setTimestamp($timestamp);

		return $dt;
	}

	private function getAllEvaluationsAverage() {
		$result = $this->db->query('SELECT ref_user_id AS id, AVG(vote) AS vote FROM evaluation GROUP BY ref_user_id');

		$averages = [];
		while($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$averages[$row['id']] = (float) $row['vote'];
		}

		return $averages;
	}
}
