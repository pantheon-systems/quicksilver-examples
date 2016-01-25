# Webhook #

This example demonstrates how to forward workflow events to an external url.

`api_token` in `webhook.json` is an optional configuration. This value will be sent as a `X-Auth-Key` header to the url.

## Instructions ##

Setting up this example is easy:

- Copy `webhook.json` to the private files area of your site after adding the webhook url and optional api key.
- Add the example `webhook.php` script to the `private/scripts` directory of your code repository.
- Add a Quicksilver operation to your `pantheon.yml` to fire the script after a deploy.
- Clear cache, sync code, clone a db, or deploy and take a look at your webhook handler for events!

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  clear_cache:
    after:
      - type: webphp
        description: Webhook
        script: private/scripts/webhook.php
  clone_database:
    after:
      - type: webphp
        description: Webhook
        script: private/scripts/webhook.php
  deploy:
    after:
      - type: webphp
        description: Webhook
        script: private/scripts/webhook.php
  sync_code:
    after:
      - type: webphp
        description: Webhook
        script: private/scripts/webhook.php
```