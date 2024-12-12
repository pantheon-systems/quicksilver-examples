# Slack Integration

Hook into platform workflows and post notifications to Slack.

## Instructions

### Set up the Slack App
1. [Navigate to api.slack.com/apps](https://api.slack.com/apps) while logged into your Slack workspace.
1. Click **Create New App**.
1. Choose **From scratch** in the **Create an app** modal.
1. Give your app a name (e.g. "Pantheon Deploybot") and select a workspace for your app.
1. Click **OAuth & Permissions** in the **Features** menu from the left sidebar of your app's configuration screen.
1. Scroll down to **Bot Token Scopes** under **Scopes** and click the **Add an OAuth Scope** button.
1. Choose `chat:write` from the dropdown. You may also add other relevant scopes if you plan on extending the Slack notification Quicksilver script's functionality.
1. Scroll up to **OAuth Tokens** and click the **Install to {your workspace}** button to install the app into your Slack instance.
1. Authorize ("Allow") the app for your workspace.
1. Copy the **Bot User OAuth Token** from the **OAuth Tokens** section. We will use [Pantheon Secrets](https://docs.pantheon.io/guides/secrets/overview) to store this token to a secret, bypassing the need for a local file with an API token stored in version control.
1. You can customize any additional information about your bot, adding an avatar, etc. as you wish.

At this point, you should be able to test the bot manually by sending a `curl` request to the Slack API:

```bash
curl -X POST -H "Authorization: Bearer xoxb-YOUR-TOKEN" \
     -H "Content-Type: application/json" \
     -d '{
           "channel": "#channel-name",
           "text": "Hello from Deploybot!"
         }' \
     https://slack.com/api/chat.postMessage
```

### Add the OAuth token to Pantheon Secrets

1. Install the [Terminus Secrets Manager Plugin](https://docs.pantheon.io/guides/secrets#installation).
1. Set the secret with the following command: `terminus secret:site:set <site> <secret-name> <oauth-token> --scope=web`
  - Replace `<site>` with your site name (e.g. `my-site`).
  - Replace `<secret-name>` with the name of your secret that you will use in the code. In the example script, this is set to `slack_deploybot_token`.
  - Replace `<oauth-token>` with the Bot User OAuth Token copied from the above steps.
1. Add the example `slack_notification.php` script to the `private` directory in the root of your site's codebase, that is under version control.
1. Update the script to set the `$slack_channel` (on line 8) to whatever channel you wish to push notifications to.
1. Update the secret name in the `pantheon_get_secret` function call on line 151 (if you are using a secret that is not named `slack_deploybot_token`).
1. Make any other customizations of the script as you see fit.
1. Add Quicksilver operations to your `pantheon.yml`
1. Test a deployment and see the notification in the Slack channel associated with the webhook.

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback or use the [Workflow Logs](https://docs.pantheon.io/workflow-logs) to return any debugging output. 

**Note:** The example `slack_notification.php` script uses [message attachments](https://api.slack.com/reference/messaging/attachments) to keep the colored sidebar while using the updated API. This can be swapped out in favor of a [block-based](https://api.slack.com/reference/block-kit/blocks) approach entirely if that cosmetic detail is not important to you (we use blocks for everything else).

For an example of the script that doesn't use attachments at all, see [@JBCSU's PR](https://github.com/pantheon-systems/quicksilver-examples/pull/176/files).

### Example `pantheon.yml`

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use. Pick and choose the exact workflows that you would like to see notifications for.

```yaml
api_version: 1

workflows:
  deploy_product:
    after:
      - type: webphp
        description: Post to Slack after site creation
        script: private/scripts/slack_notification.php
  create_cloud_development_environment:
    after:
      - type: webphp
        description: Post to Slack after Multidev creation
        script: private/scripts/slack_notification.php
  deploy:
    after:
      - type: webphp
        description: Post to Slack after deploy
        script: private/scripts/slack_notification.php
  sync_code:
    after:
      - type: webphp
        description: Post to Slack after code commit
        script: private/scripts/slack_notification.php
  clear_cache:
    after:
      - type: webphp
        description: Someone is clearing the cache again
        script: private/scripts/slack_notification.php
```
