<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function ($extKey) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $extKey,
        'Configuration/TypoScript/Static',
        'Configuration for OSM geocoding of EXT:tt_address records'
    );
}, \StrIo\Nominatim\Service\SettingsService::EXTKEY);
