# Slack Integration Using Drupal Key Module #

This script shows how easy it is to integrate Slack notifications from your Pantheon project using Quicksilver. As a bonus, we also show you how to manage API keys outside of your site repository using the [Drupal Key](https://www.drupal.org/project/key) module. This module allows you to centralize all keys into one place and give Quicksilver access to them using Drush.

## Instructions ##

1. [Enable Incoming Webhooks](https://my.slack.com/services/new/incoming-webhook/) for your Slack instance.
2. Examine the secret Webhook URL, which should look something like: `https://hooks.slack.com/services/MY/SECRET/URL` and copy everything after `https://hooks.slack.com/services/` and put it into a new key with a machine name of `slack_url_secret`. You can add this key at: /admin/config/system/keys/add of your site.
3. Add the example `slack_notification.php` and `key.php` scripts to `private` directory in the root of your site's codebase, that is under version control. 
4. Add Quicksilver operations to your `pantheon.yml`
5. Test a deploy out!

For secrets such as this webhook it's recommended to use a secure secrets management system like [Lockr](https://www.drupal.org/project/lockr). Lockr is free to use in all development environments and integrates seamlessly into the key module, just select it under the **Key Provider** dropdown when entering your key. 

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback. You may also want to customize your notifications further. The [Slack API](https://api.slack.com/incoming-webhooks) documentation has more on your options.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use.  Pick and choose the exact workflows that you would like to see notifications for.

```yaml
api_version: 1

workflows:
  deploy:
    after:
        - type: webphp
          description: Post to Slack on deploy
          script: private/scripts/slack_notification.php
  sync_code:
    after:
        - type: webphp
          description: Post to Slack on sync code
          script: private/scripts/slack_notification.php
  clear_cache:
    after:
        - type: webphp
          description: Someone is clearing the cache again
          script: private/scripts/slack_notification.php
```

