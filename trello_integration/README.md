# Trello Integration #

This example parses commit messages for Trello card IDs and adds the commit message as a comment in the related Jira issue.

Example comments:

  s3yxNR5v: Adjust layout spacing

Commits that contain multiple Trello cards will post comments to each issue mentioned. A comment will be added each time a commit is pushed to any dev or multidev branch; each Trello comment is labeled with the appropriate commit hash and Pantheon environment that triggered the post.

## Instructions ##

- Go to https://trello.com/app-key and copy your app key. Also click the link to generate a token for yourself, approve access and copy the token.
- Copy your Trello credentials (key + token) into a file called `secrets.json` and store it in the private files area of your site

```shell
  $> echo '{"trello_key" : "Your App Key" , "trello_token" : "Your generated token" }' > secrets.json
  # Note, you'll need to copy the secrets into each environment where you want to save commit messages to Trello
  $> `terminus site connection-info --env=dev --site=your-site --field=sftp_command`
      Connected to appserver.dev.d1ef01f8-364c-4b91-a8e4-f2a46f14237e.drush.in.
  sftp> cd files
  sftp> mkdir private
  sftp> cd private
  sftp> put secrets.json

```
- Add the example `trello_integration.php` script to the `private` directory of your code repository.
- Add a Quicksilver operation to your `pantheon.yml` to fire the script after a deploy.
- Push code with a commit message containing a Trello card ID!

Optionally, you may want to use the `terminus workflow:watch` command to get immediate debugging feedback.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  sync_code:
    after:
      - type: webphp
        description: Trello Integration
        script: private/scripts/trello_integration.php
```
