# Pantheon Cloud Integration Examples
This repo contains example scripts for use with Quicksilver Platform Hooks. These will allow you to automate more of your workflow, and integrate better with other cloud services.

The current release of Quicksilver supports one utility operation: `webphp`. This invokes a PHP script via the same runtime environment as the website itself. `php` scripts are subject to the same limits as any code on the platform, [like timeouts](https://pantheon.io/docs/articles/sites/timeouts/#timeouts-that-aren't-configurable), and cannot be batched.

This initial release makes four platform workflows eligible for Quicksilver operations:

- `deploy`: when code is deployed to Test or Live. `webphp` scripts run on the target environment.
- `sync_code`: code is pushed via Git or committed in the Pantheon dashboard. `webphp` scripts run on the committed-to environment (dev or multidev).
- `clone_database`: data is cloned between environments. `webphp` scripts run on the target (to_env) environment.
- `clear_cache`: the most popular workflow of them all! `webphp` scripts run on the cleared environment.

## Introducing `pantheon.yml` ##

Quicksilver is configured via a `pantheon.yml` file, which lives in the root of your repository (`~/code/`). When this file is first pushed to an environment, it will set up the workflow triggers.

The format for `pantheon.yml` is as follows:

```yaml
# Always start with an API version. This will increment as Quicksilver evolves.
api_version: 1

# Now specify the workflows to which you want to hook operations.
workflows:
  deploy:
    # Each workflow can have a before and after operation.
    after:
      # For now, the only "type" available is webphp.
      - type: webphp
        # This will show up in output to help you keep track of your operations.
        description: Log to New Relic
        # This is (obviously) the path to the script.
        script: private/scripts/new_relic_deploy.php
```

Note that if you want to hook onto deploy workflows, you'll need to deploy your `pantheon.yml` into an environment first. Likewise, if you are adding new operations or changing the script an operation will target, the deploy which contains those adjustments to `pantheon.yml` will not self-referentially exhibit the new behavior. Only subsequent deploys will be affected.

**When Updating:**
**pantheon.yml**: Updates will fire on the next sequential workflow, not post-deploy.
**scripts**:  Updates will fire post-deploy.
**script location**: Updates will fire on next sequential workflow, not post-deploy.

**When Adding:**
**pantheon.yml**: Updates will fire on the next sequential workflow, not post-deploy.
**scripts**: Updates will fire on the next sequential workflow.

## Security ##

When getting started with Quicksilver scripts, you'll want to first create **two** `private` directories on your website instance.

The first `private` directory should be created in your `~/files/` directory via SFTP (e.g. `~/files/private/`). This directory is not included in your source code and is used to store a `secrets.json` file where you can confidently store sensitive information like API keys and credentials that may differ between environment. You will need to create a separate `private` directory (and subsequent `secrets.json`) for each environment. You can easily manage the key-value pairs in the `secrets.json` file per environment (after initially creating the file via SFTP) using Terminus after installing the [Terminus Secrets Plugin](https://github.com/pantheon-systems/terminus-secrets-plugin). The Slack notification example uses this pattern. For high-security keys, we recommend a third party secrets lockbox like [Lockr](https://lockr.io).

The second `private` directory should be created in your project's web root (e.g. `~/code/private/` OR `~/code/web/private/` depending on the `web_docroot` setting in your `pantheon.yml` file). This `private` directory is part of your repository, so it should not hold any sensitive information like API keys or credentials. Once you've created the `private` directory, we recommend creating a `scripts` directory within it to store all of your Quicksilver scripts.

Pantheon automatically limits public access to both of these `private` directories, so no special configuration in `pantheon.yml` is required. Scripts stored here can only be executed by the Pantheon platform.

## Terminus Commands ##

Developers making use of Quicksilver will want to make sure they are Terminus savvy. Get the latest release, and a few new commands are included:

```shell
$ terminus help workflows
##NAME
    terminus workflows

##DESCRIPTION
    Actions to be taken on an individual site

##SYNOPSIS
    <command>

##SUBCOMMANDS
    list
        List workflows for a site
    show
        Show operation details for a workflow
    watch
        Streams new and finished workflows to the console
```

The `list` and `show` commands will allow you to explore previous workflows and their Quicksilver operations. The `watch` command is a developers best friend: it will set up Terminus to automatically "follow" the workflow activity of your site, dumping back any Quicksilver output along with them.

## Environment variables ##

To discover what environment variables are available to your scripts then take a look at the [debugging_example](debugging_example) script and instructions.

## Troubleshooting ##

- While your scripts can live anywhere, we recommend `private` since that will prevent the contents from ever being directly accessed via the public internet.
- You'll know `pantheon.yml` has been added correctly, and your quicksilver actions are registered when you see a message like the following on `git push`:
  ```
  remote: PANTHEON NOTICE:
  remote:
  remote: Changes to `pantheon.yml` detected.
  remote:
  remote: Successfully applied `pantheon.yml` to the 'dev' environment.
  ```
