# Revert All Features Logs #

This example will show you how to integrate drush commands into your quicksilver operations, with the practical outcome of reverting features. You can use the method shown here to run any Drush command you like.

Note that with the current `webphp` type operations, your timeout is limited to 120 seconds, so long-running operations should be avoided for now. 


## Instructions ##

Setting up this example is easy:

1. Add the example `revert_all_features.php` script to the 'private/scripts/' directory of your code repository.
2. Add a Quicksilver operation to your `pantheon.yml` to fire the script before a deploy.
3. Test a deploy out!

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  sync_code:
    after:
      - type: webphp
        description: Revert all features after pushing code
        script: private/scripts/revert_all_features.php
  deploy:
    after:
      - type: webphp
        description: Revert all features after deploying to test or live
        script: private/scripts/revert_all_features.php
```
