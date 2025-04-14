# Database sanitization of user emails and passwords. #

This example will show you how you can automatically import a database when creating a new site from the UI.

The use case is multiple, but imagine for example building a microsite distribution, where 

Use cases:

- Automation of microsites
- Automation of branding sites
- Automation of regional sites
- Others?

## Instructions ##

Setting up this example is easy:

1. Add either the db_import_new_site to the `private` directory of your code repository.
2. Add a Quicksilver operation to your `pantheon.yml` to fire the script spining up a new site.
3. Commit and push changes to your repository
4. Test creating a new site out of the current upstream codebase!

Optionally, you may want to use the `terminus workflow:watch yoursitename` command to get immediate debugging feedback.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml

api_version: 1
workflows:
  deploy_product:
    after:
      - type: webphp
        description: Run drush deploy new site creation in dev
        script: private/scripts/new_site.php
```

### TODO ###

Use a similar example executing config import/export for an approach following best practices
