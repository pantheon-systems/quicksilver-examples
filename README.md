# Quicksilver Examples
Example Quicksilver scripts for Power Users of Pantheon. These will allow you to automate more of your workflow, and integrate better with other cloud services.

The current release of Quicksilver supports one utility operation: `webphp`. This invokes a PHP script via tha same runtime environment as the website itself. 

This initial release makes four platform workflows eligible for Quicksilver operations:

- `deploy`: when code is deployed to Test or Live.
- `sync_code`: code is pushed via Git or committed in the Pantheon dashboard.
- `clone_database`: data is cloned between environments.
- `clear_cache`: the most popular workflow of them all!

## Introducing `pantheon.yml` ##

Quicksilver is configured via a `pantheon.yml` file, which lives in the root of your repository. When this file is first pushed to an environment, it will set up the workflow triggers. 

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

Note that if you want to hook onto deploy workflows, you'll need to deploy your `pantheon.yml` into an environment first. Likewise, if you are adding new operations or changing the script an operation will target, the deploy which contains those adjustments to pantheon.yml will not self-referentially exhibit the new behavior. Only subsequent deploys will be affected.

## Security ##

Quicksilver scripts should be tracked in the `private` directory of your project repository, which is not accessible to the public internat. Scripts there can only be executed by the Pantheon platform. We recommend setting up a dedicated directory (e.g. `private/scripts` or `private/quicksilver`) for tracking these files.

If your scripts use sensitive data like API keys, you may not want to put these keys under version control. You may make use of the private area of the `files` directory to store per-environment keyfiles. The Slack notification example uses this pattern. For high-security keys, we recommend a third party secrets lockbox like [Lockr](https://lockr.io).

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
        List Worflows for a Site
    show
        Show operation details for a workflow
    watch
        Streams new and finished workflows to the console
```

The `list` and `show` commands will allow you to explore previous workflows and their Quicksilver operations. The `watch` command is a developers best-friend: it will set up Terminus to automatically "follow" the workflow activity of your site, dumping back any Quicksilver output along with them.

