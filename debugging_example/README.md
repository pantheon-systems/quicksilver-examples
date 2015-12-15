# Quicksilver Debugging #

This example is intended for users who want to explore the potential for Quicksilver with a quick debugging example. 

Setting up this example is easy:

1. Add the example `debug.php` script to the `private` directory of your code repository.
3. Add a Quicksilver operation to your `pantheon.yml` to fire the script after cache clears.
4. Fire up terminus to watch the workflow log.
5. Push everything to Pantheon.
6. Clear the caches and see the output!

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  clear_cache:
    after:
      - type: webphp
        description: Dump debugging output
        script: private/scripts/debug.php
```
