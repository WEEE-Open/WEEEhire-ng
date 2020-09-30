# WEEEHire 2.0

Manage applications to the team.

## Local development

```bash
sqlite3 weeehire.db < database.sql
cp config/config-example.php config/config.php
nano config/config.php  # Optional, the defaults are good for a test instance but not for production
msgfmt resources/locale/en-US/LC_MESSAGES/messages.po --output-file=resources/locale/en-US/LC_MESSAGES/messages.mo
composer install
php -S [::]:8777 -t public
```

Done. Browse to `[::]:8777` and have fun.

You do not need to configure the SSO part, neither LDAP, neither sendmail, nor to install APCu: these are required in production only.

In fact, if TEST_MODE is true in config.php:

- Emails will *not* be sent, they are printed to stderr
- Authentication is bypassed, access `[::]:8777/candidates.php` directly
- No LDAP connections are made, example data is returned instead
- APCu is not used at all

APCu is optional but strongly recommended in production.
WEEEHire will print some warnings to stderr if APCu is not enabled.

## Translations

```bash
# Generate the master .pot file
xgettext -k__ -k_ngettext:1,2 --from-code utf-8 templates/*.php -o messages.pot
# Merge it into other .po files (en-US only, right now)
msgmerge --update resources/locale/en-US/LC_MESSAGES/messages.po messages.pot
# Translate strings, e.g. with
lokalize resources/locale/en-US/LC_MESSAGES/messages.po &
# Create the .mo file
msgfmt resources/locale/en-US/LC_MESSAGES/messages.po --output-file=resources/locale/en-US/LC_MESSAGES/messages.mo
```

And done.

## Production deployment

Basically the same as development:

```bash
sqlite3 weeehire.db < database.sql
cp config/config-example.php config/config.php
nano config/config.php
msgfmt resources/locale/en-US/LC_MESSAGES/messages.po --output-file=resources/locale/en-US/LC_MESSAGES/messages.mo
chown o-r weeehire.db  # Optional, prevent other users from reading the database
composer install --no-dev --optimize-autoloader  # The optimization is not required but a nice touch
```

and a real web server is needed. The root directory is `public`, no need to serve files outside that directory.
