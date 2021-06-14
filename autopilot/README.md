# Autopilot #

This example demonstrates how to leverage Autopilot variables in Quicksilver for notifications to Slack. A detailed message is sent to Slack after the VRT test runs, indicating which modules were updated, pass/fail the VRT test, and links to the site dashboard.

## Instructions ##

- Copy `autopilot.json` to `files/private` of the *live* environment after updating it with your Slack info.
 - Modify the `webhook_url` in `autopilot.json` to be the desired organization's Slack webhook. 
- Add the example `autopilot.php` script to the `private/scripts` directory of your code repository.
- Add a Quicksilver operation to your `pantheon.yml` to fire the script after an `autopilot_vrt`.
- Deploy through to the live environment and queue updates! Optionally roll a module(s) back a version or 2 to force an update if none are available currently.

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback. You may need to delete the `autopilot` multidev environment after making changes to your `pantheon.yml`.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1
workflows:
  autopilot_vrt:
    after:
      - type: webphp
        description: Autopilot after
        script: private/scripts/autopilot.php
```

After running an Autopilot VRT, if any updates were applied, you should receive a VRT notification.