# WEEEHire 2.0

Manage applications to the team.

## Local development

```bash
composer install
cp config/config-example.php config/config.php
nano config/config.php
sqlite3 weeehire.db < database.sql
msgfmt resources/locale/en-us/LC_MESSAGES/messages.po --output-file=resources/locale/en-us/LC_MESSAGES/messages.mo
php -S [::]:8777 public
```

Done. Browse to `[::]:8777` and have fun.

You do not need to configure the SSO part, neither LDAP, neither sendmail, nor to install APCu: these are required in production only.

In fact, if TEST_MODE is trues in config.php:

- Emails will *not* be sent, they are printed to stderr
- Authentication is bypassed, access `[::]:8777/candidates.php` directly
- No LDAP connections are made, example data is returned instead
- APCu is not used at all

APCu is optional but strongly recommended in production.  

## Translations

```bash
# Generate the master .pot file
xgettext -k__ --from-code utf-8 templates/*.php -o messages.pot
# Merge it into other .po files (en-us only, right now)
msgmerge --update resources/locale/en-us/LC_MESSAGES/messages.po messages.pot
# Create the .mo file
msgfmt resources/locale/en-us/LC_MESSAGES/messages.po --output-file=resources/locale/en-us/LC_MESSAGES/messages.mo
```

And done.

> Why en-us instead of en-US?

`willdurand/negotiation` negotiates en-us even if supported languages includes en-US for some reason. It works, but motranslator skips some checks and parsing because it is a non-standard format. Getting the negotiator to negotiate en-US would be nice.

## Production deployment

Basically the same as development:

```bash
composer install --optimize-autoloader  # The optimization is not required but a nice touch
msgfmt resources/locale/en-us/LC_MESSAGES/messages.po --output-file=resources/locale/en-us/LC_MESSAGES/messages.mo
cp config/config-example.php config/config.php
nano config/config.php
sqlite3 weeehire.db < database.sql
chown o-r weeehire.db  # Optional, prevent other users from reading the database
```

and a real web server is needed. The root directory is `public`, no need to serve files outside that directory.
