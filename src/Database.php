<?php

namespace WEEEOpen\WEEEHire;

use DateTime;
use DateTimeZone;
use Exception;
use SQLite3;
use SQLite3Result;

use function Couchbase\defaultDecoder;

class Database
{
	private $db;

	public function __construct()
	{
		$this->db = new SQLite3(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'weeehire.db', SQLITE3_OPEN_READWRITE);
	}

	/**
	 * Insert a new User into the database
	 *
	 * @param User $user The user
	 *
	 * @return array ID and token, in this order
	 * @throws Exception If random token cannot be generated
	 */
	public function addUser(User $user): array
	{
		$token = bin2hex(random_bytes(10));

		$stmt = $this->db->prepare('INSERT INTO users (token, name, surname, degreecourse, year, matricola, area, letter, submitted) VALUES (:token, :namep, :surname, :degreecourse, :yearp, :matricola, :area, :letter, :submitted)');
		$stmt->bindValue(':token', password_hash($token, PASSWORD_DEFAULT), SQLITE3_TEXT);
		$this->bindUserParameters($user, $stmt);
		$stmt->bindValue(':submitted', $user->submitted);
		// @ because it prints a warning and causes PHPUnit to complain
		if (!@$stmt->execute()) {
			if ($this->db->lastErrorCode() === 19 && stristr($this->db->lastErrorMsg(), 'matricola')) {
				throw new DuplicateUserException();
			} else {
				throw new DatabaseException();
			}
		}
		$id = $this->db->lastInsertRowID();

		return [$id, $token];
	}

	/**
	 * Get a User from the database
	 *
	 * @param string $id User ID
	 *
	 * @return User|null User or null if not found
	 */
	public function getUser(string $id): ?User
	{
		$stmt = $this->db->prepare('SELECT id, name, surname, degreecourse, year, matricola, area, letter, published, status,
                                                 hold, recruiter, recruitertg, submitted, notes, emailed, invitelink, visiblenotes,
                                                 (
                                                    SELECT MAX(prev_users.id) 
                                                    FROM users as prev_users
                                                    WHERE prev_users.id < :id AND 
                                                          prev_users.id NOT IN (
                                                                                SELECT prev_evaluation.ref_user_id
                                                                                FROM evaluation as prev_evaluation
                                                                                WHERE prev_evaluation.ref_user_id < :id
                                                                                )
                                                 ) AS prev_not_evaluated_user,
                                                 (
                                                    SELECT MAX(prev_users.id)
                                                    FROM users as prev_users
                                                    WHERE prev_users.id < :id
                                                 ) AS prev_user,
                                                 (
                                                    SELECT MIN(next_users.id)
                                                    FROM users as next_users
                                                    WHERE next_users.id > :id
                                                 ) AS next_user,
                                                 (
                                                    SELECT MIN(next_users.id) 
                                                    FROM users as next_users
                                                    WHERE next_users.id > :id AND 
                                                          next_users.id NOT IN (
                                                                                SELECT next_evaluation.ref_user_id
                                                                                FROM evaluation as next_evaluation
                                                                                WHERE next_evaluation.ref_user_id > :id
                                                                                )
                                                 ) AS next_not_evaluated_user
                                                 FROM users WHERE id = :id LIMIT 1');
		$stmt->bindValue(':id', $id, SQLITE3_TEXT);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$result->finalize();
		if ($row === false) {
			return null;
		}
		$user = new User();
		foreach (
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
				'visiblenotes',
				'emailed',
				'invitelink',
				'prev_user',
				'next_user',
				'prev_not_evaluated_user',
				'next_not_evaluated_user'
			] as $attr
		) {
			$user->$attr = $row[$attr];
		}
		$user->published = (bool) $user->published;
		$user->hold = (bool) $user->hold;
		$user->emailed = (bool) $user->emailed;
		$user->status = $user->status === null ? null : (bool) $user->status;
		$user->invitelink = $user->invitelink === null ? null : $user->invitelink;

		return $user;
	}

	/**
	 * Get a value from the config table
	 *
	 * @param string $option Key for the value
	 *
	 * @return null|string Value
	 */
	public function getConfigValue(string $option)
	{
		$stmt = $this->db->prepare("SELECT value FROM config WHERE id = :id");
		$stmt->bindValue(':id', $option, SQLITE3_TEXT);
		$result = $stmt->execute();
		if ($result instanceof SQLite3Result) {
			$row = $result->fetchArray(SQLITE3_ASSOC);
			$result->finalize();

			if ($row === false) {
				throw new DatabaseException("Config value $option not found");
			}

			return $row['value'];
		} else {
			throw new DatabaseException();
		}
	}

