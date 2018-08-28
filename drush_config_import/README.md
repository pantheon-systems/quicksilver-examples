# Configuration import #

This example will show you how to integrate Drush commands into your Quicksilver operations, with the practical outcome of importing configuration changes from `.yml` files . You can use the method shown here to run any Drush command you like.

Note that with the current `webphp` type operations, your timeout is limited to 120 seconds, so long-running operations should be avoided for now. 

## Instructions ##

Setting up this example is easy:

1. Add the example `drush_config_import.php` script to the 'private/scripts/drush_config_import' directory of your code repository.
2. Add a Quicksilver operation to your `pantheon.yml` to fire the script before a deploy.
3. Test a deploy out!
4. Note that automating this step may not be appropriate for all sites. Sites on which configuration is edited in the live environment may not want to automatically switch to configuration stored in files. For more information, see https://www.drupal.org/documentation/administer/config

Optionally, you may want to use the `terminus workflow:watch` command to get immediate debugging feedback.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  deploy:
    after:
      - type: webphp
        description: Import configuration from .yml files
        script: private/scripts/drush_config_import/drush_config_import.php
```
