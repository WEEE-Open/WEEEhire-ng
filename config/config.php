<?php
function env_or_default($varname, $default) {
	$value = getenv($varname);
	
	if($value === false) {
		define($varname, $default);
	} else {
		// Handle booleans.
		if(is_bool($default)) {
			define($varname, filter_var($value, FILTER_VALIDATE_BOOLEAN));
		} else {
			define($varname, $value);
		}
	}
}

env_or_default('TEST_MODE', true);  # Bypasses authentication and does not send emails
env_or_default('WEEEHIRE_EMAIL_FALLBACK', 'example@example.com');
env_or_default('WEEEHIRE_INVITE_LINK', 'https://example.com/register.php?invite=');
env_or_default('WEEEHIRE_SELF_LINK', 'https://weeehire.example.com');
env_or_default('WEEEHIRE_LDAP_URL', 'ldaps://ldap.example.com');
env_or_default('WEEEHIRE_LDAP_BIND_DN', 'cn=weeehire,ou=Services,dc=example,dc=com');
env_or_default('WEEEHIRE_LDAP_PASSWORD', 'f00b4r');
env_or_default('WEEEHIRE_LDAP_STARTTLS', false);
env_or_default('WEEEHIRE_LDAP_USERS_DN', 'ou=People,dc=example,dc=com');
env_or_default('WEEEHIRE_LDAP_INVITES_DN', 'ou=Invites,dc=example,dc=com');
env_or_default('WEEEHIRE_LDAP_SHOW_USERS_FILTER','(&(objectclass=weeeopenperson)(!(nsaccountlock=true))(|(memberOf=cn=Admins,ou=Groups,dc=example,dc=com)(memberOf=cn=SomeGroup,ou=Groups,dc=example,dc=com)))');
env_or_default('WEEEHIRE_OIDC_ISSUER', 'https://sso.example.com/auth/realms/master');
env_or_default('WEEEHIRE_OIDC_CLIENT_ID', 'weeehire');
env_or_default('WEEEHIRE_OIDC_CLIENT_KEY', 'weeehire');
env_or_default('WEEEHIRE_OIDC_CLIENT_SECRET', '');
env_or_default('WEEEHIRE_OIDC_ALLOWED_GROUPS', 'Admins,SomeGroup');
