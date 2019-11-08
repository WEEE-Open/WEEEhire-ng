<?php
define('TEST_MODE', true);  # Bypasses authentication and does not send emails
define('WEEEHIRE_EMAIL_FALLBACK', 'example@example.com');
define('WEEEHIRE_INVITE_LINK', 'https://example.com/register.php?invite=');
define('WEEEHIRE_SELF_LINK', 'https://weeehire.example.com');
define('WEEEHIRE_LDAP_URL', 'ldaps://ldap.example.com');
define('WEEEHIRE_LDAP_BIND_DN', 'cn=weeehire,ou=Services,dc=example,dc=com');
define('WEEEHIRE_LDAP_PASSWORD', 'f00b4r');
define('WEEEHIRE_LDAP_STARTTLS', false);
define('WEEEHIRE_LDAP_USERS_DN', 'ou=People,dc=example,dc=com');
define('WEEEHIRE_LDAP_INVITES_DN', 'ou=Invites,dc=example,dc=com');
define('WEEEHIRE_LDAP_SHOW_USERS_FILTER',
	'(&(objectclass=weeeopenperson)(!(nsaccountlock=true))(|(memberOf=cn=Admins,ou=Groups,dc=example,dc=com)(memberOf=cn=SomeGroup,ou=Groups,dc=example,dc=com)))');
define('WEEEHIRE_OIDC_ISSUER', 'https://sso.example.com/auth/realms/master');
define('WEEEHIRE_OIDC_CLIENT_ID', 'weeehire');
define('WEEEHIRE_OIDC_CLIENT_KEY', 'weeehire');
define('WEEEHIRE_OIDC_CLIENT_SECRET', '');
define('WEEEHIRE_OIDC_ALLOWED_GROUPS', [
	'Admins',
	'SomeGroup'
]);