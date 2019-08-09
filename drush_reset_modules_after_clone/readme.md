# Reset Modules After Clone #

The script disables the [config_readonly](https://www.drupal.org/project/config_readonly) 
module after a database has been cloned to a `dev` or `multidev` environment. 
Purpose: when config_readonly is enabled in a live environment, the configuration can't be changed. 
If the database is copied to a dev or multi-dev environment, the module is still enabled, and the dev/multi-dev site is set to read only. 
If the site is read-only, you can't uninstall modules, and you are locked out and can't make edits to your site. 

## Instructions ##

- Be sure the [config_readonly](https://www.drupal.org/project/config_readonly) module is installed into your Drupal codebase.
- Copy the `drush_reset_modules_after_clone` directory into the private/scripts directory of your code repository.
- Add a Quicksilver operation to your pantheon.yml to fire the script after a `clone_database`.
- Test a database clone to dev from an environment where the config_readonly module is enabled.

Note: While Pantheon does not require a `settings.php` file to run Drupal, Drush does. Make sure you have one committed to the codebase.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:

  # Database Clones
  clone_database:
    after:
      - type: webphp
        description: Reset modules per environment after database clone
        script: private/scripts/drush_reset_modules_after_clone/drush_reset_modules_after_clone.php
```
