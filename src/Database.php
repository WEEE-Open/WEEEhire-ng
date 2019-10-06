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

		$stmt = $this->db->prepare('INSERT INTO users (token, name, surname, degreecourse, year, matricola, area, letter, published, status, recruiter, submitted) VALUES (:token, :namep, :surname, :degreecourse, :yearp, :matricola, :area, :letter, :published, :statusp, :recruiter, :submitted)');
		$stmt->bindValue(':token', password_hash($token, PASSWORD_DEFAULT), SQLITE3_TEXT);
		$stmt->bindValue(':namep', $user->name, SQLITE3_TEXT);
		$stmt->bindValue(':surname', $user->surname, SQLITE3_TEXT);
		$stmt->bindValue(':degreecourse', $user->degreecourse, SQLITE3_TEXT);
		$stmt->bindValue(':yearp', $user->year, SQLITE3_TEXT);
		$stmt->bindValue(':matricola', $user->matricola, SQLITE3_TEXT);
		$stmt->bindValue(':area', $user->area, SQLITE3_TEXT);
		$stmt->bindValue(':letter', $user->letter, SQLITE3_TEXT);
		$stmt->bindValue(':published', $user->published, SQLITE3_INTEGER);
		$stmt->bindValue(':statusp', $user->status, $user->status === null ? SQLITE3_NULL : SQLITE3_INTEGER);
		$stmt->bindValue(':recruiter', $user->recruiter, $user->recruiter === null ? SQLITE3_NULL : SQLITE3_INTEGER);
		$stmt->bindValue(':submitted', $user->submitted);
		if(!@$stmt->execute()) {
			if($this->db->lastErrorCode() === 19 && stristr($this->db->lastErrorMsg(), 'matricola')) {
				throw new DuplicateUserException();
			} else {
				throw new DatabaseException();
			}
		}
		$id = $this->db->lastInsertRowID();
		return [$id, $token];
	}

	public function getUser(string $id): User {
		$stmt = $this->db->prepare('SELECT id, name, surname, degreecourse, year, matricola, area, letter, published, status, recruiter, submitted FROM users WHERE id = :id LIMIT 1');
		$stmt->bindValue(':id', $id, SQLITE3_TEXT);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$result->finalize();
		$user = new User();
		foreach(['id', 'name', 'surname', 'degreecourse', 'year', 'matricola', 'area', 'letter', 'published', 'status', 'recruiter', 'submitted'] as $attr) {
			$user->$attr = $row[$attr];
		}
		$user->published = (bool) $user->published;
		$user->status = $user->status === null ? null : (bool) $user->status;
		return $user;
	}

	public function validateToken(int $id, string $token): bool {
		$stmt = $this->db->prepare('SELECT token FROM users WHERE id = :id LIMIT 1');
		$stmt->bindValue(':id', $id);
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
		$stmt->bindValue(':id', $id);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}
}
