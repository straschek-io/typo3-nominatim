<?php
namespace StrIo\Nominatim\Service;

interface GeocodeProcessingInterface
{
    public function geocodeAll();
    public function geocodeSingle();
}
