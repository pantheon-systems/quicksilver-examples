# Retry Build on Circle CI #

This script will restart a Circle CI build on demand, e.g. to rerun tests
whenever the database is cloned in the test environment.

## Instructions ##

- Create an API token per the [Circle CI REST API Getting Started section](https://circleci.com/docs/api#getting-started).
- Copy the secret Circle CI token into a file called `secrets.json` and store it in the private files area of your site

```shell
  $> echo '{"circle_token": "0567e0b6d..."}' > secrets.json
  # Note, you'll need to copy the secrets into each environment where you want to trigger CI build retries.
  $> `terminus site connection-info --env=test --site=your-site --field=sftp_command`
      Connected to appserver.dev.d1ef01f8-364c-4b91-a8e4-f2a46f14237e.drush.in.
  sftp> cd files
  sftp> mkdir private
  sftp> cd private
  sftp> put secrets.json
```

- Alternately, copy the `secrets` script to a directory in your $PATH, and use it to write your token to the secrets.json file:

```shell
  $> sudo cp scripts/secrets /usr/local/bin
  $> secrets your-site env circle_token "0567e0b6d..."
```

- Add the example `circle_retry.php` script to the `private/scripts` directory of your code repository.
- Customize the circle-retry-parameters.json file. You may place this file either in `files/private`, as shown above, or in `private/scripts`. n.b. These locations will be merged together, allowing parameters be overriddent in `files/private` on a per-environment basis. If you wish, you may also mix your non-secret parameters with your secrets, and store them in your secrets.json file. If you do this, you may set your parameters with the `secrets` command as shown above.
- Add a Quicksilver operation to your `pantheon.yml` to fire the script a deploy.
- Test a deploy out!

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback. You may also want to customize your notifications further. The [Slack API](https://api.slack.com/incoming-webhooks) documentation has more on your options.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  deploy:
    after:
        - type: webphp
          description: Retry a Circle CI build
          script: private/scripts/circle_retry.php
