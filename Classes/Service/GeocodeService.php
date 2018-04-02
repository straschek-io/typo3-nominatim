<?php
namespace StrIo\Nominatim\Service;

use maxh\Nominatim\Nominatim;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GeocodeService
{
    protected $url = 'http://nominatim.openstreetmap.org/';
    protected $nominatim = null;

    public function __construct()
    {
        $this->nominatim = GeneralUtility::makeInstance(Nominatim::class, $this->url);
    }

    /**
     * @param null $street
     * @param null $zip
     * @param null $city
     * @param string $country
     * @return array
     */
    public function getCoordinatesForAddress($street = null, $zip = null, $city = null, $country = 'Germany'): array
    {
        $result = [];

        $address = $street . ', ' . $zip . ' ' . $city . ', ' . $country;
        $address = trim($address, ', ');    // remove trailing commas and whitespaces

        if ($address) {
            $result = $this->getCoodinatesForSearchString($address);
        }

        return $result;
    }

    /**
     * @param string $searchString
     * @return array
     */
    public function getCoodinatesForSearchString(string $searchString): array
    {
        try {
            $cache = $this->initializeCache();
            $cacheKey = 'nominatim-' . strtolower(str_replace(' ', '-', preg_replace('/[^0-9a-zA-Z ]/m', '', $searchString)));

            if (!$cache->has($cacheKey)) {
                $search = $this->nominatim->newSearch();
                $search->query($searchString);
                $search->limit(1);
                $result = $this->nominatim->find($search);
                $coordinates = [
                    'latitude' => $result[0]['lat'],
                    'longitude' => $result[0]['lon'],
                ];
                $cache->set($cacheKey, $coordinates, []);
                sleep(rand(1, 2));
            } else {
                $coordinates = $cache->get($cacheKey);
            }
            return $coordinates;
        } catch (NominatimException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }

    protected function initializeCache(): FrontendInterface
    {
        try {
            $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
            return $cacheManager->getCache('nominatim');
        } catch (NoSuchCacheException $e) {
            throw new \RuntimeException('Unable to load Cache!', 1522705381);
        }
    }
}
