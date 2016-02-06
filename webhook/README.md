# Webhook #

This example demonstrates how to POST workflow data to an external url.

## Instructions ##

Setting up this example is easy:

- Copy the `webhook` example directory to the `private/scripts` directory of your code repository.
- Update the `$url` value in `private/scripts/webhook/webhook.php` to the destination you would like the workflow data to be posted.
- Add the Quicksilver operations in the example below to your `pantheon.yml`.
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

### Example POST data sent to the webhook url ###

Below is an example of the data sent as a POST request to the `url` defined in `webhook.json`.

This is also the same `$data` passed to `hook_quicksilver($data)` in the drupal module.

```
Array
(
    [payload] => Array
        (
            [wf_type] => sync_code
            [user_id] => af9d4c14-9fd2-4053-aee2-7daf88fb73b5
            [user_firstname] => Finn
            [user_lastname] => Mertens
            [user_fullname] => Finn Mertens
            [site_id] => 0f97107a-e292-431b-aa3e-46f2301f5f82
            [user_role] => owner
            [trace_id] => 1089ead4-c3e2-11e3-a7f5-bc764e10b0cb
            [site_name] => adventure-time
            [environment] => dev
            [wf_description] => Sync code on "dev"
            [user_email] => fmartens@example.com
        )

)
```