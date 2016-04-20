# Performance Testing via Load Impact #

This example will show you how to integrate [Load Impact](https://loadimpact.com/)'s performance testing into your deployment workflow. 

This will allow you to do a performance scan of your testing environment after your code deployments.

## Instructions ##

In order to get up and running, you first need to setup a Load Impact project:

1. Either login to your account or register for a new one at [https://loadimpact.com/](https://loadimpact.com/).
2. Generate an API Key on your Load Impact account page: [https://app.loadimpact.com/integrations/api-token](https://app.loadimpact.com/integrations/api-token).
3. Setup a Load Impact test for your site.

Then you need to add the relevant code to your Pantheon project: 

1. Add the example `loadimpact.php` script to the 'private/scripts/' directory of your code repository.
2. Modify the `loadimpact.php` script to include your API key and your Project URL.
3. Add a Quicksilver operation to your `pantheon.yml` to fire the script after a deploy to test.
4. Test a deploy out!

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  deploy:
    after:
      - type: webphp
        description: do a performance test with Load Impact
        script: private/scripts/loadimpact.php
```
