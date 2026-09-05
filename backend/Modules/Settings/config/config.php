<?php

return [
    'name' => 'Settings',

    // Cache key holding the merged settings payload. Invalidated on every
    // write (see SettingService::flush).
    'cache_key' => 'site_settings',

    // Settings change rarely and are read on every single page render, so the
    // TTL is long; correctness comes from explicit invalidation, not expiry.
    'cache_ttl' => 86400,

    // Disk and folder used for images uploaded through the settings editor.
    'media_disk' => 'outside',
    'media_directory' => 'settings',
];
