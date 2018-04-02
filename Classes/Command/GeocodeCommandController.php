<?php
namespace StrIo\Nominatim\Command;

use StrIo\Nominatim\Service\GeocodeTableDispatcher;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

class GeocodeCommandController extends CommandController
{
    /**
     * @var GeocodeTableDispatcher
     */
    protected $geocodeTableDispatcher;

    /**
     * @param GeocodeTableDispatcher $geocodeTableDispatcher
     */
    public function __construct(GeocodeTableDispatcher $geocodeTableDispatcher)
    {
        $this->geocodeTableDispatcher = $geocodeTableDispatcher;
    }

    /**
     * Fetch & persist geodata command
     *
     * Fetch & persist geodata for tables defined in TypoScript (module.tx_nominatim.tables)
     */
    public function fetchCommand()
    {
        $this->geocodeTableDispatcher->geocodeAllRecordsOfConfiguredTables();
    }
}
