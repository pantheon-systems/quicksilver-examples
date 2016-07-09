# Visual Regression Testing via Drulenium #

This example will show you how you can automatically run visual tests on your pantheon hosted environments using Drulenium suite of modules & libraries. 

This will allow you to do a comparative visual diff between the test environment and the development environemnt(including Multidev environments) everytime you push code to the development environment.

As a note, Tests run on https://gitlab.com/ and is free to use for unlimited number of pages. For more information read http://drulenium.org/how-to-automate-testing-in-pantheon-hosted-website

## Instructions ##

Setting up this example is easy:

1. Add the example visual_regression_test_setup.gitlab.php & visual_regression_test_run.php scripts to the 'private/scripts/drulenium_visual_testing' directory of your code repository.
2. This script uses drush. While Pantheon does not require a settings.php file to run Drupal, Drush does. Make sure you have one committed to the codebase. You can simply copy the sites/default/default.settings.php to sites/default/settings.php . [Pantheon drush doc](https://pantheon.io/docs/drush/).
3. Be sure the Drulenium module, it's dependencies & libraries are installed into your Drupal codebase.

  ```
    Put your pantheon environment in SFTP mode and run these drush commands.
    Terminus drush "en drulenium drulenium_gitlab -y"
    Terminus drush "gitlab-download-client"
    Terminus drush "vr-download-blockly"
  ```
4. Register at Drulenium.org & configure the module with keys at http://dev-example-qs.pantheonsite.io/drulenium/settings/hosted
5. Register at https://api.imgur.com/oauth2/addclient (Type: Anonymous usage without user authorization) & configure the Drulenium Imgur module with keys at http://dev-example-qs.pantheonsite.io/drulenium/settings/imgur
6. Add test site URL's at http://dev-example-qs.pantheonsite.io/drulenium/settings
7. Go to the Drulenium GitLab module configuration page at http://dev-example.pantheonsite.io/drulenium/settings/gitlab and "Initialize" which will use your Gitlab account and fork the https://gitlab.com/TechNikh/drulenium_gitlab_server project along with setting required build variables you set in 5th, 6th & 7th steps above(DRULENIUM_ORG_API_SECRET, DRULENIUM_ORG_PROJECT_UUID, IMGUR_CLIENT_ID, IMGUR_CLIENT_SECRET, BASELINE_URL, TEST_URL_PREFIX, TEST_URL_SUFFIX).
8. Add the secret gitlab & Drulenium.org account parameters into a file called secrets.json and store it in the [private files](https://pantheon.io/docs/articles/sites/private-files/) directory of Test & Live environments and Clone files from the test environment into other dev environments.

  ```json
    {  
	  "gitlab_token":"***",
	  "gitlab_project":"***",
	  "drulenium_org_api_secret":"***",
	  "drulenium_org_project_uuid":"***"
	}
  ```
9. Add a Quicksilver operation to your `pantheon.yml` to fire the script after code sync.
10. Test a code sync out!

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml

api_version: 1

workflows:
  # Commits: Test visually after each code commit.
  sync_code:
    after:
      - type: webphp
        description: Setup Drulenium with gitlab configuration
        script: private/scripts/drulenium/visual_regression_test_setup.gitlab.php
      - type: webphp
        description: Run Drulenium tests after each code push
        script: private/scripts/drulenium/visual_regression_test_run.php

```

