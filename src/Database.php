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
		$this->db = new SQLite3(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'weeehire.db', SQLITE3_OPEN_READWRITE);
	}

	/**
	 * ⚠️ THIS METHOD IS UNSAFE, NEVER EXPOSE IT, ONLY FOR DB UPGRADE ⚠️
	 * Get the SQLite3 object
	 *
	 * @return SQLite3
	 */

	public function getDb(): SQLite3
	{
		return $this->db;
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
	 * Regenerate token for a user, used for resending email
	 *
	 * @param int $id User ID
	 *
	 * @return string New token
	 */
	public function regenerateToken(int $id): string
	{
		$token = bin2hex(random_bytes(10));
		$stmt = $this->db->prepare('UPDATE users SET token=:token WHERE id=:id');
		$stmt->bindValue(':id', $id, SQLITE3_TEXT);
		$stmt->bindValue(':token', password_hash($token, PASSWORD_DEFAULT), SQLITE3_TEXT);

		if (!@$stmt->execute()) {
			throw new DatabaseException();
		} elseif ($this->db->changes() === 0) {
			throw new Exception('User not found');
		} else {
			return $token;
		}
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
		$stmt = $this->db->prepare(
			'SELECT id, name, surname, degreecourse, year, matricola, area, letter, published, status,
                                                 hold, recruiter, recruitertg, submitted, emailed, invitelink, visiblenotes
                                                 FROM users WHERE id = :id LIMIT 1'
		);
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
			'visiblenotes',
			'emailed',
			'invitelink',
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

	public function getPrevAndNextUser(User $user, string $myself): ?User
	{
		$id = $user->id;
		$stmt = $this->db->prepare(
			'SELECT
                                                 (
                                                    SELECT MAX(prev_users.id) 
                                                    FROM users as prev_users
                                                    WHERE prev_users.id < :id AND 
                                                          prev_users.id NOT IN (
                                                                                SELECT prev_evaluation.ref_user_id
                                                                                FROM evaluation as prev_evaluation
                                                                                WHERE prev_evaluation.ref_user_id < :id
                                                                                AND prev_evaluation.id_evaluator = :self
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
                                                                                AND next_evaluation.id_evaluator = :self
                                                                                )
                                                 ) AS next_not_evaluated_user
                                                 FROM users WHERE id = :id LIMIT 1'
		);
		$stmt->bindValue(':id', $id, SQLITE3_TEXT);
		$stmt->bindValue(':self', $myself, SQLITE3_TEXT);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$result->finalize();
		if ($row === false) {
			return null;
		}
		foreach (
			[
			'prev_user',
			'next_user',
			'prev_not_evaluated_user',
			'next_not_evaluated_user',
			] as $attr
		) {
			$user->$attr = $row[$attr];
		}

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
				throw new DatabaseException("Config value $option not found", 404);
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
	 * @param string $value  Value to set
	 */
	public function setConfigValue(string $option, string $value)
	{
		$stmt = $this->db->prepare('INSERT OR REPLACE INTO config (id, value) VALUES (:id, :value)');
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
	 * @param int    $userId        User ID
	 * @param string $idEvaluator   ID of the evaluator/recruiter (LDAP uid attribute)
	 * @param string $descEvaluator Evaluator/recruiter description (LDAP cn attribute, aka full name)
	 * @param int    $vote          Vote
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
	 * @param int    $id    User ID
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
	 * Get all users for the candidates table with notes and evaluations
	 *
	 * @param string $evaluatorName Evaluator username
	 *
	 * @return array Array of associative arrays
	 */
	public function getUsersWithNotesAndEvaluations(string $evaluatorName): array
	{
		$votes = $this->getAllEvaluationsAverage();

		// there is additional left join in order to get note about user,
		// the reason why there is additional subquery with distinct candidate_id because
		// if I left join and if user has some ( more than one ) notes after join user begin to duplicate
		// in this case I am getting with distinct candidate_id
		$query = 'SELECT users.id, name, surname, area, recruiter, published, status, submitted, hold, evaluation.vote AS myvote, candidate_id AS has_note
                    FROM users
                    LEFT JOIN evaluation ON ref_user_id=users.id AND evaluation.id_evaluator=:user
                    LEFT JOIN ( SELECT DISTINCT candidate_id FROM notes WHERE uid=:user ) ON candidate_id=users.id
                    ORDER BY submitted DESC';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':user', $evaluatorName, SQLITE3_TEXT);
		$result = $stmt->execute();

		$compact = [];
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$compact[] = [
			'id'           => $row['id'],
			'name'         => $row['name'] . ' ' . $row['surname'],
			'area'         => $row['area'],
			'recruiter'    => $row['recruiter'],
			'hold'         => (bool) $row['hold'],
			'published'    => (bool) $row['published'],
			'myvote'       => $row['myvote'] === null ? null : (int) $row['myvote'],
			'status'       => $row['status'] === null ? null : (bool) $row['status'],
			'submitted'    => $row['submitted'],
			'evaluation'   => $votes[$row['id']] ?? null,
			'has_note'     => $row['has_note'], // convert candidate_id to has_note
			];
		}

		return $compact;
	}

	/**
	 * Save notes.
	 *
	 * @param string $author
	 * @param int    $candidate User ID
	 * @param string $note      Notes
	 * @param string $type
	 */
	public function saveNotes(string $author, int $candidate, string $note)
	{
		$stmt = $this->db->prepare('INSERT INTO notes (uid, candidate_id, note, created_at, updated_at) VALUES ( :uid, :id, :note, :now1, :now2 )');

		$stmt->bindValue(':uid', $author, SQLITE3_TEXT);
		$stmt->bindValue(':id', $candidate, SQLITE3_INTEGER);
		$stmt->bindValue(':note', $note, SQLITE3_TEXT);

		$stmt->bindValue(':now1', time(), SQLITE3_INTEGER);
		$stmt->bindValue(':now2', time(), SQLITE3_INTEGER);

		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Update note.
	 *
	 * @param int    $id   User ID
	 * @param string $note Notes
	 * @param string $type
	 */
	public function updateNote(string $author, int $id, ?string $note)
	{
		if ($note === null || $note === '') {
			$stmt = $this->db->prepare('DELETE FROM notes WHERE candidate_id=:id AND uid=:uid');
		} else {
			$stmt = $this->db->prepare('UPDATE notes SET note=:note, updated_at=:updated_at WHERE candidate_id=:id AND uid=:uid');
			$stmt->bindValue(':updated_at', time(), SQLITE3_INTEGER);
			$stmt->bindValue(':note', $note, SQLITE3_TEXT);
		}
		$stmt->bindValue(':uid', $author, SQLITE3_TEXT);
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);

		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Retrieve notes on a candidate
	 *
	 * @param  int    $candidateId
	 * @param  string $type
	 * @return array
	 */
	public function getNotesByCandidateId(int $candidateId)
	{
		$stmt = $this->db->prepare('SELECT uid, candidate_id, note, created_at, updated_at FROM notes WHERE candidate_id = :id ORDER BY updated_at DESC');

		$stmt->bindValue(':id', $candidateId, SQLITE3_INTEGER);
		$result = $stmt->execute();

		$compact = [];
		$dtz = new DateTimeZone('Europe/Rome');
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$row['updated_at'] = $this->timestampToTime((int) $row['updated_at'], $dtz);
			$row['created_at'] = $this->timestampToTime((int) $row['created_at'], $dtz);
			$compact[] = $row;
		}

		return $compact;
	}

	/**
	 * Save the visible notes.
	 *
	 * @param int    $id    User ID
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
	 * @param int         $id        User ID
	 * @param bool|null   $status    True for approved, False for rejected, null to unset
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
	 * @param int  $id   User ID
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
	 * @param int  $id        User ID
	 * @param bool $published True for published and visible, false for not published (only admins can see it)
	 * @see   setStatus
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
	 * @param int    $id   User ID
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
	 * @param int    $id     User ID
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
	 * @param int  $id      User ID
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
		$stmt = $this->db->prepare('UPDATE users SET name = :namep, surname = :surname, degreecourse = :degreecourse, year = :yearp, matricola = :matricola, area = :area, letter = :letter WHERE id = :id');
		$this->bindUserParameters($user, $stmt);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
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
	 * @param int  $days       Days
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
		// safetytestdate
		$stmt = $this->db->prepare('SELECT interview, interviewer, hold, interviewertg, answers, interviewstatus FROM users WHERE id = :id LIMIT 1');
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
		$interview->answers = $row['answers'];
		$interview->status = $row['interviewstatus'] === null ? null : (bool) $row['interviewstatus'];
		if (!isset($row['safetytestdate'])) {
			$interview->safetyTestDate = null;
		} else {
			$dt = $this->timestampToTime((int) $row['safetytestdate']);
			$interview->safetyTestDate = $dt;
		}

		return $interview;
	}

	/**
	 * Set date and time for an interview
	 *
	 * @param int           $id          User ID
	 * @param string|null   $recruiter   Recruiter full name
	 * @param string|null   $recruitertg Recruiter Telegram nickname
	 * @param DateTime|null $when        When the interview is scheduled
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
	 * @param int         $id        User ID
	 * @param string|null $questions Questions to ask and notes
	 * @param string|null $answers   Answers given by the candidate and comments
	 */
	public function setInterviewData(int $id, ?string $answers)
	{
		$stmt = $this->db->prepare('UPDATE users SET answers = :a WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(':a', $answers, $answers === null ? SQLITE3_NULL : SQLITE3_TEXT);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Set interview status. Yeah.
	 *
	 * @param int       $id     User ID
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

	public function setSafetyTestDate(int $id, DateTime $when)
	{
		$stmt = $this->db->prepare('UPDATE users SET safetytestdate = :safetyTestDate WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(':safetyTestDate', $when->getTimestamp(), SQLITE3_INTEGER);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	public function clearSafetyTestDate(int $id)
	{
		$stmt = $this->db->prepare('UPDATE users SET safetytestdate = null WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
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
		// safetyTestDate
		$result = $this->db->query('SELECT id, name, surname, area, interviewer, recruiter, interview, hold, interviewstatus, IFNULL(LENGTH(answers), 0) as al, IFNULL(LENGTH(invitelink), 0) as il FROM users WHERE status >= 1 AND published >= 1 ORDER BY interview DESC, surname, name');
		$compact = [];
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			if ($row['interview'] === null) {
				$when = null;
			} else {
				$dt = $this->timestampToTime((int) $row['interview'], $dtz);
				$when = $dt;
			}

			if (!isset($row['safetytestdate'])) {
				$safetyTestDate = null;
			} else {
				$dt = $this->timestampToTime((int) $row['safetytestdate'], $dtz);
				$safetyTestDate = $dt;
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
			'invite'          => (bool) $row['il'],
			'safetyTestDate'  => $safetyTestDate
			];
		}

		return $compact;
	}

	/**
	 * Get all assigned (to a recruiter) interviews for the tables in the interview page
	 *
	 * @return array Array of associative arrays
	 */
	public function getAllAssignedInterviewsForTable(): array
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

	/**
	 * Get all positions
	 *
	 * @param string $lang Language (optional), if not set, won't provide name or description
	 *
	 * @return array Array of associative arrays with id, availability, printable name and description
	 */
	public function getPositions($lang = null)
	{
		if ($lang) {
			$stmt = $this->db->prepare(
				"SELECT 
					p.id,
					p.idx,
					p.available,
					t_name.value AS name,
					t_summ.value AS summary,
					t_desc.value AS description
				FROM 
					positions p
				LEFT JOIN 
					translations t_name ON t_name.id = 'position.' || p.id || '.name' AND t_name.lang = :lang
				LEFT JOIN 
					translations t_summ ON t_summ.id = 'position.' || p.id || '.summary' AND t_summ.lang = :lang
				LEFT JOIN 
					translations t_desc ON t_desc.id = 'position.' || p.id || '.description' AND t_desc.lang = :lang
				ORDER BY
					p.idx ASC"
			);
			$stmt->bindValue(':lang', $lang, SQLITE3_TEXT);
		} else {
			$stmt = $this->db->prepare('SELECT id, idx, available FROM positions');
		}
		$result = $stmt->execute();

		$positions = [];
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$positions[] = $row;
		}
		return $positions;
	}

	/**
	 * Get available positions
	 *
	 * @param string $lang Language (optional), if not set, won't provide name or description
	 *
	 * @return array Array of associative arrays with id, availability, printable name and description
	 */
	public function getAvailablePositions($lang = null)
	{
		if ($lang) {
			$stmt = $this->db->prepare(
				"SELECT 
					p.id,
					p.idx,
					p.available,
					t_name.value AS name,
					t_summ.value AS summary,
					t_desc.value AS description
				FROM 
					positions p
				LEFT JOIN 
					translations t_name ON t_name.id = 'position.' || p.id || '.name' AND t_name.lang = :lang
				LEFT JOIN 
					translations t_summ ON t_summ.id = 'position.' || p.id || '.summary' AND t_summ.lang = :lang
				LEFT JOIN 
					translations t_desc ON t_desc.id = 'position.' || p.id || '.description' AND t_desc.lang = :lang
				WHERE 
					p.available = 1
				ORDER BY
					p.idx ASC"
			);
			$stmt->bindValue(':lang', $lang, SQLITE3_TEXT);
		} else {
			$stmt = $this->db->prepare('SELECT id, idx, available FROM positions');
		}
		$result = $stmt->execute();

		$positions = [];
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$positions[] = $row;
		}
		return $positions;
	}

	/**
	 * Get a single position
	 *
	 * @param int    $id   Position ID
	 * @param string $lang Language (optional), if not set, won't provide name or description
	 *
	 * @return array Associative array with id, availability, printable name and description
	 */
	public function getPosition($id, $lang = null)
	{
		if ($lang) {
			$stmt = $this->db->prepare(
				"SELECT 
					p.id,
					p.idx,
					p.available,
					t_name.value AS name,
					t_desc.value AS description
				FROM 
					positions p
				LEFT JOIN 
					translations t_name ON t_name.id = 'position.' || p.id || '.name' AND t_name.lang = :lang
				LEFT JOIN 
					translations t_desc ON t_desc.id = 'position.' || p.id || '.description' AND t_desc.lang = :lang
				WHERE
					p.id = :id"
			);
			$stmt->bindValue(':lang', $lang, SQLITE3_TEXT);
		} else {
			$stmt = $this->db->prepare('SELECT id, idx, available FROM positions WHERE id = :id');
		}
		$stmt->bindValue(':id', $id, SQLITE3_TEXT);
		$result = $stmt->execute();

		return $result->fetchArray(SQLITE3_ASSOC);
	}

	/**
	 * Add a new position
	 *
	 * @param string $id        Position ID
	 * @param int    $available Availability
	 */
	public function setPositionAvailability($id, $available)
	{
		$stmt = $this->db->prepare('UPDATE positions SET available=:available WHERE id=:id');
		$stmt->bindValue(':id', $id, SQLITE3_TEXT);
		$stmt->bindValue(':available', $available, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Add a new position
	 *
	 * @param string $id Position ID
	 *
	 * @throws DatabaseException
	 */
	public function addPosition($id)
	{
		$stmt = $this->db->prepare('INSERT INTO positions (id, available, idx) VALUES (:id, 0, (SELECT MAX(idx) + 1 FROM positions))');
		$stmt->bindValue(':id', $id, SQLITE3_TEXT);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Update a position id
	 *
	 * @param string $oldId Old position ID
	 * @param string $newId New position ID
	 */
	public function updatePositionId($oldId, $newId)
	{
		$stmt = $this->db->prepare('UPDATE positions SET id=:newId WHERE id=:oldId');
		$stmt->bindValue(':oldId', $oldId, SQLITE3_TEXT);
		$stmt->bindValue(':newId', $newId, SQLITE3_TEXT);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Update a position index
	 *
	 * @param string $id    Position ID
	 * @param int    $index Position index
	 */
	public function updatePositionIndex($id, $index)
	{
		$stmt = $this->db->prepare('UPDATE positions SET idx=:index WHERE id=:id');
		$stmt->bindValue(':id', $id, SQLITE3_TEXT);
		$stmt->bindValue(':index', $index, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Delete a positionù
	 *
	 * @param int $id Position ID
	 */
	public function deletePosition($id)
	{
		$stmt = $this->db->prepare('DELETE FROM positions WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_TEXT);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * Get a translation
	 *
	 * @param string $id   Translation ID
	 * @param string $lang Language
	 *
	 * @return array Associative array with value
	 */
	public function getTranslation($id, $lang)
	{
		$stmt = $this->db->prepare('SELECT value FROM translations WHERE id = :id AND lang = :lang');
		$stmt->bindValue(':id', $id, SQLITE3_TEXT);
		$stmt->bindValue(':lang', $lang, SQLITE3_TEXT);
		$result = $stmt->execute();

		return $result->fetchArray(SQLITE3_ASSOC);
	}

	/**
	 * Update a translation
	 *
	 * @param string $id    Translation ID
	 * @param string $lang  Language
	 * @param string $value Translation value
	 *
	 * @throws DatabaseException
	 *
	 * @return void
	 */
	public function updateTranslation($id, $lang, $value)
	{
		$stmt = $this->db->prepare('INSERT OR REPLACE INTO translations (id, lang, value) VALUES (:id, :lang, :value)');
		$stmt->bindValue(':id', $id, SQLITE3_TEXT);
		$stmt->bindValue(':lang', $lang, SQLITE3_TEXT);
		$stmt->bindValue(':value', $value, SQLITE3_TEXT);
		$result = $stmt->execute();
		if ($result === false) {
			throw new DatabaseException();
		}
	}

	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	/**
	 * Convert timestamp to a DateTime
	 *
	 * @param int               $timestamp Unix Timestamp
	 * @param DateTimeZone|null $dtz       Timezone, null for default
	 *
	 * @return DateTime
	 */
	private function timestampToTime(int $timestamp, ?DateTimeZone $dtz = null): DateTime
	{
		$dtz = $dtz ?? new DateTimeZone('Europe/Rome');
		/**
	* @noinspection PhpUnhandledExceptionInspection
*/
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
	 * @param User|null    $user
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
