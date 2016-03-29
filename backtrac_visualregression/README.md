# Visual Regression Testing via Backtrac.io #

This example will show you how to integrate [backtrac.io](http://backtrac.io)'s visual regression operation into your deployment workflow. 

This will allow you to do a comparative visual diff between the live environment and the test environemnt everytime you deploy to the testing environment. 

For more advanced use cases, including doing visual regression against Multidev instances, this script can be easily adapted for Backtrac.io's REST API: [http://backtrac.io/documentation/rest-api](http://backtrac.io/documentation/rest-api). 

As a note, Backtrac.io's free tier currently only supports 10 visual regression tests a day per project. If you have exceeded your daily limit, `terminus workflows watch` will display the error message from Backtrac.io. The paid tiers (coming soon) will allow more daily visual regression tests.

## Instructions ##

In order to get up and running, you first need to setup a Backtrac.io project:

1. Either login to your account or register for a new one at [http://backtrac.io](http://backtrac.io).
2. Generate an API Key on your Backtrac.io account page: [http://backtrac.io/user](http://backtrac.io/user).
3. Setup a Backtrac.io project for your site and define the Production and Staging URLs in the project settings.

Then you need to add the relevant code to your Pantheon project: 

1. Add the example `backtrac_visualregression.php` script to the 'private/scripts/' directory of your code repository.
2. Modify the `backtrac_visualregression.php` script to include your API key and your Project ID.
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
        description: do a visual regression test with Backtrac.io
        script: private/scripts/backtrac_visualregression.php
```
