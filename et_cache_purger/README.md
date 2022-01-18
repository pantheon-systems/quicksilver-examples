# ET Cache Purger #

This script simply purge the files/et-cache directory which help sites using divi theme having problems with saving content or page edits.

## Instructions ##

1. Add et_cache_purger.php to private/scripts directory
2. Add Quicksilver operations to your `pantheon.yml` best used with clear cache web hook

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use.  Pick and choose the exact workflows that you would like to see notifications for.

```yaml
api_version: 1

workflows:
  clear_cache:
    after:
        - type: webphp
          description: Purged et-cache after clear cache
          script: private/scripts/et_cache_purger.php
  create_cloud_development_environment:
    after: 
        - type: webphp
          description: Purge et-cache after multidev creation
          script: private/scripts/et_cache_purger.php
  deploy:
    after:
        - type: webphp
          description: Purge et-cache after deployment
          script: private/scripts/et_cache_purger.php
  sync_code:
    after:
        - type: webphp
          description: Purge et-cache after code commit
          script: private/scripts/et_cache_purger.php
```

