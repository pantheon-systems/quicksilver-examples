# Slack Integration #

This script shows how easy it is to integrate Slack notifications from your Pantheon project using Quicksilver. As a bonus, we also show you how to manage API keys outside of your site repository.

## Instructions ##

Setting up this example is easy:

- [Enable Incoming Webhooks](https://my.slack.com/services/new/incoming-webhook/) for your slack instance.
- Copy the secret Webhook URL into a file called `secrets.json` and store it in the private files area of your site

```shell
  $> echo '{"slack_url": "https://hooks.slack.com/services/MY/SECRET/URL"}' > secrets.json
  # Note, you'll need to copy the secrets into each environment where you want to trigger Slack notifications.
  $> `terminus site connection-info --env=live --site=your-site --field=sftp_command`
      Connected to appserver.live.d1ef01f8-364c-4b91-a8e4-f2a46f14237e.drush.in.
  sftp> cd files
  sftp> mkdir private
  sftp> cd private
  sftp> put secrets.json

```

- Add the example `slack_notification.php` script to the `private` directory of your code repository.
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
          script: private/scripts/slack_notification.php
```

