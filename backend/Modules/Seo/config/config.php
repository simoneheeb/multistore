<?php

return [
    'name' => 'Seo',

    // How long a generated sitemap stays cached. A newly published item shows
    // up within this window at the latest.
    'sitemap_ttl' => 3600,

    // Maximum URLs per sitemap file. The spec allows 50,000; a lower number
    // keeps each file small enough to serve quickly.
    'sitemap_chunk' => 5000,
];
