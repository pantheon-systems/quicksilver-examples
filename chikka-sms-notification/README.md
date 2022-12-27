#**SMS Notification using Chikka API**

This script shows how easy it is to integrate Chikka SMS notifications from your Pantheon project using Quicksilver. As a bonus, we also show you how to manage API keys outside of your site repository.

Instructions

1. [Sign up and Register your Chikka Application](https://api.chikka.com/docs/getting-started#register-your-application) from your Chikka SMS Website.

2. Copy the following variables:

 * chikka_client_id
 * chikka_client_secret
 * chikka_accesscode

  into a file called `secrets.json` and store it in the [private files](https://pantheon.io/docs/articles/sites/private-files/) directory of every environment where you want to trigger Chikka SMS notifications.

  ```
  $> echo '{"chikka_client_id": "xxxxxxxxxxxxxxxxx", "chikka_client_secret": "xxxxxxxxxxxxxx", "chikka_accesscode": "xxxxxxxxxxxxxx"}' > secrets.json
  # Note, you'll need to copy the secrets into each environment where you want to trigger Chikka SMS notifications.
  $> `terminus site connection-info --env=dev --site=your-site --field=sftp_command`
      Connected to appserver.dev.xxxxxxx-xxxxxx-xxxxx-xxxxx.drush.in.
  sftp> cd files  
  sftp> mkdir private
  sftp> cd private
  sftp> put secrets.json
  ```
  
3. Add the example `chikka-sms-notification.php` script to the private directory in the root of your site's codebase, that is under version control. Note this is a different private directory than where the secrets.json is stored.

4. Add Quicksilver operations to your `pantheon.yml`

5. Test a deploy out!

Optionally, you may want to use the terminus workflows watch command to get immediate debugging feedback. You may also want to customize your notifications further. 

Example pantheon.yml

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use. Pick and choose the exact workflows that you would like to see notifications for.

```
api_version: 1

workflows:
  deploy:
    after:
        - type: webphp
          description: send sms on deploy
          script: private/scripts/chikka_sms_notification.php
  sync_code:
    after:
        - type: webphp
          description: send sms on sync code
          script: private/scripts/chikka_sms_notification.php
  clear_cache:
    after:
        - type: webphp
          description: send sms when clearing cache
          script: private/scripts/chikka_sms_notification.php
```
