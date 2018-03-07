#** External Deploy via phploy (https://github.com/banago/PHPloy/) **

This script allows you to deploy your repo to an external server via ftp/sftp

Instructions

1. Copy the following files to the root of your repository.
 * phploy
 * phploy.ini

2. Edit phploy.ini with the basic (s)ftp details

3. Create and upload secrets.json with the following information
 * username
 * password
 * slack_url (optional)
 * slack_channel (optional)

4. Add the example scripts `external_deploy.php`, `slack_notification.php`, and `get_secrets.inc` private directory in the root of your site's codebase, that is under version control. Note this is a different private directory than where the secrets.json is stored.

5. Add Quicksilver operations to your `pantheon.yml`

6. If you already have your code deployed to your external server, initial your external server to match your current revision, run `phploy --sync` from your local computer
