# New Relic Deploy Logs #

This example will show you how you can automatically log changes to your site into New Relic's [Deploy Log](https://docs.newrelic.com/docs/apm/applications-menu/events/deployments-page) when the workflow fires on Pantheon.

This script uses a couple clever tricks to get data about the platform. First of all it uses the `pantheon_curl()` command to fetch the extended metadata information for the site/environment, which includes the New Relic API key. It also uses data within the git repository on the platform to pull out deploy tag numbers and log messages. 

## Instructions ##

Setting up this example is easy:

1. Enable New Relic for your site.
2. Add the example `new_relic_deploy.php` script to the `private` directory of your code repository.
3. Add a Quicksilver operation to your `pantheon.yml` to fire the script a deploy.
4. Test a deploy out!

Oteionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  deploy:
    after:
      webphp:
        - new_relic: private/scripts/new_relic_deploy.php
```
