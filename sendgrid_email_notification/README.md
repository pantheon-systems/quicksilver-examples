# Sendgrid Email notification #

This script shows how easy it is to integrate Sendgrid emails from your Pantheon project using Quicksilver. As a bonus, we also show you how to manage API keys outside of your site repository.

## Instructions ##

1. Copy the secret Webhook URL into a file called `secrets.json` and store it in the [private files](https://pantheon.io/docs/articles/sites/private-files/) directory of every environment where you want to trigger Slack notifications.

  ```shell
    $> echo '{"sg_username": "YOUR_SENDGRID)USERNAME","sg_password": "YOUR_SENDGRID_PASSWORD"}' > secrets.json
    # Note, you'll need to copy the secrets into each environment where you want to trigger Sendgrid email notifications.
    $> `terminus site connection-info --env=dev --site=your-site --field=sftp_command`
        Connected to appserver.dev.d1ef01f8-364c-4b91-a8e4-f2a46f14237e.drush.in.
    sftp> cd files  
    sftp> mkdir private
    sftp> cd private
    sftp> put secrets.json
    sftp> quit
  ```

3. Add, and update as needed, the example `send_email.php` script to the `private` directory in the root of your site's codebase, that is under version control. Note this is a different `private` directory than where the secrets.json is stored.
4. Add Quicksilver operations to your `pantheon.yml`
5. Test a deploy out!

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback. You may also want to customize your notifications further.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use.  Pick and choose the exact workflows that you would like to see notifications for.

```yaml
api_version: 1

workflows:
  deploy_product:
    after:
        - type: webphp
          description: Send email after site creation
          script: private/scripts/send_email.php
  create_cloud_development_environment:
    after:
        - type: webphp
          description: Send email after Multidev creation
          script: private/scripts/send_email.php
  deploy:
    after:
        - type: webphp
          description: Send email after deploy
          script: private/scripts/send_email.php
  sync_code:
    after:
        - type: webphp
          description: Send email after code commit
          script: private/scripts/send_email.php
  clear_cache:
    after:
        - type: webphp
          description: Someone is clearing the cache again
          script: private/scripts/send_email.php
```

