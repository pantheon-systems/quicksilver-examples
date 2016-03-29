# Visual Regression Testing via Spotbot.qa #

This example will show you how to integrate [Spotbot.qa](https://spotbot.qa/)'s visual regression operation into your deployment workflow. 

This will allow you to do a comparative visual diff between the testing environment after your code deployments. 

## Instructions ##

In order to get up and running, you first need to setup a Spotbot.qa project:

1. Either login to your account or register for a new one at [https://spotbot.qa/](https://spotbot.qa/).
2. Generate an API Key on your Spotbot.qa account page: [https://spotbot.qa/api/token](https://spotbot.qa/api/token).
3. Setup a Spotbot.qa project for your site.

Then you need to add the relevant code to your Pantheon project: 

1. Add the example `spotbot_visualregression.php` script to the 'private/scripts/' directory of your code repository.
2. Modify the `spotbot_visualregression.php` script to include your API key and your Project URL.
3. Add a Quicksilver operation to your `pantheon.yml` to fire the script before & after a deploy to test.
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
        description: do a visual regression test with Spotbot.qa
        script: private/scripts/spotbot_visualregression.php
```
