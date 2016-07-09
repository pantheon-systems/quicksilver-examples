# Visual Regression Testing via Drulenium #

This example will show you how you can automatically run visual tests on your pantheon hosted environments using Drulenium suite of modules & libraries. 

This will allow you to do a comparative visual diff between the test environment and the development environemnt(including Multidev environments) everytime you push code to the development environment.

As a note, Tests run on https://gitlab.com/ and is free to use for unlimited number of pages. For more information read http://drulenium.org/how-to-automate-testing-in-pantheon-hosted-website

## Instructions ##

Setting up this example is easy:

1. Add the example visual_regression_test_setup.gitlab.php & visual_regression_test_run.php scripts to the 'private/scripts/drulenium_visual_testing' directory of your code repository.
2. Add the secret gitlab & Drulenium.org account parameters into a file called secrets.json and store it in the [private files](https://pantheon.io/docs/articles/sites/private-files/) directory of Test & Live environments and Clone files from the test environment into other dev environments.

  ```json
    {  
	  "gitlab_token":"***",
	  "gitlab_project":"***",
	  "drulenium_org_api_secret":"***",
	  "drulenium_org_project_uuid":"***"
	}
  ```
  
3. Add a Quicksilver operation to your `pantheon.yml` to fire the script after code sync.
4. Test a code sync out!

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

