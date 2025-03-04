# Upgrading Drupal

The Polymer Drupal extension provides support for easing the Drupal upgrade
process. This functionality is afforded by the `drupal:upgrade` command, which
leverages the [Drupal Core Composer Updater Plugin](https://github.com/digitalpolygon/drupal-upgrade-plugin).

## Upgrade process

The upgrade process happens in two phases:

1. The upgrade version is determined and the code is upgraded.
2. All sites run database updates and export configuration (if applicable).

### Code upgrade

When you run the `drupal:upgrade` command, the configured upgrade strategy for
the project is used. The extension defaults to the `latest-minor` strategy. The
following strategies are available:

- `latest-minor`: Upgrades to the latest minor release of Drupal within the currently installed major version.
- `next-major`: Upgrades to the next major release of Drupal, relative to the current version installed.
- `latest-major`: Upgrades to the latest major release of Drupal.
- `semantic`: Upgrades your codebase relative to the specified versions constraints in your `composer.json` file (i.e. `composer update`).

If you run `drupal:upgrade --new-version=...`, then no upgrade strategy is used
and instead that specific version of Drupal will be the targeted version
upgraded to.

### Database updates and configuration export

After the code upgrade is complete, all sites are updated and their
configuration is exported (if applicable).

## Finalizing the upgrade

Once the upgrade process has completed, you can add and commit all changes to a
upgrade branch and create a pull request. It is of course recommended to
put the upgrade changes through a QA process before merging the changes into
your main line!
