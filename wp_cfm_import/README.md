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

Install the wp-cfm plugin from the master branch on [Github](https://github.com/forumone/wp-cfm).  (We need to use the
master branch here, because it allows us to set the configuration file locations - this functionality isn't in the
code on wordpress.org yet).

### Hiding configuration files

By default WP-CFM looks for it's configuration files in `/config`, relative to the document root of your site.  That's
not a great example on Pantheon, because it will be publically browsable. Instead, it would be better to store the files
in `/private/config`.  To do this, add a mu-plugin to your site to set the wp-cfm directory paths early in the 
WordPress loading process.  An example mu-plugin is included in the file `alter_wpcfm_config_path.php` in this repo.  
Just copy it to `wp-config/mu-plugins`.
   
### Creating a WP-CFM configuration file

This should be straight forward, but you need to know which _wp_options_ values you should track.  If you don't know, one way to
find out is this:

1. Put your site into sftp mode.
1. Clone your database from the live environment.
2. Create a temporary wp-cfm bundle tracking all the options and push it to the filesystem.
3. Make the changes you want to see in your development site.
4. Use the _diff_ functionality to compare the saved bundle with the current state of the database.
5. The options that have changed are the ones you should track.
6. Re-clone the database from the live environment.
7. Make the changes you want again.
8. Make a new bundle, and track only the options you identified.
9. Push the bundle to the filesystem.
10. Delete the temporary wp-cfm bundle you created.
11. Put your site back into git mode, making sure the new bundle gets saved.

### Automating loading the WP-CFM configuration file

You can have as many or as few configurations as you like.  In `wp_cfm_after_clone.php` change the values in `$config_map` to
set the mapping between environments and filenames.  Follow the instructions in the comments.

Now we just need to configure our pantheon.yml to actually do the import, triggered after the `db_clone` workflow:

```
---
api_version: 1

workflows:

  # Database Clones: Notify, sanitize, and notify on db clone
  clone_database:
    after:
      - type: webphp
        description: Pull environment specific config using wp-cfm after cloning db
        script: private/scripts/wp_cfm_after_clone.php
```
