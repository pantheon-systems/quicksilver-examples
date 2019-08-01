#**SMS Notification using Nexmo API**

This script shows how easy it is to integrate Nexmo SMS notifications from your Pantheon project using Quicksilver. As a bonus, we also show you how to manage API keys outside of your site repository.

Instructions

1. [Sign up and Register at Nexmo and get free credits](https://dashboard.nexmo.com/sign-up) from your Nexmo SMS Website. You may use this sms api in more 225 countries. 

2. Run composer in root folder of nexmo plugin
```
composer require nexmo/client
```

3. Copy the following variables:

 * nexmo_api_key
 * nexmo_api_secret
 * primary_mobile_number

  into a file called `secrets.json` and store it in the [private files](https://pantheon.io/docs/articles/sites/private-files/) directory of every environment where you want to trigger Nexmo SMS notifications.

  ```
  $> echo '{"nexmo_api_key": "xxxxxxxxxxxxxxxxx", "nexmo_api_secret": "xxxxxxxxxxxxxx", "primary_mobile_number": "xxxxxxxxxxxxxx"}' > secrets.json
  # Note, you'll need to copy the secrets into each environment where you want to trigger Chikka SMS notifications.
  $> `terminus site connection-info --env=dev --site=your-site --field=sftp_command`
      Connected to appserver.dev.xxxxxxx-xxxxxx-xxxxx-xxxxx.drush.in.
  sftp> cd files  
  sftp> mkdir private
  sftp> cd private
  sftp> put secrets.json
  ```
  
4. Add the example `nexmo_sms_notification/notify.php` script to the private directory in the root of your site's codebase, that is under version control. Note this is a different private directory than where the secrets.json is stored. Make sure that you include the vendors inside nexmo_sms_notification/ folder


5. Add Quicksilver operations to your `pantheon.yml`

6. Test a deploy from your dashboard!

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
          script: private/scripts/nexmo_sms_notification/notify.php
  sync_code:
    after:
        - type: webphp
          description: send sms on sync code
          script: private/scripts/nexmo_sms_notification/notify.php
  clear_cache:
    after:
        - type: webphp
          description: send sms when clearing cache
          script: private/scripts/nexmo_sms_notification/notify.php
```
