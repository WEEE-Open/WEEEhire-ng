<?php

namespace WEEEOpen\WEEEHire;

use Exception;

class Ldap
{
	protected $bindDn;
	protected $password;
	protected $usersDn;
	protected $invitesDn;
	protected $url;
	protected $starttls;
	private $apcu = false;
	public static $multivalued = ['memberof' => true, 'sshpublickey' => true, 'weeelabnickname' => true];

	public function __construct(
		string $url,
		string $bindDn,
		string $password,
		string $usersDn,
		string $invitesDn,
		bool $startTls = true
	) {
		$this->url = $url;
		$this->bindDn = $bindDn;
		$this->password = $password;
		$this->starttls = $startTls;
		$this->usersDn = $usersDn;
		$this->invitesDn = $invitesDn;

		if (Utils::hasApcu()) {
			$this->apcu = true;
		}
	}

	/**
	 * Get all available recruiters
	 *
	 * @return array Array of arrays, each one with recruiter name as element 0 and recruiter Telegram nickname as 1
	 */
	public function getRecruiters(): array
	{
		if (TEST_MODE) {
			error_log('Test mode enabled, returning sample data');

			return [
			['Alice', 'ali'],
			['Bob', 'b0b'],
			['Mario Rossi', 'test'],
			];
		}

		if ($this->apcu) {
			$cached = false;
			/**
	   * @noinspection PhpComposerExtensionStubsInspection
*/
			$recruiters = apcu_fetch('recruiters', $cached);
			/**
	   * @noinspection PhpComposerExtensionStubsInspection
*/
			if ($cached) {
				return $recruiters;
			}
		}

		$ds = $this->connect();
		$sr = ldap_search($ds, $this->usersDn, WEEEHIRE_LDAP_SHOW_USERS_FILTER, ['cn', 'telegramnickname']);
		if (!$sr) {
			throw new LdapException('Cannot search recruiters');
		}
		$count = ldap_count_entries($ds, $sr);
		if ($count === 0) {
			return [];
		} else {
			$recruiters = [];
			$entries = ldap_get_entries($ds, $sr);
			unset($entries['count']);
			foreach ($entries as $entry) {
				if (isset($entry['cn'])) {
					$cn = $entry['cn'][0];
				} else {
					$cn = "⚠️ Missing cn";
				}
				if (isset($entry['telegramnickname'])) {
					$tgn = $entry['telegramnickname'][0];
				} else {
					$tgn = "⚠️ Missing telegram nickname";
				}
				$recruiters[$cn] = [$cn, $tgn];
			}
			ksort($recruiters, SORT_STRING | SORT_FLAG_CASE);
			if ($this->apcu) {
				/**
		  * @noinspection PhpComposerExtensionStubsInspection
*/
				apcu_store('recruiters', $recruiters, 3600); // 1 hour
			}

			return $recruiters;
		}
	}

	/**
	 * @param User $user User to invite
	 *
	 * @return string The invite URL
	 * @throws Exception When entropy is too low
	 */
	public function createInvite(User $user): string
	{
		$inviteCode = strtoupper(bin2hex(random_bytes(12)));
		$normalizedName = Utils::normalizeCase($user->name);
		$normalizedSurname = Utils::normalizeCase($user->surname);

		$add = [
		'cn'                      => $normalizedName . ' ' . $normalizedSurname, // Mandatory attribute
		'objectclass'             => [
		'inviteCodeContainer',
		'schacLinkageIdentifiers',
		'schacPersonalCharacteristics',
		'telegramAccount',
		'weeeOpenPerson',
		],
		'givenname'               => $normalizedName,
		'sn'                      => $normalizedSurname,
		'mail'                    => Utils::politoMail($user->matricola),
		'schacpersonaluniquecode' => $user->matricola,
		'degreecourse'            => $user->degreecourse,
		'weeeOpenUniqueId'        => $inviteCode,
		];

		if (TEST_MODE) {
			error_log('Test mode enabled, not creating an invite. I would have inserted:');
			error_log(print_r($add, true));

			return WEEEHIRE_INVITE_LINK . $inviteCode;
		}

		$ds = $this->connect();
		$result = ldap_add($ds, "inviteCode=$inviteCode," . $this->invitesDn, $add);
		if (!$result) {
			throw new LdapException('Cannot create invite');
		}

		return WEEEHIRE_INVITE_LINK . $inviteCode;
	}

	protected static function simplify(array $result): array
	{
		// Same function in Crauto, too
		$things = [];
		foreach ($result as $k => $v) {
			// dn seems to be always null!?
			if (!is_int($k) && $k !== 'count' && $k !== 'dn') {
				$attr = strtolower($k); // Should be already done, but repeat it anyway
				$things[$attr] = $v[0];
			}
		}

		return $things;
	}


	private function connect()
	{
		if (TEST_MODE) {
			error_log('Test mode enabled, not connecting to LDAP');

			return null;
		}

		$ds = ldap_connect($this->url);
		if (!$ds) {
			throw new LdapException('Cannot connect to LDAP server');
		}
		if ($this->starttls) {
			if (!ldap_start_tls($ds)) {
				throw new LdapException('Cannot STARTTLS with LDAP server');
			}
		}
		if (!ldap_bind($ds, $this->bindDn, $this->password)) {
			throw new LdapException('Bind with LDAP server failed');
		}

		return $ds;
	}
}
