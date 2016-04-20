# Visual Regression Testing via Drulenium #

This example will show you how you can automatically run visual tests on your pantheon hosted environments using Drulenium suite of modules & libraries. 

This will allow you to do a comparative visual diff between the test environment and the development environemnt(including Multidev environments) everytime you push code to the development environment.

As a note, Tests run on https://travis-ci.org/ and is free to use for unlimited number of pages but the test results are public. For more information read http://drulenium.org/how-to-automate-testing-in-pantheon-hosted-website

## Instructions ##

Setting up this example is easy:

1. Add the example post_drulenium_github.php script to the 'private/scripts/' directory of your code repository.
2. Add the secret github account parameters into a file called secrets.json and store it in the private files directory of Test & Live environments and Clone files from the test environment into other dev environments. ```{
  "github_username": "Drulenium",
  "github_repository": "pantheon-travis",
  "github_accesstoken": "a06e6d536db8743056e1faae60aa803a0b17b13f",
  "github_master_branch_sha": "0600c12ea73e185ac7f29a2d33deda1708672996"
}```
3. Modify the post_drulenium_github.php script to include your Site pages & email to notify upon completion of the test run.
4. Add a Quicksilver operation to your `pantheon.yml` to fire the script after code sync.
5. Test a code sync out!

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
        description: test visually after each code push
        script: private/scripts/post_drulenium_github.php
```

