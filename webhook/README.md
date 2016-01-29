# Webhook #

This example demonstrates how to POST workflow data to an external url.

It also contains a Drupal module which can be setup to receive and forward the workflow data through Drupal hooks. i.e. `hook_quicksilver($data)`

## Instructions ##

Setting up this example is easy:

- Copy `webhook.json` to `files/private/webhook.json` after adding the webhook url and an optional api key.
  - The token `:api_key` may be used in the url. It will be replaced with the value of the `api_key` property when data is sent.
- Add the example `webhook.php` script to the `private/scripts/webhook.php` directory of your code repository.
- Add a Quicksilver operations in the example below to your `pantheon.yml`.
- Clear cache, sync code, clone a db, or deploy and take a look at your webhook handler for events!

If using the accompanying Drupal module, you will need to retrieve and set the api key from the configuration page after installation.

You may also set the Drupal module to test mode from the configuration page. This will write all the data received to the db log.

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
            [user_firstname] => Mike
            [user_lastname] => Milano
            [user_fullname] => Mike Milano
            [site_id] => 0f97107a-e292-431b-aa3e-46f2301f5f82
            [user_role] => owner
            [trace_id] => 1089ead4-c3e2-11e3-a7f5-bc764e10b0cb
            [site_name] => quicksilver-examples
            [environment] => dev
            [wf_description] => Sync code on "dev"
            [user_email] => user@example.com
        )

)
```