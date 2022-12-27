# Automagically Generate Development Content #

This example will show you how to integrate drush devel generate commands into your quicksilver operations, with the practical outcome of generating development content on each DB clone operation. You can use the method shown here to genereate content of any content type you want.

## Instructions ##

Setting up this example is easy:

1. Add the example `generate_dev_content.php` script to the 'private/scripts/' directory of your code repository.
2. Add a Quicksilver operation to your `pantheon.yml` to fire the script before a deploy.
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
        description: Generate development article content after the database clones
        script: private/scripts/generate_dev_content.php
```

