# Automatically importing wp-cfm settings after cloning a database

A common need is for development environments to have slightly different settings than the production environment.

For example, you my have different configurations for third-party plugins, like google analytics property ids.  Or, you
may want to turn off some plugins in development that aren't needed.

A good way to manage different configurations is by using the [WP-CFM plugin](https://wordpress.org/plugins/wp-cfm/), but
unless you can apply the configurations automatically, you will have to remember to apply them everytime you clone a database
from your production environment.  Fortunately, Quicksilver gives us the opportunity to automate that.

This example will show you how you can automatically apply configurations to your database from WP-CFM configuration files
stored in git.
 
## Instructions

### Install WP-CFM

Install the [wp-cfm plugin](https://wordpress.org/plugins/wp-cfm/) in the usual way.

### Hiding configuration files

By default WP-CFM looks for it's configuration files in `/config`, relative to the document root of your site.  That's
not a great example on Pantheon, because it will be publically browsable. Instead, it would be better to store the files
in `/private/config`.  To do this, add a mu-plugin to your site to set the wp-cfm directory paths early in the 
WordPress loading process.  An example mu-plugin is included in the file `alter_wpcfm_config_path.php` in this repo.  
Just copy it to `wp-config/mu-plugins`.
   
### Creating a WP-CFM configuration file

This should be straight forward, but you need to know which `wp_options` values you should track.  If you don't know, one way to
find out is this:

1. Put your site into sftp mode.
2. Clone your database from the live environment.
3. Create a wp-cfm bundle, tracking all the options, and push it to the filesystem.
4. Make the changes you want to see in your development site.
5. Use the diff functionality to compare the saved bundle with the current state of the database.
6. Update the wp-cfm bundle to track only the options that changed in step 5.
7. Push the bundle to the filesystem.
8. Commit the new bundle files in the Pantheon dashboard.
9. Put your site back into git mode.

### Automating loading the WP-CFM configuration file

You can have as many or as few configurations as you like.
Now we just need to configure our pantheon.yml to actually do the import, triggered after the `db_clone` and `deploy` workflows:

```
---
api_version: 1

workflows:

  # Database Clones: Notify, sanitize, and notify on db clone
  clone_database:
    after:
      - type: webphp
        description: Import configuration with WP-CFM after cloning a database
        script: private/scripts/wp_cfm_after_clone.php

  deploy:
    after:
      - type: webphp
        description: Import configuration with WP-CFM after deployment
        script: private/scripts/wp_cfm_after_clone.php
```
