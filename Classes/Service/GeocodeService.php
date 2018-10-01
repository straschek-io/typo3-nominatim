<?php
namespace StrIo\Nominatim\Service;

use maxh\Nominatim\Exceptions\NominatimException;
use maxh\Nominatim\Nominatim;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class GeocodeService
{
    const FETCH_STATUS_SUCCESS = 'success';
    const FETCH_STATUS_NORESULT = 'noresult';

    protected $url = 'http://nominatim.openstreetmap.org/';

    /**
     * @var Nominatim
     */
    protected $nominatim = null;

    /**
     * @var Dispatcher
     */
    protected $signalSlotDispatcher = null;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        Dispatcher $signalSlotDispatcher
    ) {
        $this->objectManager = $objectManager ?: GeneralUtility::makeInstance(ObjectManager::class);
        $this->nominatim = GeneralUtility::makeInstance(Nominatim::class, $this->url);
        $this->signalSlotDispatcher = $signalSlotDispatcher ?: $this->objectManager->get(Dispatcher::class);
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
        $street = preg_replace("/\r|\n/", ' ', $street);

        try {
            $cache = $this->initializeCache();
            $cacheKey = 'nominatim-' . strtolower(str_replace(
                ' ',
                '-',
                    preg_replace('/[^0-9a-zA-Z ]/m', '', trim($street . ' ' . $zip . ' ' . $city . ' ' . $country))
                ));

            if (!$cache->has($cacheKey)) {
                $search = $this->nominatim->newSearch();
                $search->street($street);
                // Remove postalcode from search, as it apparently makes search more loose
                // $search->postalCode($zip);
                $search->city($city);
                $search->country($country);
                $search->limit(1);
                $result = $this->nominatim->find($search);
                if (!empty($result[0])) {
                    $coordinates = [
                        'status' => self::FETCH_STATUS_SUCCESS,
                        'latitude' => $result[0]['lat'],
                        'longitude' => $result[0]['lon'],
                    ];
                } else {
                    $coordinates = [
                        'status' => self::FETCH_STATUS_NORESULT,
                    ];
                }
                $coordinates['queryString'] = $search->getQueryString();
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

    /**
     * @param string $searchString
     * @param array $countryCodes
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \maxh\Nominatim\Exceptions\InvalidParameterException
     * @return array
     */
    public function getCoordinatesForSearchString(string $searchString, $countryCodes = []): array
    {
        try {
            $cache = $this->initializeCache();
            $cacheKey = 'nominatim-' . strtolower(str_replace(
                    ' ',
                    '-',
                    preg_replace('/[^0-9a-zA-Z ]/m', '', trim($searchString))
                )) . '-' . implode('-', $countryCodes);

            if (!$cache->has($cacheKey)) {
                $this->signalSlotDispatcher->dispatch(__CLASS__, 'nominatimSearchPreProcess', [&$searchString]);
                $search = $this->nominatim->newSearch();
                $search->query($searchString);
                $search->limit(1);
                foreach ($countryCodes as $countryCode) {
                    $search->countryCode($countryCode);
                }
                $result = $this->nominatim->find($search);
                if (!empty($result[0])) {
                    $coordinates = [
                        'status' => self::FETCH_STATUS_SUCCESS,
                        'latitude' => $result[0]['lat'],
                        'longitude' => $result[0]['lon'],
                    ];
                } else {
                    $coordinates = [
                        'status' => self::FETCH_STATUS_NORESULT,
                    ];
                }
                $coordinates['queryString'] = $search->getQueryString();
                $cache->set($cacheKey, $coordinates, []);
            } else {
                $coordinates = $cache->get($cacheKey);
            }
            $this->signalSlotDispatcher->dispatch(__CLASS__, 'nominatimSearchPostProcess', [&$searchString, &$coordinates]);
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
