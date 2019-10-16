# Microsoft Teams Integration #

This script shows how easy it is to integrate Microsoft Teams notifications from your Pantheon project using Quicksilver.

## Instructions ##

Setting up this example is quite easy:

- Within your Microsoft Teams, go to the channel in Teams where you want to integrate the notification and click on the ••• near the name of the channel.
- Click on "Connectors" and search for the Connector "Incoming Webhook" then click on "Configure"
- Set "Pantheon" as the name of this Connector and upload the Pantheon logo image to customize the image this Connector
- Copy the webhook URL into a file called `secrets.json` and store it in the private files area of your site

```shell
  $> echo '{"teams_url": "DevChat"}' > secrets.json
  # Note, you'll need to copy the secrets into each environment where you want to trigger Hipchat notifications.
  $> `terminus site connection-info --env=dev --site=your-site --field=sftp_command`
      Connected to appserver.dev.d1ef01f8-364c-4b91-a8e4-f2a46f14237e.drush.in.
  sftp> cd files
  sftp> mkdir private
  sftp> cd private
  sftp> put secrets.json
```
- Add the example `teams_notifications` directory to the `private/scripts` directory of your code repository.
- Add the Quicksilver operations to your `pantheon.yml` to fire the scripts when code is synced and deployed.

Optionally, you may want to customize your notifications further. In that case, you can update the JSON files stored in the /samples folder. It based on MessageCard that is defined [here](https://docs.microsoft.com/en-us/outlook/actionable-messages/message-card-reference) by Microsoft official documentation.
Note that AdapativeCard format doesn't work with webhook Connector.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if these were the only Quicksilver operations you wanted to use:

```yaml
api_version: 1

workflows:
  deploy:
    after:
      - type: webphp
        description: Microsoft Teams Notification - Deploy
        script: private/scripts/teams_notification/teams_notification.php
  sync_code:
    after:
      - type: webphp
        description: Microsoft Teams Notification - Sync
        script: private/scripts/teams_notification/teams_notification.php
```
