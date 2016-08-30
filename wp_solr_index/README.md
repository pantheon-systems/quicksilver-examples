# Solr Power Indexing on Multidev Creation for WordPress #

When you create a new multidev environment the code, files and database are cloned but not the Solr instance. This script will re-index Solr using WP-CLI and the [Solr Power WordPress](https://github.com/pantheon-systems/solr-power) plugin.

## Instructions ##

1. Install and activate the Solr Power WordPress plugin in the dev environment.
2. Add the example `solr_power_index.php` script to the `private/scripts` directory in the root of your site's codebase, that is under version control. 
3. Add the Quicksilver operations to your `pantheon.yml`.
4. Deploy `pantheon.yml` to the dev environment.
5. Create a new multidev instance
	* Optionally run `terminus workflows watch` locally to get feedback from the Quicksilver script.
6. After indexing completes, test out search or other items using Solr.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use.

```yaml
api_version: 1

workflows:
  create_cloud_development_environment:
    after:
        - type: webphp
          description: Index Solr Power items after multidev creation
          script: private/scripts/wp_solr_power_index.php
```

