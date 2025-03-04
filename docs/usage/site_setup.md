# Site setup

You can run the `polymer drupal:setup:site` command to set up that specific site
in your development environment. Without any other option specified, the site
will be setup with it's configured site strategy. The available site strategies
are:

- `install`: Installs the site via Drush.
- `sync`: Synchronizes the site with a remote database and files.
