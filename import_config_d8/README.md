# Automagically Import Config in D8 #

This example will show you how to automatically import config on each deploy using Drupal 8's configuration management system. 

## Instructions ##

Setting up this example is easy:

1. Add the example `import_config_d8.php` script to the 'private/scripts/' directory of your code repository.
2. Add a Quicksilver operation to your `pantheon.yml` to fire the script before a deploy.
3. Test a deploy out!

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  deploy:
    after:
      - type: webphp
        description: import config into Drupal 8 site
        script: private/scripts/import_config_d8.php
```


