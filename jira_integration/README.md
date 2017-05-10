# Jira Integration #

This example parses commit messages for Jira issue IDs and adds the commit message as a comment in the related Jira issue.

Example comments:

  MYPROJECT-9: Adjust layout spacing.
  Fixes issues MYPROJECT-4 and MYPROJECT-7.

Commits that contain multiple Jira issues will post comments to each issue mentioned. A comment will be added each time a commit is pushed to any dev or multidev branch; each Jira comment is labeled with the appropriate commit hash and Pantheon environment that triggered the post.

## Instructions ##

- Copy your Jira credentials and the URL to your Jira instance into a file called `secrets.json` and store it in the private files area of your site

```shell
  $> php -r "print json_encode(['jira_url'=>'https://myjira.atlassian.net','jira_user'=>'serviceaccount','jira_pass'=>'secret']);" > secrets.json
  # Note, you'll need to copy the secrets into each environment where you want to save commit messages to Jira
  $> `terminus connection:info --field=sftp_command <site>.<env>`
      Connected to appserver.dev.d1ef01f8-364c-4b91-a8e4-f2a46f14237e.drush.in.
  sftp> cd files
  sftp> mkdir private
  sftp> cd private
  sftp> put secrets.json

```
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
