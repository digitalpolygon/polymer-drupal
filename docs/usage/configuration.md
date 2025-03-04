# Site-specific configuration

You can create site-specific configuration by placing `polymer.yml` and
`[env].polymer.yml` files in site directories. For example:

- `<docroot>/sites/default/polymer.yml`
- `<docroot>/sites/default/local.polymer.yml`

The site configuration context is loaded very late in the configuration
processor, so configuration specified here will override most other
configuration already provided by other contexts.

!!! info

    You can use the `--site` option to specify the site (identified by the site
    directory name) you want to use as the context for the command ran.

    For example, run `polymer drupal:setup:site --site=my_site` to run site
    setup operations, but using additional configuration specified within the
    `<docroot>/sites/my_site/polymer.yml` file.
