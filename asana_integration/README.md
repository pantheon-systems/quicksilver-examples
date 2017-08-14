# Asana Integration #

This example parses commit messages for Asana task IDs and adds the commit message as a comment in the related Asana task.

Example comments:

  [389749465118801]: Adjust layout spacing

Commits that contain multiple Asana tasks will post comments to each issue mentioned. A comment will be added each time a commit is pushed to any dev or multidev branch; each Asana comment is labeled with the appropriate commit hash and Pantheon environment that triggered the post.

## Instructions ##

- In Asana, go to My Profile Settings -> Apps -> Manage Developer Apps -> Create New Personal Access Token and copy the new token.
- Store the Asana Personal Access Token into a file called `secrets.json` and store it in the private files area of your site

```shell
  $> echo '{"asana_access_token" : "Your generated Personal Access Token" }' > secrets.json
  # Note, you'll need to copy the secrets into each environment where you want to save commit messages to Asana
  $> `terminus site connection-info --env=dev --site=your-site --field=sftp_command`
      Connected to appserver.dev.d1ef01f8-364c-4b91-a8e4-f2a46f14237e.drush.in.
  sftp> cd files
  sftp> mkdir private
  sftp> cd private
  sftp> put secrets.json

```
- Add the example `asana_integration.php` script to the `private` directory of your code repository.
- Add a Quicksilver operation to your `pantheon.yml` to fire the script after a deploy.
- Push code with a commit message containing an Asana task ID!

Note: If you open the task in Asana the URL will look something like this: https://app.asana.com/0/389749465118800/389749465118801
The second number is your task ID. Surround it with [] in your commits.

Optionally, you may want to use the `terminus workflow:watch` command to get immediate debugging feedback.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  sync_code:
    after:
      - type: webphp
        description: Asana Integration
        script: private/scripts/asana_integration.php
```
