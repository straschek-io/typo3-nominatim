<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function ($extKey) {

    if (TYPO3_MODE === 'BE') {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][$extKey] =
            \StrIo\Nominatim\Hooks\DataHandlerGeocodeAwareHook::class;
    }

}, \StrIo\Nominatim\Service\SettingsService::EXTKEY);
