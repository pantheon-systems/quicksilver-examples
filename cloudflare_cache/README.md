# Cloudflare Cache #

This example demonstrates how to purge Cloudflare cache when your live environment's cache is cleared.

## Instructions ##

- Copy `cloudflare_cache.json` to `files/private` of the *live* environment after updating it with your cloudflare info.
 - API key can be found in the `My Settings` page on the Cloudflare site.
 - I couldn't find zone id in the UI. I viewed page source on the overview page and found it printed in JavaScript.
- Add the example `cloudflare_cache.php` script to the `private/scripts` directory of your code repository.
- Add a Quicksilver operation to your `pantheon.yml` to fire the script after a deploy.
- Deploy through to the live environment and clear the cache!

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  clear_cache:
    after:
      - type: webphp
        description: Cloudflare Cache
        script: private/scripts/cloudflare_cache.php
```

Note that you will almost always want to clear your CDN cache with the _after_ timing option. Otherwise you could end up with requests re-caching stale content. Caches should generally be cleared "bottom up".