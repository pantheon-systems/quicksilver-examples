# New Relic Monitor #

This example will show you how you can automatically create a [New Relic Synthetics Ping Monitor](https://docs.newrelic.com/docs/synthetics/new-relic-synthetics/getting-started/types-synthetics-monitors) when a live deployment is triggered on Pantheon. This can be useful for monitoring the server response time and uptime from various locations around the world.

This script uses the `pantheon_curl()` command to fetch the extended metadata information for the site/environment, which includes the New Relic API key. Using New Relic's REST API, we first check to see if the monitor exists, and if not, we will create a new one.

> **Note:** This example will work for all Pantheon sites (except Basic) once the bundled [New Relic APM Pro feature](https://pantheon.io/features/new-relic) is activated. 

## Instructions ##

Setting up this example is easy:

1. [Activate New Relic Pro](https://pantheon.io/docs/new-relic/#activate-new-relic-pro) within your site Dashboard. 
2. Add the example `new_relic_monitor.php` script to the `private` directory of your code repository.
3. Add a Quicksilver operation to your `pantheon.yml` to fire the script after a deploy.
4. Test a deploy out!

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
# Always need to specify the pantheon.yml API version.
api_version: 1

workflows:
  # Create a New Relic Monitor when deploying to live.
  deploy:
    after:
      - type: webphp
        description: Log to New Relic
        script: private/scripts/new_relic_monitor.php
```
