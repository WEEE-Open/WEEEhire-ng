# WEEEHire 2.0

Manage applications to the team.

## Testing

```bash
composer install
cp config/config-example.php config/config.php
nano config/config.php
sqlite3 weeehire.db < database.sql
php -S [::]:8777 public
```

Done. Browse to `[::]:8777` and have fun.

You do not need to configure the SSO part, neither to configure sendmail, nor to install APCu: these are required in production only.

In fact, if TEST_MODE is trues in config.php:

- Emails will *not* be sent, they are printed to stderr
- Authentication is bypassed, access `[::]:8777/candidates.php` directly

LDAP is not bypassed, however. If APCu is disabled a warning will be printed, but the software will still work (even in production if you're desperate).  

## Translations

```bash
xgettext -k__ --from-code utf-8 templates/*.php -o messages.pot
msgmerge --update resources/locale/en-us/LC_MESSAGES/messages.po messages.pot
msgfmt resources/locale/en-us/LC_MESSAGES/messages.po --output-file=resources/locale/en-us/LC_MESSAGES/messages.mo
```

TODO
