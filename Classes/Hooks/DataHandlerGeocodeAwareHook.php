<?php
namespace StrIo\Nominatim\Hooks;

use StrIo\Nominatim\Service\GeocodeService;
use StrIo\Nominatim\Service\GeocodeTableDispatcher;
use StrIo\Nominatim\Service\SettingsService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class DataHandlerGeocodeAwareHook
{
    public function processDatamap_preProcessFieldArray(
        array &$fieldArray,
        string $table,
        string $id,
        \TYPO3\CMS\Core\DataHandling\DataHandler &$dataHandler
    ) {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $geocodeService = $objectManager->get(GeocodeService::class);
        $settingsService = $objectManager->get(SettingsService::class);
        $tableDispatcher = GeneralUtility::makeInstance(
            GeocodeTableDispatcher::class,
            $objectManager,
            $geocodeService,
            $settingsService
        );
        $tableDispatcher->geocodeSingleRecordOfConfiguredTable($table, $fieldArray);
    }
}
