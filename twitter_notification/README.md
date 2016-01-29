# Twitter/Single User OAuth Integration #

This example shows how to integrate OAuth signed requests (in the form of Twitter updates) with your Pantheon site.

## Instructions ##

Setting up this example is relatively straightforward:

- [Create a new Twitter application](https://apps.twitter.com/).
- Generate a Consumer Key, Consumer Secret, Access Token & Access Token Secret as per the instructions on the [Twitter Developer Site](https://dev.twitter.com/oauth/overview/application-owner-access-tokens)
- Save these tokens into a file called `secrets.json` and store it in the private files area of your site as per the instructions for the [Slack Notification Example](https://github.com/pantheon-systems/quicksilver-examples/blob/master/slack_notification/README.md)

- Add the example `twitter_deploy.php` script to the `private` directory of your code repository.
- Add a Quicksilver operation to your `pantheon.yml` to fire the script a deploy.
- Test a deploy out!

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback. You may also want to customize your notifications further. The [Slack API](https://api.slack.com/incoming-webhooks) documentation has more on your options.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  deploy:
    after:
        - type: webphp
          description: Post to Slack
          script: private/scripts/twitter_deploy.php
```
