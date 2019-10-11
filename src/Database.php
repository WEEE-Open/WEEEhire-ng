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
		$stmt = $this->db->prepare('SELECT id, name, surname, degreecourse, year, matricola, area, letter, published, status, recruiter, recruitertg, submitted, notes, emailed, invitelink FROM users WHERE id = :id LIMIT 1');
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
		$result = $this->db->query('SELECT id, name, surname, area, recruiter, published, status, submitted, IFNULL(LENGTH(notes), 0) as notesl FROM users ORDER BY submitted DESC');
		$compact = [];
		while($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$compact[] = [
				'id'        => $row['id'],
				'name'      => $row['name'] . ' ' . $row['surname'],
				'area'      => $row['area'],
				'recruiter' => $row['recruiter'],
				'notes'     => (bool) $row['notesl'],
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

	public function deleteOlderThan(int $days) {
		$stmt = $this->db->prepare("DELETE FROM users WHERE published = 1 AND strftime('%s','now') - submitted >= :diff");
		$stmt->bindValue(':diff', $days * 24 * 60 * 60, SQLITE3_INTEGER);
		$result = $stmt->execute();
		if($result === false) {
			throw new DatabaseException();
		}
	}
}
