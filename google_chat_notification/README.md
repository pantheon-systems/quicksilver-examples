# Google Chat Integration

Hook into platform workflows and post notifications to Google Chat.

## Instructions

1. [Register an Incoming Webhook](https://developers.google.com/chat/how-tos/webhooks) for a Google Chat space.
2. Copy the secret Webhook URL into a file called `secrets.json` and store it in the [private files](https://pantheon.io/docs/articles/sites/private-files/) directory of each environment where you want to trigger Slack notifications. The secret WebHook URL is like a password, which should not be stored in version control.

    ```shell
      $> echo '{"google_chat_webhook": "https://chat.googleapis.com/v1/spaces/AAAAMBwMFRY/messages?key=<KEY>&token=<TOKEN>"}' > secrets.json
      # Note, you will need to copy the secrets into each environment where you want to trigger Google Chat notifications.
      $> `terminus connection:info  --field=sftp_command site.env`
          Connected to appserver.dev.<SITE_ID>.drush.in.
      sftp> cd files
      sftp> mkdir private
      sftp> cd private
      sftp> put secrets.json
      sftp> quit
    ```

3. Add the example `google_chat_notification.php` script to the `private` directory in the root of your site's codebase, that is under version control. Note this is a different `private` directory than where the `secrets.json` is stored.
4. Add Quicksilver operations to your `pantheon.yml`
5. Test a deployment and see the notification in the Google Chat space associated with the webhook.

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback. You may also want to customize your notifications further. The [Google Chat API Reference](https://developers.google.com/chat/api/reference/rest/v1/spaces.messages) documentation has more information on options that are available.

### Example `pantheon.yml`

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use. Pick and choose the exact workflows that you would like to see notifications for.

```yaml
api_version: 1

workflows:
  deploy_product:
    after:
      - type: webphp
        description: Post to Google Chat after site creation
        script: private/scripts/google_chat_notification/google_chat_notification.php
  create_cloud_development_environment:
    after:
      - type: webphp
        description: Post to Google Chat after Multidev creation
        script: private/scripts/google_chat_notification/google_chat_notification.php
  deploy:
    after:
      - type: webphp
        description: Post to Google Chat after deploy
        script: private/scripts/google_chat_notification/google_chat_notification.php
  sync_code:
    after:
      - type: webphp
        description: Post to Google Chat after code commit
        script: private/scripts/google_chat_notification/google_chat_notification.php
  clear_cache:
    after:
      - type: webphp
        description: Post to Google Chat after cache clear
        script: private/scripts/google_chat_notification/google_chat_notification.php
  clone_database:
    after:
      - type: webphp
        description: Post to Google Chat after database clone
        script: private/scripts/google_chat_notification/google_chat_notification.php
  autopilot_vrt:
    after:
      - type: webphp
        description: Post to Google Chat after Autopilot VRT
        script: private/scripts/google_chat_notification/google_chat_notification.php
```
