# Slack Integration #

This script shows how easy it is to integrate Slack notifications from your Pantheon project using Quicksilver. As a bonus, we also show you how to manage API keys outside of your site repository.

## Instructions ##

1. [Enable Incoming Webhooks](https://my.slack.com/services/new/incoming-webhook/) for your Slack instance.
2. Copy the secret Webhook URL into a file called `secrets.json` and store it in the [private files](https://pantheon.io/docs/articles/sites/private-files/) directory of every environment where you want to trigger Slack notifications.

  ```shell
    $> echo '{"slack_url": "https://hooks.slack.com/services/MY/SECRET/URL","slack_channel": "#channel"}' > secrets.json
    # To post as a specific user you will want to use:
    $> echo '{"slack_url": "https://hooks.slack.com/services/MY/SECRET/URL","slack_channel": "#channel","slack_username":"myusername"}' > secrets.json
    # Note, you'll need to copy the secrets into each environment where you want to trigger Slack notifications.
    $> `terminus connection:info  --field=sftp_command site.env`
        Connected to appserver.dev.d1ef01f8-364c-4b91-a8e4-f2a46f14237e.drush.in.
    sftp> cd files  
    sftp> mkdir private
    sftp> cd private
    sftp> put secrets.json
    sftp> quit
  ```

3. Add, and update as needed, the example `slack_notification.php` script to the `private` directory in the root of your site's codebase, that is under version control. Note this is a different `private` directory than where the secrets.json is stored.
4. Add Quicksilver operations to your `pantheon.yml`
5. Test a deploy out!

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback. You may also want to customize your notifications further. The [Slack API](https://api.slack.com/incoming-webhooks) documentation has more on your options.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use.  Pick and choose the exact workflows that you would like to see notifications for.

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

