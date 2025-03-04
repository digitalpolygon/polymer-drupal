# Continuous integration

This extension wants to make it easy to roll out CI process for your Drupal
application.

## Supported platforms and workflows

- GitHub Actions
    - Automatic upgrades with a pull request
    - Composer diff

### GitHub Actions

#### Automatic upgrades with a pull request

This workflow does the following:

1. Sets up all sites using each site's configured setup strategy.
2. Runs the configured Drupal upgrade strategy.
3. For each site, runs database updates and exports configuration (if applicable).
4. Creates a pull request with the changes.

##### Pre-requisites

###### DDEV

The OOTB workflow file assumes the project uses DDEV, and it executes commands
via DDEV in the workflow.

###### Create or reuse a custom GitHub application

You will need to have a [custom GitHub application](https://docs.github.com/en/apps/creating-github-apps) configured with the following
permissions:

- Read access to members and data
- Read and write access to code and pull requests

Ensure to enable the application for the repository for your project.

We need to create two repository secrets: `DP_PR_APP_ID` and `DP_PR_APP_KEY`.
The `DP_PR_APP_ID` is the ID of the GitHub application. The `DP_PR_APP_KEY`
is a [private key associated with the GitHub app](https://docs.github.com/en/apps/creating-github-apps/authenticating-with-a-github-app/managing-private-keys-for-github-apps).

###### SSH key

Finally, you need to add a repository secret, `SSH_KEY`, that
contains a private SSH key. You should only need this if any of your site setup
strategies depend on having this SSH key available.

#### Composer diff

This workflow runs on every pull request that contains changes to
`composer.lock`. It will post, as a sticky comment on the pull request,
a table with a row for each package changed with the following fields:

- Package name
- Change type
- Previous version
- New version
- Link to package changes code diff (Drupal packages are supported!)

It relies on the `Composer Diff` plugin to generate a
version diff of the changes made to the `composer.lock` file.

??? example "Example Composer diff table of changes"

    | Prod Packages                                                                                   | Operation | Base             | Target      | Link                                                                                                                                  |
    |-------------------------------------------------------------------------------------------------|-----------|------------------|-------------|---------------------------------------------------------------------------------------------------------------------------------------|
    | [composer/installers](https://github.com/composer/installers)                                   | Upgraded  | v2.2.0           | v2.3.0      | [Compare](https://github.com/composer/installers/compare/v2.2.0...v2.3.0)                                                             |
    | [digitalpolygon/drupal-upgrade-plugin](https://github.com/digitalpolygon/drupal-upgrade-plugin) | Changed   | dev-main 87f2362 | 1.0.0-beta1 | [Compare](https://github.com/digitalpolygon/drupal-upgrade-plugin/compare/87f2362...1.0.0-beta1)                                      |
    | [doctrine/annotations](https://github.com/doctrine/annotations)                                 | Upgraded  | 1.14.3           | 1.14.4      | [Compare](https://github.com/doctrine/annotations/compare/1.14.3...1.14.4)                                                            |
    | [doctrine/deprecations](https://github.com/doctrine/deprecations)                               | Upgraded  | 1.1.3            | 1.1.4       | [Compare](https://github.com/doctrine/deprecations/compare/1.1.3...1.1.4)                                                             |
    | [drupal/core](https://www.drupal.org/project/drupal)                                            | Upgraded  | 10.3.0           | 10.4.3      | [Compare](https://github.com/drupal/core/compare/6f1af3070110d7d0f2a6671bea26add34667f765...b9ecec3637e19050a3ab5fe14f6d84e9e00c9abd) |
    | [drupal/core-composer-scaffold](https://github.com/drupal/core-composer-scaffold)               | Upgraded  | 10.3.0           | 10.4.3      | [Compare](https://github.com/drupal/core-composer-scaffold/compare/10.3.0...10.4.3)                                                   |
    | [drupal/core-project-message](https://github.com/drupal/core-project-message)                   | Upgraded  | 10.3.0           | 11.1.3      | [Compare](https://github.com/drupal/core-project-message/compare/10.3.0...11.1.3)                                                     |
    | [drupal/core-recommended](https://github.com/drupal/core-recommended)                           | Upgraded  | 10.3.0           | 10.4.3      | [Compare](https://github.com/drupal/core-recommended/compare/10.3.0...10.4.3)                                                         |
    | [egulias/email-validator](https://github.com/egulias/EmailValidator)                            | Upgraded  | 4.0.2            | 4.0.3       | [Compare](https://github.com/egulias/EmailValidator/compare/4.0.2...4.0.3)                                                            |
    | [guzzlehttp/guzzle](https://github.com/guzzle/guzzle)                                           | Upgraded  | 7.8.2            | 7.9.2       | [Compare](https://github.com/guzzle/guzzle/compare/7.8.2...7.9.2)                                                                     |
    | [guzzlehttp/psr7](https://github.com/guzzle/psr7)                                               | Upgraded  | 2.6.3            | 2.7.0       | [Compare](https://github.com/guzzle/psr7/compare/2.6.3...2.7.0)                                                                       |
    | [mck89/peast](https://github.com/mck89/peast)                                                   | Upgraded  | v1.16.2          | v1.16.3     | [Compare](https://github.com/mck89/peast/compare/v1.16.2...v1.16.3)                                                                   |
    | [pear/pear-core-minimal](https://github.com/pear/pear-core-minimal)                             | Upgraded  | v1.10.15         | v1.10.16    | [Compare](https://github.com/pear/pear-core-minimal/compare/v1.10.15...v1.10.16)                                                      |
    | [symfony/dependency-injection](https://github.com/symfony/dependency-injection)                 | Upgraded  | v6.4.16          | v6.4.19     | [Compare](https://github.com/symfony/dependency-injection/compare/v6.4.16...v6.4.19)                                                  |
    | [symfony/error-handler](https://github.com/symfony/error-handler)                               | Upgraded  | v6.4.8           | v6.4.19     | [Compare](https://github.com/symfony/error-handler/compare/v6.4.8...v6.4.19)                                                          |
    | [symfony/http-foundation](https://github.com/symfony/http-foundation)                           | Upgraded  | v6.4.8           | v6.4.18     | [Compare](https://github.com/symfony/http-foundation/compare/v6.4.8...v6.4.18)                                                        |
    | [symfony/http-kernel](https://github.com/symfony/http-kernel)                                   | Upgraded  | v6.4.8           | v6.4.19     | [Compare](https://github.com/symfony/http-kernel/compare/v6.4.8...v6.4.19)                                                            |
    | [symfony/mailer](https://github.com/symfony/mailer)                                             | Upgraded  | v6.4.8           | v6.4.18     | [Compare](https://github.com/symfony/mailer/compare/v6.4.8...v6.4.18)                                                                 |
    | [symfony/mime](https://github.com/symfony/mime)                                                 | Upgraded  | v6.4.8           | v6.4.19     | [Compare](https://github.com/symfony/mime/compare/v6.4.8...v6.4.19)                                                                   |
    | [symfony/polyfill-ctype](https://github.com/symfony/polyfill-ctype)                             | Upgraded  | v1.29.0          | v1.31.0     | [Compare](https://github.com/symfony/polyfill-ctype/compare/v1.29.0...v1.31.0)                                                        |
    | [symfony/polyfill-iconv](https://github.com/symfony/polyfill-iconv)                             | Upgraded  | v1.29.0          | v1.31.0     | [Compare](https://github.com/symfony/polyfill-iconv/compare/v1.29.0...v1.31.0)                                                        |
    | [symfony/polyfill-intl-grapheme](https://github.com/symfony/polyfill-intl-grapheme)             | Upgraded  | v1.29.0          | v1.31.0     | [Compare](https://github.com/symfony/polyfill-intl-grapheme/compare/v1.29.0...v1.31.0)                                                |
    | [symfony/polyfill-intl-idn](https://github.com/symfony/polyfill-intl-idn)                       | Upgraded  | v1.29.0          | v1.31.0     | [Compare](https://github.com/symfony/polyfill-intl-idn/compare/v1.29.0...v1.31.0)                                                     |
    | [symfony/polyfill-intl-normalizer](https://github.com/symfony/polyfill-intl-normalizer)         | Upgraded  | v1.29.0          | v1.31.0     | [Compare](https://github.com/symfony/polyfill-intl-normalizer/compare/v1.29.0...v1.31.0)                                              |
    | [symfony/polyfill-mbstring](https://github.com/symfony/polyfill-mbstring)                       | Upgraded  | v1.29.0          | v1.31.0     | [Compare](https://github.com/symfony/polyfill-mbstring/compare/v1.29.0...v1.31.0)                                                     |
    | [symfony/polyfill-php83](https://github.com/symfony/polyfill-php83)                             | Upgraded  | v1.29.0          | v1.31.0     | [Compare](https://github.com/symfony/polyfill-php83/compare/v1.29.0...v1.31.0)                                                        |
    | [symfony/process](https://github.com/symfony/process)                                           | Upgraded  | v6.4.15          | v6.4.19     | [Compare](https://github.com/symfony/process/compare/v6.4.15...v6.4.19)                                                               |
    | [symfony/psr-http-message-bridge](https://github.com/symfony/psr-http-message-bridge)           | Upgraded  | v6.4.8           | v6.4.13     | [Compare](https://github.com/symfony/psr-http-message-bridge/compare/v6.4.8...v6.4.13)                                                |
    | [symfony/routing](https://github.com/symfony/routing)                                           | Upgraded  | v6.4.8           | v6.4.18     | [Compare](https://github.com/symfony/routing/compare/v6.4.8...v6.4.18)                                                                |
    | [symfony/serializer](https://github.com/symfony/serializer)                                     | Upgraded  | v6.4.8           | v6.4.19     | [Compare](https://github.com/symfony/serializer/compare/v6.4.8...v6.4.19)                                                             |
    | [symfony/validator](https://github.com/symfony/validator)                                       | Upgraded  | v6.4.8           | v6.4.19     | [Compare](https://github.com/symfony/validator/compare/v6.4.8...v6.4.19)                                                              |
    | [symfony/var-exporter](https://github.com/symfony/var-exporter)                                 | Upgraded  | v6.4.13          | v6.4.19     | [Compare](https://github.com/symfony/var-exporter/compare/v6.4.13...v6.4.19)                                                          |
    | [twig/twig](https://github.com/twigphp/Twig)                                                    | Upgraded  | v3.10.3          | v3.19.0     | [Compare](https://github.com/twigphp/Twig/compare/v3.10.3...v3.19.0)                                                                  |
    | [symfony/polyfill-php72](https://github.com/symfony/polyfill-php72)                             | Removed   | v1.31.0          | -           | [Compare](https://github.com/symfony/polyfill-php72/releases/tag/v1.31.0)                                                             |
    | [symfony/polyfill-php80](https://github.com/symfony/polyfill-php80)                             | Removed   | v1.31.0          | -           | [Compare](https://github.com/symfony/polyfill-php80/releases/tag/v1.31.0)                                                             |

    | Dev Packages                                                                                | Operation | Base     | Target   | Link                                                                                         |
    |---------------------------------------------------------------------------------------------|-----------|----------|----------|----------------------------------------------------------------------------------------------|
    | [behat/mink](https://github.com/minkphp/Mink)                                               | Upgraded  | v1.11.0  | v1.12.0  | [Compare](https://github.com/minkphp/Mink/compare/v1.11.0...v1.12.0)                         |
    | [brick/math](https://github.com/brick/math)                                                 | New       | -        | 0.12.3   | [Compare](https://github.com/brick/math/releases/tag/0.12.3)                                 |
    | [composer/ca-bundle](https://github.com/composer/ca-bundle)                                 | Upgraded  | 1.5.1    | 1.5.5    | [Compare](https://github.com/composer/ca-bundle/compare/1.5.1...1.5.5)                       |
    | [composer/class-map-generator](https://github.com/composer/class-map-generator)             | Upgraded  | 1.3.4    | 1.6.0    | [Compare](https://github.com/composer/class-map-generator/compare/1.3.4...1.6.0)             |
    | [composer/composer](https://github.com/composer/composer)                                   | Upgraded  | 2.7.7    | 2.8.6    | [Compare](https://github.com/composer/composer/compare/2.7.7...2.8.6)                        |
    | [composer/pcre](https://github.com/composer/pcre)                                           | Upgraded  | 3.2.0    | 3.3.2    | [Compare](https://github.com/composer/pcre/compare/3.2.0...3.3.2)                            |
    | [drupal/coder](https://github.com/pfrenssen/coder)                                          | Upgraded  | 8.3.24   | 8.3.28   | [Compare](https://github.com/pfrenssen/coder/compare/8.3.24...8.3.28)                        |
    | [drupal/core-dev](https://github.com/drupal/core-dev)                                       | Upgraded  | 10.3.0   | 10.4.3   | [Compare](https://github.com/drupal/core-dev/compare/10.3.0...10.4.3)                        |
    | [google/protobuf](https://github.com/protocolbuffers/protobuf-php)                          | Upgraded  | v3.25.4  | v4.29.3  | [Compare](https://github.com/protocolbuffers/protobuf-php/compare/v3.25.4...v4.29.3)         |
    | [ion-bazan/composer-diff](https://github.com/IonBazan/composer-diff)                        | Upgraded  | v1.9.1   | v1.11.0  | [Compare](https://github.com/IonBazan/composer-diff/compare/v1.9.1...v1.11.0)                |
    | [mglaman/phpstan-drupal](https://github.com/mglaman/phpstan-drupal)                         | Upgraded  | 1.2.12   | 1.3.3    | [Compare](https://github.com/mglaman/phpstan-drupal/compare/1.2.12...1.3.3)                  |
    | [mikey179/vfsstream](https://github.com/bovigo/vfsStream)                                   | Upgraded  | v1.6.11  | v1.6.12  | [Compare](https://github.com/bovigo/vfsStream/compare/v1.6.11...v1.6.12)                     |
    | [myclabs/deep-copy](https://github.com/myclabs/DeepCopy)                                    | Upgraded  | 1.12.0   | 1.13.0   | [Compare](https://github.com/myclabs/DeepCopy/compare/1.12.0...1.13.0)                       |
    | [nyholm/psr7-server](https://github.com/Nyholm/psr7-server)                                 | New       | -        | 1.1.0    | [Compare](https://github.com/Nyholm/psr7-server/releases/tag/1.1.0)                          |
    | [open-telemetry/api](https://github.com/opentelemetry-php/api)                              | Upgraded  | 1.0.3    | 1.2.2    | [Compare](https://github.com/opentelemetry-php/api/compare/1.0.3...1.2.2)                    |
    | [open-telemetry/context](https://github.com/opentelemetry-php/context)                      | Upgraded  | 1.0.2    | 1.1.0    | [Compare](https://github.com/opentelemetry-php/context/compare/1.0.2...1.1.0)                |
    | [open-telemetry/exporter-otlp](https://github.com/opentelemetry-php/exporter-otlp)          | Upgraded  | 1.0.4    | 1.2.0    | [Compare](https://github.com/opentelemetry-php/exporter-otlp/compare/1.0.4...1.2.0)          |
    | [open-telemetry/gen-otlp-protobuf](https://github.com/opentelemetry-php/gen-otlp-protobuf)  | Upgraded  | 1.1.0    | 1.5.0    | [Compare](https://github.com/opentelemetry-php/gen-otlp-protobuf/compare/1.1.0...1.5.0)      |
    | [open-telemetry/sdk](https://github.com/opentelemetry-php/sdk)                              | Upgraded  | 1.0.8    | 1.2.2    | [Compare](https://github.com/opentelemetry-php/sdk/compare/1.0.8...1.2.2)                    |
    | [open-telemetry/sem-conv](https://github.com/opentelemetry-php/sem-conv)                    | Upgraded  | 1.25.0   | 1.30.0   | [Compare](https://github.com/opentelemetry-php/sem-conv/compare/1.25.0...1.30.0)             |
    | [php-http/discovery](https://github.com/php-http/discovery)                                 | Upgraded  | 1.19.4   | 1.20.0   | [Compare](https://github.com/php-http/discovery/compare/1.19.4...1.20.0)                     |
    | [php-http/guzzle7-adapter](https://github.com/php-http/guzzle7-adapter)                     | Upgraded  | 1.0.0    | 1.1.0    | [Compare](https://github.com/php-http/guzzle7-adapter/compare/1.0.0...1.1.0)                 |
    | [php-http/httplug](https://github.com/php-http/httplug)                                     | Upgraded  | 2.4.0    | 2.4.1    | [Compare](https://github.com/php-http/httplug/compare/2.4.0...2.4.1)                         |
    | [phpdocumentor/reflection-docblock](https://github.com/phpDocumentor/ReflectionDocBlock)    | Upgraded  | 5.4.1    | 5.6.1    | [Compare](https://github.com/phpDocumentor/ReflectionDocBlock/compare/5.4.1...5.6.1)         |
    | [phpdocumentor/type-resolver](https://github.com/phpDocumentor/TypeResolver)                | Upgraded  | 1.8.2    | 1.10.0   | [Compare](https://github.com/phpDocumentor/TypeResolver/compare/1.8.2...1.10.0)              |
    | [phpspec/prophecy](https://github.com/phpspec/prophecy)                                     | Upgraded  | v1.19.0  | v1.20.0  | [Compare](https://github.com/phpspec/prophecy/compare/v1.19.0...v1.20.0)                     |
    | [phpspec/prophecy-phpunit](https://github.com/phpspec/prophecy-phpunit)                     | Upgraded  | v2.2.0   | v2.3.0   | [Compare](https://github.com/phpspec/prophecy-phpunit/compare/v2.2.0...v2.3.0)               |
    | [phpstan/extension-installer](https://github.com/phpstan/extension-installer)               | Upgraded  | 1.4.1    | 1.4.3    | [Compare](https://github.com/phpstan/extension-installer/compare/1.4.1...1.4.3)              |
    | [phpstan/phpdoc-parser](https://github.com/phpstan/phpdoc-parser)                           | Upgraded  | 1.29.1   | 2.1.0    | [Compare](https://github.com/phpstan/phpdoc-parser/compare/1.29.1...2.1.0)                   |
    | [phpstan/phpstan](https://github.com/phpstan/phpstan)                                       | Upgraded  | 1.11.10  | 1.12.19  | [Compare](https://github.com/phpstan/phpstan/compare/1.11.10...1.12.19)                      |
    | [phpstan/phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules)   | Upgraded  | 1.2.0    | 1.2.1    | [Compare](https://github.com/phpstan/phpstan-deprecation-rules/compare/1.2.0...1.2.1)        |
    | [phpstan/phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit)                       | Upgraded  | 1.4.0    | 1.4.2    | [Compare](https://github.com/phpstan/phpstan-phpunit/compare/1.4.0...1.4.2)                  |
    | [phpunit/php-code-coverage](https://github.com/sebastianbergmann/php-code-coverage)         | Upgraded  | 9.2.31   | 9.2.32   | [Compare](https://github.com/sebastianbergmann/php-code-coverage/compare/9.2.31...9.2.32)    |
    | [phpunit/phpunit](https://github.com/sebastianbergmann/phpunit)                             | Upgraded  | 9.6.20   | 9.6.22   | [Compare](https://github.com/sebastianbergmann/phpunit/compare/9.6.20...9.6.22)              |
    | [ramsey/collection](https://github.com/ramsey/collection)                                   | New       | -        | 2.0.0    | [Compare](https://github.com/ramsey/collection/releases/tag/2.0.0)                           |
    | [ramsey/uuid](https://github.com/ramsey/uuid)                                               | New       | -        | 4.7.6    | [Compare](https://github.com/ramsey/uuid/releases/tag/4.7.6)                                 |
    | [sirbrillig/phpcs-variable-analysis](https://github.com/sirbrillig/phpcs-variable-analysis) | Upgraded  | v2.11.19 | v2.11.22 | [Compare](https://github.com/sirbrillig/phpcs-variable-analysis/compare/v2.11.19...v2.11.22) |
    | [slevomat/coding-standard](https://github.com/slevomat/coding-standard)                     | Upgraded  | 8.15.0   | 8.16.0   | [Compare](https://github.com/slevomat/coding-standard/compare/8.15.0...8.16.0)               |
    | [squizlabs/php_codesniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer)              | Upgraded  | 3.10.2   | 3.11.3   | [Compare](https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.10.2...3.11.3)         |
    | [symfony/browser-kit](https://github.com/symfony/browser-kit)                               | Upgraded  | v6.4.8   | v6.4.19  | [Compare](https://github.com/symfony/browser-kit/compare/v6.4.8...v6.4.19)                   |
    | [symfony/css-selector](https://github.com/symfony/css-selector)                             | Upgraded  | v6.4.8   | v6.4.13  | [Compare](https://github.com/symfony/css-selector/compare/v6.4.8...v6.4.13)                  |
    | [symfony/dom-crawler](https://github.com/symfony/dom-crawler)                               | Upgraded  | v6.4.8   | v6.4.19  | [Compare](https://github.com/symfony/dom-crawler/compare/v6.4.8...v6.4.19)                   |
    | [symfony/lock](https://github.com/symfony/lock)                                             | Upgraded  | v6.4.8   | v6.4.13  | [Compare](https://github.com/symfony/lock/compare/v6.4.8...v6.4.13)                          |
    | [symfony/phpunit-bridge](https://github.com/symfony/phpunit-bridge)                         | Upgraded  | v6.4.10  | v6.4.16  | [Compare](https://github.com/symfony/phpunit-bridge/compare/v6.4.10...v6.4.16)               |
    | [symfony/polyfill-php80](https://github.com/symfony/polyfill-php80)                         | New       | -        | v1.31.0  | [Compare](https://github.com/symfony/polyfill-php80/releases/tag/v1.31.0)                    |
    | [symfony/polyfill-php82](https://github.com/symfony/polyfill-php82)                         | Upgraded  | v1.30.0  | v1.31.0  | [Compare](https://github.com/symfony/polyfill-php82/compare/v1.30.0...v1.31.0)               |
    | [symfony/stopwatch](https://github.com/symfony/stopwatch)                                   | Upgraded  | v6.4.8   | v7.2.4   | [Compare](https://github.com/symfony/stopwatch/compare/v6.4.8...v7.2.4)                      |
    | [tbachert/spi](https://github.com/Nevay/spi)                                                | New       | -        | v1.0.2   | [Compare](https://github.com/Nevay/spi/releases/tag/v1.0.2)                                  |

## Initializing workflows

Currently, you can initialize the workflows by running
`polymer drupal:workflow:generate github`. This will place the supported
workflows in `.github/workflows/`.
