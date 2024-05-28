# Set New Relic Apdex T values on Multidev Creation #


[All sites on Pantheon include access to New Relic APM Pro](https://pantheon.io/features/new-relic). This application performance monitoring relies on the site owners setting time benchmark to measure against. Ideally, your Drupal or WordPress responds quickly to requests. And if the site is not responding quickly, New Relic can alert you.

The question is, where do you want to set the bar? By default, New Relic uses 0.5 as the target number of seconds. This value is called "T" in the [Apdex (Application Performance Index) formula](https://docs.newrelic.com/docs/apm/new-relic-apm/apdex/apdex-measuring-user-satisfaction).

In addition to monitoring how fast the server (Drupal or WordPress) respond, New Relic can monitor how fast real world browsers render your site. Browser performance is measured with the same Apdex formula. By default, New Relic uses a much more generous 7 seconds as the T value in browser Apdex.

We recommend that any team working on a site discuss expectations for server-side and client-side performance and set T values accordingly. As you are developing new features with [Pantheon Multidev,](https://pantheon.io/features/multidev-cloud-environments) you might even want the Multidev environments to have more stringent T values than Test or Live environments.

This Quicksilver example shows how you can set custom T values for Multidev environments when they are created. Otherwise each environment will use the default values of 0.5 and 7 for server and browser respectively.

To do the actual setting of default values this script first gets an API key and then uses that key to interact with New Relic's REST API to set a T values based on the existing values from the dev (or test/live) environment.

## Instructions ##

To use this example:

1. [Activate New Relic Pro](https://pantheon.io/docs/new-relic/#activate-new-relic-pro) within your site dashboard.
2. Get a [New Relic User Key](https://docs.newrelic.com/docs/apis/intro-apis/new-relic-api-keys/)
2. Using [Terminus Secrets Manager Plugin](https://github.com/pantheon-systems/terminus-secrets-manager-plugin), set a site secret for the API key just created (e.g. `new_relic_api_key`, if you name it something else, make sure to update in the script below). Make sure type is `runtime` and scope contains `web`.
2. Add the example `new_relic_apdex_t.php` script to the `private/scripts` directory of your code repository.
3. Optionally, modify the environment to pull existing threshold T values from at the top of the file. This defaults to `dev` but can also be `test` or `live`.

```php
// get New Relic info from the dev environment
// Change to test or live as you wish
$app_info = get_app_info( 'dev' );

```

4. Add a Quicksilver operation to your `pantheon.yml` to fire the script after a deploy. (One gotcha is that this script cannot be the first or only script called as part of Multidev creation. Before the New Relic API recognizes the the Multidev environment, that environment needs to have received at least one previous request.) 


### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  create_cloud_development_environment:
    after:
      # The setting of Apdex cannot be the first traffic the new Multidev environment
      # receives. A New Relic application ID is not available until after the
      # environment receives some traffic. So run another script prior to calling
      # new_relic_apdex_t.php. In this case drush_config_import.php is an
      # arbitrary example.
      - type: webphp
        description: Drush Config Import
        script: private/scripts/drush_config_import.php
      - type: webphp
        description: Set Apdex T values
        script: private/scripts/new_relic_apdex_t.php
```
