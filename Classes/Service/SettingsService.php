<?php
namespace StrIo\Nominatim\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class SettingsService
{
    const EXTKEY = 'nominatim';

    public function __construct(
        ConfigurationManager $configurationManager
    ) {
        $this->configurationManager = $configurationManager;
    }

    public function getTableConfiguration(): array
    {
        $fullTypoScript = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        if (empty($fullTypoScript['module.']['tx_nominatim.']['tables.'])) {
            throw new \RuntimeException('No configuration given in TypoScript (module.tx_nominatim.tables)!', 1522863185);
        } else {
            return GeneralUtility::removeDotsFromTS($fullTypoScript['module.']['tx_nominatim.']['tables.']);
        }

    }
}
