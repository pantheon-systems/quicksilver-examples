# Slack Integration with Lockr #

This script shows how easy it is to integrate Slack notifications from your Pantheon project using Quicksilver. You will need a [Slack Webhook URL](https://api.slack.com/incoming-webhooks), which should not be exposed publicly.

 We **do not** recommend tracking sensitive information, such as API keys, in version control. Instead consider using a secure, managed service for storing sensitive credentials. This example uses [Lockr](https://www.lockr.io/), which has a free tier, but feel free to use your own solution.

## Instructions ##

1. [Enable Incoming Webhooks](https://my.slack.com/services/new/incoming-webhook/) for your Slack instance.
1. Install and activate the [Lockr WordPress plugin](https://wordpress.org/plugins/lockr/).
1. Save your webhook URL as `slack_url` in Lockr.
1. Save the Slack channel to post messages to as `slack_channel` in Lockr.
1. Add, and update as needed, the example `slack_notification.php` script to the `private` directory in the root of your site's codebase, that is under version control.
1. Add Quicksilver operations to your `pantheon.yml`
1. Test a deploy out!
    - Note that using the `cache_clear` Quicksilver hook makes testing easy. Once things are working well change to the hooks below.

Optionally, you may want to use the `terminus workflow:watch` command to get immediate debugging feedback. You may also want to customize your notifications further. The [Slack API](https://api.slack.com/incoming-webhooks) documentation has more information on formatting options.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use.  Pick and choose the exact workflows that you would like to see notifications for.

```yaml
api_version: 1

workflows:
  deploy_product:
    after:
        - type: webphp
          description: Post to Slack after site creation
          script: private/scripts/slack_notification_lockr.php
  create_cloud_development_environment:
    after: 
        - type: webphp
          description: Post to Slack after Multidev creation
          script: private/scripts/slack_notification_lockr.php
  deploy:
    after:
        - type: webphp
          description: Post to Slack after deploy
          script: private/scripts/slack_notification_lockr.php
  sync_code:
    after:
        - type: webphp
          description: Post to Slack after code commit
          script: private/scripts/slack_notification_lockr.php
  clear_cache:
    after:
        - type: webphp
          description: Someone is clearing the cache again
          script: private/scripts/slack_notification_lockr.php
```

