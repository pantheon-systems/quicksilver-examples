# Jenkins Integration #

This script shows how easy it is to integrate Jenkins builds from your Pantheon project using Quicksilver. As a bonus, we also show you how to manage API keys/User Data outside of your site repository.

## Instructions ##

Setting up this example is easy:

- Configure a Jenkins Job with a token at https://YOUR_JENKINS_SERVER_NAME/job/JOB_NAME/configure . Found under "Build Triggers" tab, check the "Trigger Builds remotely" checkbox and enter a TOKEN_VALUE.

- Copy the following information into a secrets.json file:
	- jenkins_url: JENKINS_WEBHOOK_URL (https://YOUR_JENKINS_SERVER_NAME/job/JOB_NAME/build)
	- token: TOKEN_VALUE (Setup above)
	- username: USERNAME (Your Jenkins Username)
	- api_token: API_TOKEN (Found at https://YOUR_JENKINS_SERVER_NAME/YOUR_USERNAME/configure under API Token)

```shell
  $> echo '{"jenkins_url": "JENKINS_WEBHOOK_URL","token": "TOKEN_VALUE","username": "USERNAME","api_token": "API_TOKEN"}' > secrets.json
  $> `terminus site connection-info --env=dev --site=your-site --field=sftp_command`
      Connected to appserver.dev.d1ef01f8-364c-4b91-a8e4-f2a46f14237e.drush.in.
  sftp> cd files
  sftp> mkdir private
  sftp> cd private
  sftp> put secrets.json

```

- Add the example `jenkins_integration.php` script to the `private` directory of your code repository.
- Add a Quicksilver operation to your `pantheon.yml` to fire the script a deploy.
- Test a deploy out!

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback. You may also want to record in Jenkins why you are triggering that particular build. You can optionally append '&cause=Cause+Text' to the post data if you want that included in the build records. 

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  deploy:
    after:
        - type: webphp
          description: Integrate With Jenkins
          script: private/scripts/jenkins_integration.php
```
