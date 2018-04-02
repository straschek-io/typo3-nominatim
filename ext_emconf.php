<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Service: Geocoding via OSM Nominatim',
    'description' => 'OSM geocoding service via Nominatim: https://wiki.openstreetmap.org/wiki/Nominatim',
    'category' => 'sv',
    'author' => 'Michael Straschek',
    'author_email' => 'm@straschek.io',
    'state' => 'alpha',
    'internal' => '',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '0.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.10-8.7.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
