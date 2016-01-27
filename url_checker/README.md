# Status Check #

This example demonstrates how check specific URLs after a live deployment.

Each URL will be checked for a return status of 200. Failures will be emailed to the address defined in `config.json`.

Note: This example could also be used to warm up cache after a live deployment.

## Instructions ##

- Copy the example `url_checker` directory to the `private/scripts` directory of your code repository.
- Add a Quicksilver operation to your `pantheon.yml` to fire the script after a deploy. Be sure to target the file for your platform.
- Test a deploy out!

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback.

Here is an example of what you might see when using `terminus workflows watch`:

```
URL Checks
--------
  200 - https://example.com/
  200 - https://example.com/user
  404 - https://example.com/bad-path
--------
1 failed
```

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  deploy:
    after:
      - type: webphp
        description: URL Checker
        script: private/scripts/url_checker/url_checkerk_(drupal8|drupal7|drupal6|wordpress).php
```