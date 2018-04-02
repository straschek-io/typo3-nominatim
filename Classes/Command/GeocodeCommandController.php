<?php
namespace StrIo\Nominatim\Command;

use StrIo\Nominatim\Service\GeocodeTableService;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

class GeocodeCommandController extends CommandController
{
    /**
     * @var GeocodeTableService
     */
    protected $geocodeTableService;

    /**
     * @param GeocodeTableService $geocodeTableService
     */
    public function __construct(GeocodeTableService $geocodeTableService)
    {
        $this->geocodeTableService = $geocodeTableService;
    }

    public function TtAddressCommand()
    {
        $this->geocodeTableService->geocodeAll();
    }
}
