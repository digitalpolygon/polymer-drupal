<?php

if (!isset($polymer_override_config_directories)) {
    $polymer_override_config_directories = TRUE;
}

// Configuration directories.
if ($polymer_override_config_directories && isset($repo_root)) {
    // phpcs:ignore
    $settings['config_sync_directory'] = $repo_root . "/config/default";
}
