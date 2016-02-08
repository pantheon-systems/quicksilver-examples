# Jira Integration #

This example parses commit messages for Jira issue IDs and adds the commit message as a comment in the related Jira issue.

## Instructions ##

- Copy `jira_integration.json` to `files/private` after updating it with your credentials and Jira url.
- Add the example `jira_integration.php` script to the `private` directory of your code repository.
- Add a Quicksilver operation to your `pantheon.yml` to fire the script after a deploy.
- Push code with a commit message containing a Jira issue ID!

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  sync_code:
    after:
      - type: webphp
        description: Jira Integration
        script: private/scripts/jira_integration.php
```
