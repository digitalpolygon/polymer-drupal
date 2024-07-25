# Drupal Integration for Polymer WebOps Tooling

This repository provides a suite of commands designed to integrate Polymer WebOps Tooling with Drupal, enhancing your
Drupal development experience. These commands streamline various aspects of site management, including multisite
operations, configuration management, and settings initialization.

## Installation

To integrate this Drupal plugin with Polymer using Composer, follow these steps:

1. **Add Plugin Repository**: Add the plugin GitHub repository to the repositories section in your
   project's `composer.json` file.

   ```json
   {
       "repositories": [
           {
               "type": "vcs",
               "url": "git@github.com:digitalpolygon/polymer-drupal.git"
           }
       ]
   }
   ```

1. **Require the Plugin**: Add the plugin to your project's `composer.json` file.

   ```bash
   composer require digitalpolygon/polymer-drupal:dev-main;
   ```

## Commands Overview

### Multisite Operations

1. `polymer drupal:multisite:update-all` (aliases: `dmua`): This command deploys updates across all defined multisites.
   It sequentially switches to each site context and invokes the drupal:update command to ensure all sites are updated.
2. `polymer drupal:update` (aliases: `du`): This command updates the current Drupal site. It clears the cache, updates
   the database, imports configuration, and runs deploy hooks to ensure the site is up-to-date.
3. `drupal:config:import`  (aliases: `dcim`): Imports configuration based on the defined
   strategy (`core-only`, `config-split`, or `none`). It handles `UUID` synchronization and configuration splits, if
   necessary, followed by a cache rebuild.
4. `drupal:deploy:hook` (aliases: `ddh`): Executes deploy hooks defined in the Drush deploy system. These hooks run
   one-time functions necessary after configuration imports.

### Site Creation

1. `drupal:multisite:create`: Initializes a new Drupal multisite by copying configuration from the default site. It
   ensures the new site directory is created and configured properly, adding it to the multisite configuration if using
   DDEV.

### Settings Initialization

1. `drupal:init:settings`: Generates database settings for all defined multisites and ensures the inclusion of Polymer
   settings in each site's `settings.php` file.

## Additional Information

- Ensure you have configured `polymer.yml` correctly with relevant paths and strategies.
- Commands may require specific environment setups like DDEV for multisite creation.
- For detailed documentation on Polymer and Drupal integration, refer to
  the [Polymer Documentation](https://digitalpolygon.github.io/polymer/).

## Contributing

We welcome contributions to enhance the functionality and features of this plugin. To contribute:

1. **Fork the Repository**: Create a personal fork of the repository on GitHub.
2. **Make Improvements**: Implement your changes or bug fixes.
3. **Submit a Pull Request**: Submit a pull request with a clear description of your changes.

Thank you for your interest in improving this plugin!
