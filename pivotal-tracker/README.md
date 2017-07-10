# Pivotal Tracker Integration #

This example parses commit messages for Pivotal Tracker story IDs and adds the commit message as an activity to the story.

Example comments:

```shell
  [#148528125] Making a change to this story.
  I made a change to [#148528125] addressing the functionality.
```

The Pivotal Tracker API will also change story status by including "fixed", "completed", or "finished" within the square brackets, in addition to the story ID. You may use different cases or forms of these verbs, such as "Fix" or "FIXES", and they may appear before or after the story ID. For features, one of these keywords will put the story in the finished state. For chores, it will put the story in the accepted state.

If code is automatically deployed, use the keyword "delivers" and feature stories will be put in the "delivered" state, rather than "completed." Examples:

```shell
  [Completed #148528125] adding requested feature.
  I finally [finished #148528125] this functionality.
  This commit [fixes #148528125]
  [Delivers #148528125] Small bug fix.
```

Commits that contain multiple Tracker stories will post activity to each story. Activity will be updated each time a commit is pushed to any dev or multidev branch; each message is labeled with the appropriate commit hash and Pantheon environment that triggered the post.

## Instructions ##

- Copy your Tracker API token into a file called `secrets.json` and store it in the private files area of your site

```shell
        SITE=<site>
        $ echo '{}' > secrets.json
        $ `terminus connection:info $SITE.dev --field=sftp_command`
        sftp> put ./files/private secrets.json
        sftp> bye
        terminus secrets:set $SITE.dev tracker_token <token>
        pivotal-tracker $terminus secrets:list $SITE.dev //verify
        $ rm secrets.json
                
```
- Add the example `pivotal_integration.php` script to the `private` directory of your code repository (at the docroot, not in the aforementioned files/private directory).
- Add a Quicksilver operation to your `pantheon.yml` to fire the script after a deploy.
- Push code with a commit message containing a Pivotal story ID!

Optionally, you may want to use the `terminus workflows watch` command to get immediate debugging feedback.

### Example `pantheon.yml` ###

Here's an example of what your `pantheon.yml` would look like if this were the only Quicksilver operation you wanted to use:

```yaml
api_version: 1

workflows:
  sync_code:
    after:
      - type: webphp
        description: Jira Integration
        script: private/pivotal_integration.php
```
