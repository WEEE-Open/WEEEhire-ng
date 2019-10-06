<?php


namespace WEEEOpen\WEEEHire;


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
		$stmt = $this->db->prepare('SELECT id, name, surname, degreecourse, year, matricola, area, letter, published, status, recruiter, recruitertg, submitted, notes FROM users WHERE id = :id LIMIT 1');
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
				'recruiter',
				'recruitertg',
				'submitted',
				'notes'
			] as $attr
		) {
			$user->$attr = $row[$attr];
		}
		$user->published = (bool) $user->published;
		$user->status = $user->status === null ? null : (bool) $user->status;

		return $user;
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
		$result = $this->db->query('SELECT id, name, surname, area, recruiter, published, status, submitted FROM users');
		$compact = [];
		while($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$compact[] = [
				'id'        => $row['id'],
				'name'      => $row['name'] . ' ' . $row['surname'],
				'area'      => $row['area'],
				'recruiter' => $row['recruiter'],
				'published' => (bool) $row['published'],
				'status'    => $row['status'] === null ? null : (bool) $row['status'],
				'submitted' => $row['submitted']
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

	public function setStatus(int $id, ?bool $status) {
		$stmt = $this->db->prepare('UPDATE users SET status = :statusp WHERE id = :id');
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

	public function setPublished(int $id, bool $published) {
		$stmt = $this->db->prepare('UPDATE users SET published = :pub WHERE id = :id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(':pub', (int) $published, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}
}
