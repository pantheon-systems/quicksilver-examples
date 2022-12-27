# New Relic Deploy Logs #

This example will show you how you can automatically log changes to your site into [New Relic's Deployments Page](https://docs.newrelic.com/docs/apm/applications-menu/events/deployments-page) when the workflow fires on Pantheon. This can be quite useful for keeping track of all your performance improvements!

This script uses a couple clever tricks to get data about the platform. First of all it uses the `pantheon_curl()` command to fetch the extended metadata information for the site/environment, which includes the New Relic API key. It also uses data within the git repository on the platform to pull out deploy tag numbers and log messages. 

> **Note:** This example will work for all Pantheon sites once the bundled [New Relic APM Pro feature](https://pantheon.io/features/new-relic) is activated, regardless of service level. 

## Instructions ##

Setting up this example is easy:

1. [Activate New Relic Pro](https://pantheon.io/docs/new-relic/#activate-new-relic-pro) within your site Dashboard. 
2. Add the example `new_relic_deploy.php` script to the `private` directory of your code repository.
3. Add a Quicksilver operation to your `pantheon.yml` to fire the script after a deploy.
4. Test a deploy out!

Optionally, you may want to use the `terminus workflow:watch yoursitename` command to get immediate debugging feedback.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
# Always need to specify the pantheon.yml API version.
api_version: 1

# You might also want some of the following here:
# php_version: 7.0
# drush_version: 8

workflows:
  # Log to New Relic when deploying to test or live.
  deploy:
    after:
      - type: webphp
        description: Log to New Relic
        script: private/scripts/new_relic_deploy.php
  # Also log sync_code so you can track new code going into dev/multidev.
  sync_code:
    after:
      - type: webphp
        description: Log to New Relic
        script: private/scripts/new_relic_deploy.php

```
