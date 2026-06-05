<?php

/**
 * Standalone (split-repo) PHPUnit bootstrap.
 *
 * The polymer_drupal extension namespace is deliberately NOT mapped in
 * composer.json — Polymer registers it on the autoloader at boot. Unit tests
 * run without booting Polymer, so this bootstrap mirrors that registration.
 * In the monorepo the root tests/bootstrap.php does the same job.
 */

declare(strict_types=1);

$loader = require __DIR__ . '/../../vendor/autoload.php';

$loader->addPsr4('DigitalPolygon\\Polymer\\polymer_drupal\\', __DIR__ . '/../../src');
