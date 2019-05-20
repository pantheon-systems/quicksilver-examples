# Visual Regression Testing via Diffy.website #

This example will show you how to integrate [Diffy.website](http://Diffy.website)'s visual regression operation into your deployment workflow.

This will allow you to do a comparative visual diff between the live environment and the test environemnt everytime you deploy to the testing environment.

For more advanced use cases, including doing visual regression against Multidev instances, this script can be easily adapted for Diffy.website's REST API: [https://diffy.website/rest](https://diffy.website/rest).

## Instructions ##

In order to get up and running, you first need to setup a Diffy.website project:

1. Either login to your account or register for a new one at [https://diffy.website](https://diffy.website).
2. Setup a Diffy project for your site and define the Production and Staging URLs in the project settings.

Then you need to add the relevant code to your Pantheon project:

1. Add the example `diffyVisualregression.php` script to the 'private/scripts/' directory of your code repository.
2. Create an API token in Diffy (). Copy the token and project_id into a file called `secrets.json` and store it in the [private files](https://pantheon.io/docs/articles/sites/private-files/) directory.

     ```shell
       $> echo '{"token": "yourToken", "project_id" : "123"}' > secrets.json
       sftp> cd files
       sftp> mkdir private
       sftp> cd private
       sftp> put secrets.json
       sftp> quit
       ```

3. Add a Quicksilver operation to your `pantheon.yml` to fire the script after a deploy to test.
4. Test a deploy out!

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  deploy:
    after:
      - type: webphp
        description: do a visual regression test with Diffy.website
        script: private/scripts/DiffyVisualregression.php
```
