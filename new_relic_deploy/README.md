= New Relic Deploy Logs =

This example will show you how you can automatically log changes to your site into New Relic's [Deploy Log](https://docs.newrelic.com/docs/apm/applications-menu/events/deployments-page) when the workflow fires on Pantheon.

== Instructions ==

Setting up this example is easy:

1. Enable New Relic for your site.
2. Add the example `new_relic_deploy.php` script to the `private` directory of your code repository.
2. Add a Quicksilver operation to your `pantheon.yml` to fire the script a deploy.
3. Test a deploy out!

=== Example `pantheon.yml` ===

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  deploy:
    after:
      webphp:
        - new_relic: private/scripts/new_relic_deploy.php
```