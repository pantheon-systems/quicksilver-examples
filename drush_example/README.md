# Drush Example #

This script demonstrates how to use drush from within a Quicksilver script.

The demonstration enables the [devel](https://www.drupal.org/project/devel) module after a database has been cloned to the `dev` environment.

## Instructions ##

- Be sure the [devel](https://www.drupal.org/project/devel) module is installed into your Drupal codebase.
- Copy the `drush_example` directory into the private/scripts directory of your code repository.
- Add a Quicksilver operation to your pantheon.yml to fire the script after a `clone_database`.
- Test a database clone to dev from an environment where the devel module is not enabled.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  clone_database:
    after:
      - type: webphp
        description: Drush Example
        script: private/scripts/drush_example/drush_example.php
```
