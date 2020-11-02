# Quicksilver Pushback #

This Quicksilver project is used in conjunction with the various suite of [Terminus Build Tools](https://github.com/pantheon-systems/terminus-build-tools-plugin)-based example repositories to push any commits made on the Pantheon dashboard back to the original Git repository for the site. This allows developers (or other users) to work on the Pantheon dashboard in SFTP mode and commit their code, through Pantheon, back to the canonical upstream repository via a PR. This is especially useful in scenarios where you want to export configuration (Drupal, WP-CFM).

This project is maintained in it's own repo located at https://github.com/pantheon-systems/quicksilver-pushback. Check that page for more information about the project, including installation instructions. Please note that it comes installed automatically if you use the Terminus Build Tools plugin, so you probably don't need to install it yourself unless you're following a non-standard workflow.