	/**
	 * Delete a value from the config table
	 *
	 * @param string $option Key for the value
	 */
	public function unsetConfigValue(string $option)
	{
		$stmt = $this->db->prepare('UPDATE config SET value = null WHERE id = :id');
		$stmt->bindValue(':id', $option, SQLITE3_TEXT);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Set a value in the config table
	 *
	 * @param string $option Key for the value
	 * @param string $value Value to set
	 */
	public function setConfigValue(string $option, string $value)
	{
		$stmt = $this->db->prepare('UPDATE config SET value = :value WHERE id = :id');
		$stmt->bindValue(':value', $value, SQLITE3_TEXT);
		$stmt->bindValue(':id', $option, SQLITE3_TEXT);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Get evaluation (votes) for a candidate
	 *
	 * @param int $userId User ID
	 *
	 * @return array Array of associative arrays, one for each vote
	 */
	public function getEvaluation(int $userId)
	{
		$stmt = $this->db->prepare("SELECT id_evaluation, ref_user_id, id_evaluator, desc_evaluator, date, vote FROM evaluation WHERE ref_user_id = :id");
		$stmt->bindValue(':id', $userId, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if ($result instanceof SQLite3Result) {
			$compact = [];
			while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
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

	/**
	 * Set evaluation for a candidate, by a recruiter
	 *
	 * @param int $userId User ID
	 * @param string $idEvaluator ID of the evaluator/recruiter (LDAP uid attribute)
	 * @param string $descEvaluator Evaluator/recruiter description (LDAP cn attribute, aka full name)
	 * @param int $vote Vote
	 */
	public function setEvaluation(int $userId, string $idEvaluator, string $descEvaluator, int $vote)
	{
		$stmt = $this->db->prepare("INSERT INTO evaluation (ref_user_id, id_evaluator, desc_evaluator, date, vote) VALUES (:id_user, :id_eval, :desc_eval, :time, :vote)");
		$stmt->bindValue(':id_user', $userId, SQLITE3_INTEGER);
		$stmt->bindValue(':id_eval', $idEvaluator, SQLITE3_TEXT);
		$stmt->bindValue(':desc_eval', $descEvaluator, SQLITE3_TEXT);
		$stmt->bindValue(':time', time(), SQLITE3_INTEGER);
		$stmt->bindValue(':vote', $vote, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Remove an evaluation done by a recruiter for a candidate
	 *
	 * @param int $id Evaluation ID
	 */
	public function removeEvaluation(int $id)
	{
		$stmt = $this->db->prepare("DELETE FROM evaluation WHERE id_evaluation = :id");
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Check that a token is valid
	 *
	 * @param int $id User ID
	 * @param string $token Token
	 *
	 * @return bool Valid or not
	 */
	public function validateToken(int $id, string $token): bool
	{
		$stmt = $this->db->prepare('SELECT token FROM users WHERE id = :id LIMIT 1');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if ($result instanceof SQLite3Result) {
			$row = $result->fetchArray(SQLITE3_ASSOC);
			$result->finalize();

			return $row !== false && password_verify($token, $row['token']);
		} else {
			throw new DatabaseException();
		}
	}

	/**
	 * Completely delete a user
	 *
	 * @param int $id User ID
	 */
	public function deleteUser(int $id)
	{
		$stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Get all users for the candidates table
	 *
	 * @param string $username Evaluator username
	 *
	 * @return array Array of associative arrays
	 */
	public function getAllUsersForTable(string $username)
	{
		$votes = $this->getAllEvaluationsAverage();

		$stmt = $this->db->prepare('SELECT id, name, surname, area, recruiter, published, status, submitted, hold, evaluation.vote AS myvote
FROM users
LEFT JOIN evaluation ON ref_user_id=id AND evaluation.id_evaluator=:user
ORDER BY submitted DESC');
		$stmt->bindValue(':user', $username, SQLITE3_TEXT);
		$result = $stmt->execute();

		$compact = [];
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$compact[] = [
				'id'         => $row['id'],
				'name'       => $row['name'] . ' ' . $row['surname'],
				'area'       => $row['area'],
				'recruiter'  => $row['recruiter'],
				'hold'       => (bool) $row['hold'],
				'published'  => (bool) $row['published'],
				'myvote'     => $row['myvote'] === null ? null : (int) $row['myvote'],
				'status'     => $row['status'] === null ? null : (bool) $row['status'],
				'submitted'  => $row['submitted'],
				'evaluation' => $votes[$row['id']] ?? null,
			];
		}

		return $compact;
	}

	/**
	 * Save notes.
	 *
	 * @param int $id User ID
	 * @param string $note Notes
     * @param string $type
	 */
	public function saveNotes(int $id, string $note, string $type = 'candidate')
	{
		$stmt = $this->db->prepare('INSERT INTO notes (uid, candidate_id, note, type) VALUES ( :uid, :id, :note, :type )');
		$uid = $_SESSION['uid'];

		$stmt->bindValue(':uid', $uid, SQLITE3_TEXT);
		$stmt->bindValue(':type', $type, SQLITE3_TEXT);
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		if ($note === '') {
			$stmt->bindValue(':note', null, SQLITE3_NULL);
		} else {
			$stmt->bindValue(':note', $note, SQLITE3_TEXT);
		}
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Update note.
	 *
	 * @param int $id User ID
	 * @param string $note Notes
     * @param string $type
     */
	public function updateNote(int $id, string $note, string $type = 'candidate')
	{
		$stmt = $this->db->prepare('UPDATE notes SET note=:note, updated_at=:updated_at WHERE candidate_id=:id AND uid=:uid AND type=:type');
		$uid = $_SESSION['uid'];

		$stmt->bindValue(':uid', $uid, SQLITE3_TEXT);
        $stmt->bindValue(':type', $type, SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(':updated_at', date('Y-m-d H:i:s'), SQLITE3_TEXT);
		if ($note === '') {
			$stmt->bindValue(':note', null, SQLITE3_NULL);
		} else {
			$stmt->bindValue(':note', $note, SQLITE3_TEXT);
		}
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Retrieve notes beside on candidate id
	 *
	 * @param $candidateId
     * @param string $type
	 * @return array
	 */
	public function getNotesByCandidateId($candidateId, string $type = 'candidate')
	{
		$stmt = $this->db->prepare('SELECT * FROM notes WHERE candidate_id=:id AND type=:type');
		$stmt->bindValue(':id', $candidateId, SQLITE3_INTEGER);
		$stmt->bindValue(':type', $type, SQLITE3_TEXT);
		$result = $stmt->execute();

		$compact = [];
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$compact[] = [
				'id'           => $row['id'],
				'uid'          => $row['uid'],
				'candidate_id' => $row['candidate_id'],
				'note'         => $row['note'],
				'created_at'   => $row['created_at'],
				'updated_at'   => $row['updated_at']
			];
		}

		return $compact;
	}

	/**
	 * Save the visible notes.
	 *
	 * @param int $id User ID
	 * @param string $notes Notes
	 */
	public function saveVisibleNotes(int $id, string $notes)
	{
		$stmt = $this->db->prepare('UPDATE users SET visiblenotes = :notes WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		if ($notes === '') {
			$stmt->bindValue(':notes', null, SQLITE3_NULL);
		} else {
			$stmt->bindValue(':notes', $notes, SQLITE3_TEXT);
		}
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Set candidate status
	 *
	 * @param int $id User ID
	 * @param bool|null $status True for approved, False for rejected, null to unset
	 * @param string|null $recruiter Recruiter that made this difficult choice
	 */
	public function setStatus(int $id, ?bool $status, ?string $recruiter)
	{
		$stmt = $this->db->prepare('UPDATE users SET status = :statusp, recruiter = :recruiter WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		if ($status === null) {
			$stmt->bindValue(':statusp', null, SQLITE3_NULL);
		} else {
			$stmt->bindValue(':statusp', (int) $status, SQLITE3_INTEGER);
		}
		if ($recruiter === null) {
			$stmt->bindValue(':recruiter', null, SQLITE3_NULL);
		} else {
			$stmt->bindValue(':recruiter', $recruiter, SQLITE3_TEXT);
		}
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Set the "hold" status of candidates (waiting list)
	 *
	 * @param int $id User ID
	 * @param bool $hold True to put on hold, false to not put on hold
	 */
	public function setHold(int $id, bool $hold)
	{
		$stmt = $this->db->prepare('UPDATE users SET hold = :hold WHERE id = :id');
		$stmt->bindValue(':id', (int) $id, SQLITE3_INTEGER);
		$stmt->bindValue(':hold', $hold, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Set if status is published and visible or not
	 *
	 * @param int $id User ID
	 * @param bool $published True for published and visible, false for not published (only admins can see it)
	 * @see setStatus
	 */
	public function setPublished(int $id, bool $published)
	{
		$stmt = $this->db->prepare('UPDATE users SET published = :pub WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(':pub', (int) $published, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Set recruiter for a user
	 *
	 * @param int $id User ID
	 * @param string $name Recruiter full name
	 * @param string $tgid Recruiter Telegram nickname
	 */
	public function setRecruiter(int $id, string $name, string $tgid)
	{
		$stmt = $this->db->prepare('UPDATE users SET recruiter = :recruiter, recruitertg = :tgid WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(':recruiter', $name, SQLITE3_TEXT);
		$stmt->bindValue(':tgid', $tgid, SQLITE3_TEXT);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Set invite link for a candidate. Use after generating such a link.
	 *
	 * @param int $id User ID
	 * @param string $invite Invite link
	 */
	public function setInviteLink(int $id, string $invite)
	{
		$stmt = $this->db->prepare('UPDATE users SET invitelink = :invite WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(':invite', $invite, SQLITE3_TEXT);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Set candidate as "emailed" or not, when approved.
	 *
	 * @param int $id User ID
	 * @param bool $emailed The email has been sent or not
	 */
	public function setEmailed(int $id, bool $emailed)
	{
		$stmt = $this->db->prepare('UPDATE users SET emailed = :emailed WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(':emailed', (int) $emailed, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Update candidates personal information
	 *
	 * @param User $user User, the ID is taken from there
	 */
	public function updateUser(User $user)
	{
//		$stmt = $this->db->prepare('UPDATE users SET name = :namep, surname = :surname, degreecourse = :degreecourse, year = :yearp, matricola = :matricola, area = :area, letter = :letter WHERE id = :id');
//		$this->bindUserParameters($user, $stmt);
//		$result = $stmt->execute();
//		if ($result === false) {
//			throw new DatabaseException();
//		}
	}

	/**
	 * Publish all rejected candidates
	 */
	public function publishRejected()
	{
		$result = $this->db->query('UPDATE users SET published = 1 WHERE status = 0');
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Delete candidates older than X days, if they are published
	 *
	 * @param int $days Days
	 * @param bool $deleteHold Also delete candidates put on hold (default false)
	 */
	public function deleteOlderThan(int $days, bool $deleteHold = false)
	{
		if ($deleteHold) {
			$stmt = $this->db->prepare("DELETE FROM users WHERE published = 1 AND strftime('%s','now') - submitted >= :diff");
		} else {
			$stmt = $this->db->prepare("DELETE FROM users WHERE published = 1 AND hold = 0 AND strftime('%s','now') - submitted >= :diff");
		}
		$stmt->bindValue(':diff', $days * 24 * 60 * 60, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Get details on a single interview
	 *
	 * @param string $id User ID
	 *
	 * @return Interview|null Interview or null if user does not exist
	 */
	public function getInterview(string $id): ?Interview
	{
		$stmt = $this->db->prepare('SELECT interview, interviewer, hold, interviewertg, notes AS questions, answers, interviewstatus FROM users WHERE id = :id LIMIT 1');
		$stmt->bindValue(':id', $id, SQLITE3_TEXT);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$result->finalize();
		if ($row === false) {
			return null;
		}
		$interview = new Interview();
		$interview->id = (int) $id;
		$interview->recruiter = $row['interviewer'];
		$interview->hold = $row['hold'];
		$interview->recruitertg = $row['interviewertg'];
		if ($row['interview'] === null) {
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

	/**
	 * Set date and time for an interview
	 *
	 * @param int $id User ID
	 * @param string|null $recruiter Recruiter full name
	 * @param string|null $recruitertg Recruiter Telegram nickname
	 * @param DateTime|null $when When the interview is scheduled
	 */
	public function setInterviewSchedule(int $id, ?string $recruiter, ?string $recruitertg, ?DateTime $when)
	{
		$stmt = $this->db->prepare('UPDATE users SET interview = :interview, interviewer = :interviewer, interviewertg = :interviewertg WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(
			':interview',
			$when === null ? null : $when->getTimestamp(),
			$when === null ? SQLITE3_NULL : SQLITE3_TEXT
		);
		$stmt->bindValue(':interviewer', $recruiter, $when === null ? SQLITE3_NULL : SQLITE3_TEXT);
		$stmt->bindValue(':interviewertg', $recruitertg, $when === null ? SQLITE3_NULL : SQLITE3_TEXT);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Set data for an interview
	 *
	 * @param int $id User ID
	 * @param string|null $questions Questions to ask and notes
	 * @param string|null $answers Answers given by the candidate and comments
	 */
	public function setInterviewData(int $id, ?string $questions, ?string $answers)
	{
		$stmt = $this->db->prepare('UPDATE users SET notes = :q, answers = :a WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(':q', $questions, $questions === null ? SQLITE3_NULL : SQLITE3_TEXT);
		$stmt->bindValue(':a', $answers, $answers === null ? SQLITE3_NULL : SQLITE3_TEXT);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Set interview status. Yeah.
	 *
	 * @param int $id User ID
	 * @param bool|null $status True for approved, false for rejected, null to unset
	 */
	public function setInterviewStatus(int $id, ?bool $status)
	{
		$stmt = $this->db->prepare('UPDATE users SET interviewstatus = :statusp WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		if ($status === null) {
			$stmt->bindValue(':statusp', null, SQLITE3_NULL);
		} else {
			$stmt->bindValue(':statusp', (int) $status, SQLITE3_INTEGER);
		}
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Get all interviews for the tables in the interview page
	 *
	 * @return array Array of associative arrays
	 */
	public function getAllInterviewsForTable()
	{
		$dtz = new DateTimeZone('Europe/Rome');
		$result = $this->db->query('SELECT id, name, surname, area, interviewer, recruiter, interview, hold, interviewstatus, IFNULL(LENGTH(answers), 0) as al, IFNULL(LENGTH(invitelink), 0) as il FROM users WHERE status >= 1 AND published >= 1 ORDER BY interview DESC, surname, name');
		$compact = [];
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			if ($row['interview'] === null) {
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
				'hold'            => $row['hold'],
				'recruiter'       => $row['recruiter'],
				'interviewstatus' => $row['interviewstatus'] === null ? null : (bool) $row['interviewstatus'],
				'answers'         => (bool) $row['al'],
				'when'            => $when,
				'invite'          => (bool) $row['il']
			];
		}

		return $compact;
	}

	/**
	 * Get all assigned (to a recruiter) interviews for the tables in the interview page
	 *
	 * @return array Array of associative arrays
	 */
	public function getAllAssignedInterviewsForTable()
	{
		$dtz = new DateTimeZone('Europe/Rome');
		$result = $this->db->query('SELECT id, name, surname, area, interviewer, interview, interviewstatus AS status FROM users WHERE status >= 1 AND published >= 1 AND interviewer IS NOT NULL and interview IS NOT NULL ORDER BY interviewer, interview, surname, name');
		$compact = [];

		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$dt = $this->timestampToTime((int) $row['interview'], $dtz);

			if (!isset($compact[$row['interviewer']])) {
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
	 * Convert timestamp to a DateTime
	 *
	 * @param int $timestamp Unix Timestamp
	 * @param DateTimeZone|null $dtz Timezone, null for default
	 *
	 * @return DateTime
	 */
	private function timestampToTime(int $timestamp, ?DateTimeZone $dtz = null): DateTime
	{
		$dtz = $dtz ?? new DateTimeZone('Europe/Rome');
		/** @noinspection PhpUnhandledExceptionInspection */
		$dt = new DateTime('now', $dtz);
		$dt->setTimestamp($timestamp);

		return $dt;
	}

	/**
	 * Get evaluations for all users, averaged
	 *
	 * @return array Array with "User ID => evaluation", evaluation is a float
	 */
	private function getAllEvaluationsAverage()
	{
		$result = $this->db->query('SELECT ref_user_id AS id, AVG(vote) AS vote FROM evaluation GROUP BY ref_user_id');

		$averages = [];
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$averages[$row['id']] = (float) $row['vote'];
		}

		return $averages;
	}

	/**
	 * @param User|null $user
	 * @param \SQLite3Stmt $stmt
	 */
	private function bindUserParameters(?User $user, \SQLite3Stmt $stmt): void
	{
		$stmt->bindValue(':id', $user->id, SQLITE3_INTEGER);
		$stmt->bindValue(':namep', $user->name, SQLITE3_TEXT);
		$stmt->bindValue(':surname', $user->surname, SQLITE3_TEXT);
		$stmt->bindValue(':degreecourse', $user->degreecourse, SQLITE3_TEXT);
		$stmt->bindValue(':yearp', $user->year, SQLITE3_TEXT);
		$stmt->bindValue(':matricola', $user->matricola, SQLITE3_TEXT);
		$stmt->bindValue(':area', $user->area, SQLITE3_TEXT);
		$stmt->bindValue(':letter', $user->letter, SQLITE3_TEXT);
	}
}
