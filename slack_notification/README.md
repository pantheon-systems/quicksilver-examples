# Slack Integration

Hook into platform workflows and post notifications to Slack.

## Instructions

1. [Enable Incoming Webhooks](https://api.slack.com/messaging/webhooks#advanced_message_formatting) for your Slack instance.
2. Copy the secret Webhook URL into a file called `secrets.json` and store it in the [private files](https://pantheon.io/docs/articles/sites/private-files/) directory of each environment where you want to trigger Slack notifications. The secret WebHook URL is like a password, which should not be stored in version control.

```shell
  $> echo '{"slack_url": "https://hooks.slack.com/services/MY/SECRET/URL"}' > secrets.json
  # Note, you will need to copy the secrets into each environment where you want to trigger Slack notifications.
  $> `terminus connection:info  --field=sftp_command site.env`
      Connected to appserver.dev.d1ef01f8-364c-4b91-a8e4-f2a46f14237e.drush.in.
  sftp> cd files
  sftp> mkdir private
  sftp> cd private
  sftp> put secrets.json
  sftp> quit
```

3. Add the example `slack_notification.php` script to the `private` directory in the root of your site's codebase, that is under version control. Note this is a different `private` directory than where the `secrets.json` is stored.
4. Add Quicksilver operations to your `pantheon.yml`
5. Test a deployment and see the notification in the Slack channel associated with the webhook.

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback. You may also want to customize your notifications further. The [Slack API](https://api.slack.com/incoming-webhooks) documentation has more on your options.

### Example `pantheon.yml`

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use. Pick and choose the exact workflows that you would like to see notifications for.

```yaml
api_version: 1

workflows:
  deploy_product:
    after:
      - type: webphp
        description: Post to Slack after site creation
        script: private/scripts/slack_notification.php
  create_cloud_development_environment:
    after:
      - type: webphp
        description: Post to Slack after Multidev creation
        script: private/scripts/slack_notification.php
  deploy:
    after:
      - type: webphp
        description: Post to Slack after deploy
        script: private/scripts/slack_notification.php
  sync_code:
    after:
      - type: webphp
        description: Post to Slack after code commit
        script: private/scripts/slack_notification.php
  clear_cache:
    after:
      - type: webphp
        description: Someone is clearing the cache again
        script: private/scripts/slack_notification.php
```
