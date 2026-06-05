# Configuration reference (Drupal)

Defaults shipped by `digitalpolygon/polymer-drupal` in its
`config/default.yml`, all under the `drupal.*` namespace (plus the docroot
keys below). Override per project in `polymer/polymer.yml`, per site in
`web/sites/<site>/polymer.yml`, or per site + environment in
`web/sites/<site>/<environment>.polymer.yml` — the extension registers those
site contexts above the project context.

## Docroot

| Key | Default | Description |
| --- | --- | --- |
| `docroot-dir-name` | `web` | Name of the Drupal docroot directory. |
| `docroot` | `${repo.root}/${docroot-dir-name}` | Full docroot path. At runtime the extension replaces this with the path drupal-finder detects, when available. |
| `deploy.docroot` | `${deploy.dir}/${docroot-dir-name}` | Docroot inside the deploy artifact (overrides core's default). |

## `drupal.upgrade`

Drives `drupal:upgrade` and its subcommands.

| Key | Default | Description |
| --- | --- | --- |
| `drupal.upgrade.strategy` | `latest-minor` | One of `semantic`, `latest-minor`, `next-major`, `latest-major`. Major strategies additionally trigger rector and upgrade-status (below). |
| `drupal.upgrade.upgrade-status.ignore-contrib` | `true` | Pass `--ignore-contrib` to `upgrade_status:analyze`. |
| `drupal.upgrade.rector.run-on-major-upgrades` | `true` | Run `drupal:upgrade:rector` automatically before a major-version composer upgrade. |
| `drupal.upgrade.rector.command` | `${composer.bin}/rector` | Rector binary. Requires the project to depend on a rector package (e.g. `palantirnet/drupal-rector`). |
| `drupal.upgrade.rector.config` | `${repo.root}/rector.php` | Rector config file; seeded by `drupal:upgrade:rector:setup` when missing. |
| `drupal.upgrade.rector.paths` | `modules/custom`, `themes/custom`, `profiles/custom` (under `${docroot}`) | Custom-code paths rector processes. |

## `drupal.account` / site identity

| Key | Default | Description |
| --- | --- | --- |
| `drupal.account.name` | *(random)* | Admin account name for site installs; random unless set. |
| `drupal.account.pass` | *(random)* | Admin password; random unless set. |
| `drupal.account.mail` | `no-reply@example.com` | Admin account email. |
| `drupal.site.mail` | `${drupal.account.mail}` | Site email. |
| `drupal.locale` | `en` | Install locale. |
| `drupal.profile.name` | `minimal` | Install profile. |

## Settings files

| Key | Default | Description |
| --- | --- | --- |
| `drupal.local_settings_file` | `${docroot}/sites/${site}/settings/local.settings.php` | Per-site local settings file managed by setup. |
| `drupal.settings_file` | `${docroot}/sites/${site}/settings.php` | The site's settings.php. |

Site setup also generates
`${docroot}/sites/<site>/settings/polymer-extensions.settings.php` from the
settings-file events in `polymer-drupal-contracts` — extensions contribute
snippets, they are not configured here.

## `drupal.drush`

| Key | Default | Description |
| --- | --- | --- |
| `drupal.drush.bin` | `${composer.bin}/drush` | Drush binary. |
| `drupal.drush.dir` | `${docroot}` | Working directory for drush invocations. |
| `drupal.drush.alias-dir` | `${repo.root}/drush/sites` | Where site alias files live. |
| `drupal.drush.aliases.local` | `self` | Alias used locally. |
| `drupal.drush.aliases.ci` | `self` | Alias used in CI. |
| `drupal.drush.default_alias` | `${drupal.drush.aliases.local}` | Default alias. |
| `drupal.drush.alias` | `${drupal.drush.default_alias}` | Alias prepended (as `@alias`) to every drush command. |
| `drupal.drush.ansi` | `true` | Pass `--ansi` to drush. |
| `drupal.drush.sanitize` | `true` | Sanitize databases on sync. |

## `drupal.multisite`

| Key | Default | Description |
| --- | --- | --- |
| `drupal.multisite.sites` | `[default]` | Sites Polymer iterates for multi-site commands. At runtime the extension replaces this with the directories found under `${docroot}/sites`, when detectable. |
| `drupal.multisite.exclude-patterns` | `local.polymer.yml`, `local.drush.yml`, `settings.local.php`, `settings.ddev.php`, `files` | Paths ignored when cloning a site directory for a new multisite. |

## `drupal.cm` (configuration management)

| Key | Default | Description |
| --- | --- | --- |
| `drupal.cm.strategy` | `none` | One of `core-only`, `config-split`, `none`. Config export/import steps only run for `core-only`/`config-split`. |
| `drupal.cm.core.path` | `../config` | Parent directory for config directories, relative to the docroot. |
| `drupal.cm.core.dirs.sync.path` | `${drupal.cm.core.path}/default` | The sync directory. |
| `drupal.cm.core.install_from_config` | `false` | Install the site directly from exported config. Incompatible with install profiles implementing `hook_install`. |

## `drupal.setup`

| Key | Default | Description |
| --- | --- | --- |
| `drupal.setup.strategy` | `install` | One of `install` (drush si), `sync` (pull from a remote), `import` (load a dump). |
| `drupal.setup.dump-file` | `null` | Dump file imported when strategy is `import`; path relative to the docroot. |
| `drupal.setup.install-args` | `install_configure_form.enable_update_status_module=NULL` | Extra arguments passed to `drush si`. |

## `drupal.sync`

| Key | Default | Description |
| --- | --- | --- |
| `drupal.sync.public-files` | `false` | Sync public files during `sync:refresh` (or pass `-D sync.public-files=true`). |
| `drupal.sync.private-files` | `false` | Sync private files. |
| `drupal.sync.exclude-paths` | `styles`, `css`, `js` | Paths excluded from file syncs. |
| `drupal.sync.commands` | `drupal:site:sync:database`, `drupal:site:sync:files`, `drupal:site:sync:private-files`, `drupal:update` | The command pipeline a sync runs, in order. Reorder/replace per project as needed. |
