# Database sanitization of user emails and passwords. #

This example will show you how you can automatically sanitize emails and passwords from your Drupal or WordPress database when cloning to a different environment. This practice can help prevent against the accidental exposure of sensitive data by making it easy for your team members to use and download sanitized databases. The Pantheon backups of the live environment will still contain user emails and hashed passwords.

## Instructions ##

Setting up this example is easy:

1. Add either the db_sanitization_drupal.php or the db_sanitization_wordpress.php to the `private` directory of your code repository.
2. Add a Quicksilver operation to your `pantheon.yml` to fire the script after cloning the database.
3. Test a deploy out!

Optionally, you may want to use the `terminus workflow:watch yoursitename` command to get immediate debugging feedback.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml

api_version: 1

workflows:
  clone_database:
    after:
      - type: webphp
        description: Sanitize the db
        script: private/scripts/db_sanitization_(wordpress|drupal).php
```

