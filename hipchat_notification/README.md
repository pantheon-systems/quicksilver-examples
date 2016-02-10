# Hipchat Integration #

This script shows how easy it is to integrate Hipchat notifications from your Pantheon project using Quicksilver. As a bonus, we also show you how to manage API keys outside of your site repository.

## Instructions ##

Setting up this example is easy:

- Within the Hipchat administration area, create an auth token with the `Send Notification` permission. (Rooms -> RoomID -> Tokens)
- Copy the secret token and room id into a file called `secrets.json` and store it in the private files area of your site

```shell
  $> echo '{"hipchat_room_id": "DevChat", "hipchat_auth_token": "a5boORCcbLnqW4kpa71Os37aPZsnc9EXkGDwY0GE"}' > secrets.json
  # Note, you'll need to copy the secrets into each environment where you want to trigger Hipchat notifications.
  $> `terminus site connection-info --env=dev --site=your-site --field=sftp_command`
      Connected to appserver.dev.d1ef01f8-364c-4b91-a8e4-f2a46f14237e.drush.in.
  sftp> cd files
  sftp> mkdir private
  sftp> cd private
  sftp> put secrets.json
```

- Add the example `hipchat_notification` directory to the `private/scripts` directory of your code repository.
- Add the Quicksilver operations to your `pantheon.yml` to fire the scripts when code is synced and deployed.
- Test out a code commit and deployment while watching your Hipchat channel!

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback. You may also want to customize your notifications further. The [Slack API](https://api.slack.com/incoming-webhooks) documentation has more on your options.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if these were the only Quicksilver operations you wanted to use:

```yaml
api_version: 1

workflows:
  deploy:
    after:
      - type: webphp
        description: Hipchat Notification - Deploy
        script: private/scripts/hipchat_notification/hipchat_notification.php
  sync_code:
    after:
      - type: webphp
        description: Hipchat Notification - Sync
        script: private/scripts/hipchat_notification/hipchat_notification.php
```

