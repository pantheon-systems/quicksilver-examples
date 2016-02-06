# Enable Development Modules #

This script demonstrates how to use drush from within a Quicksilver script.

The demonstration enables the [devel](https://www.drupal.org/project/devel) 
module after a database has been cloned to a `dev` or `multidev` environment.

## Instructions ##

- Be sure the [devel](https://www.drupal.org/project/devel) module is installed into your Drupal codebase.
- Copy the `enable_dev_modules` directory into the private/scripts directory of your code repository.
- Add a Quicksilver operation to your pantheon.yml to fire the script after a `clone_database`.
- Test a database clone to dev from an environment where the devel module is not enabled.

Note: While Pantheon does not require a `settings.php` file to run Drupal, Drush does. Make sure you have one committed to the codebase.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  clone_database:
    after:
      - type: webphp
        description: Drush Example
        script: private/scripts/enable_dev_modules/enable_dev_modules.php
```
