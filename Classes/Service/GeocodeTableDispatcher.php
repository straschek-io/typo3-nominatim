<?php
namespace StrIo\Nominatim\Service;

use maxh\Nominatim\Exceptions\NominatimException;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

class GeocodeTableDispatcher
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var GeocodeService
     */
    protected $geocodeService;

    /**
     * @var array
     */
    protected $tableConfiguration;

    public function __construct(
        ObjectManagerInterface $objectManager,
        GeocodeService $geocodeService,
        SettingsService $settingsService
    ) {
        $this->objectManager = $objectManager;
        $this->geocodeService = $geocodeService;
        $this->tableConfiguration = $settingsService->getTableConfiguration();
    }

    public function geocodeAllRecordsOfConfiguredTables()
    {
        try {
            if (!empty($this->tableConfiguration)) {
                foreach ($this->tableConfiguration as $tableName => $tableConfiguration) {
                    $geocodeTableService = $this->objectManager->get(GeocodeTableService::class);
                    $geocodeTableService->setTable($tableName);
                    $geocodeTableService->setSourceFields($tableConfiguration['sourceFields']);
                    $geocodeTableService->setTargetFields($tableConfiguration['targetFields']);
                    $geocodeTableService->geocodeAllUnProcessedRecords();
                }
            }
        } catch (NominatimException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }

    public function geocodeSingleRecordOfConfiguredTable(string $table, array &$fieldArray)
    {
        try {
            if (!empty($this->tableConfiguration)) {
                foreach ($this->tableConfiguration as $tableName => $tableConfiguration) {
                    if ($table === $tableName) {
                        $geocodeTableService = $this->objectManager->get(GeocodeTableService::class);
                        $geocodeTableService->setTable($tableName);
                        $geocodeTableService->setSourceFields($tableConfiguration['sourceFields']);
                        $geocodeTableService->setTargetFields($tableConfiguration['targetFields']);
                        $geocodeTableService->geocodeSingleRecord($fieldArray);
                        if ($fieldArray[$tableConfiguration['targetFields']['latitude']] === null || $fieldArray[$tableConfiguration['targetFields']['longitude']] === null) {
                            $this->addFlashMessage('Geodata could not be fetched', FlashMessage::ERROR);
                        } else {
                            $this->addFlashMessage('Geodata successfully fetched', FlashMessage::INFO);
                        }
                    }
                }
            }
        } catch (NominatimException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }

    private function addFlashMessage($message, $severity = FlashMessage::ERROR)
    {
        /** @var FlashMessage $message */
        $message = GeneralUtility::makeInstance(
            FlashMessage::class,
            $message,
            '',
            $severity,
            true
        );
        /** @var $flashMessageService FlashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $flashMessageService->getMessageQueueByIdentifier()->enqueue($message);
    }
}
