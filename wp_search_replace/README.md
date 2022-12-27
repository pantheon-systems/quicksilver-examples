# Search and Replace URLs on WordPress Sites #

This example will show you how you can automatically find and replace URLs in the database on a WordPress website. This practice can help smooth out workflow gotchas with sites that have multiple domains in an environment.

## Instructions ##

Setting up this example is easy:

1. Add the wp_search_replace.php to the `private` directory of your code repository.
2. Add a Quicksilver operation to your `pantheon.yml` to fire the script a deploy.
3. Test a deploy out!

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml

api_version: 1

workflows:
  clone_database:
    after:
      - type: webphp
        description: Search and replace url in database
        script: private/scripts/search-replace-example.php
```
