# Visual Regression Testing via Drulenium #

This example will show you how you can automatically run visual tests on your pantheon hosted environments using Drulenium suite of modules & libraries.

## Instructions ##

Setting up this example is easy:

1. Add the post_drulenium_github.php to the `private` directory of your code repository.
2. Add a Quicksilver operation to your `pantheon.yml` to fire the script after code sync.
3. Test a code sync out!

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

