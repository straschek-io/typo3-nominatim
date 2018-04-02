<?php
namespace StrIo\Nominatim\Service;

use maxh\Nominatim\Exceptions\NominatimException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GeocodeTableService implements GeocodeProcessingInterface
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var GeocodeService
     */
    protected $geocodeService;

    protected $table = 'tt_address';

    protected $sourceFields = [
        'address' => 'address',
        'zip' => 'zip',
        'city' => 'city',
        'country' => 'country',
    ];

    protected $targetFields = [
        'latitude' => 'latitude',
        'longitude' => 'longitude',
    ];

    public function __construct(GeocodeService $geocodeService)
    {
        $this->geocodeService = $geocodeService;
    }

    public function geocodeAll()
    {
        try {
            $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->table);
            $this->queryBuilder->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
            $records = $this->fetchAllNonGeocodedRecords();
            foreach ($records as $record) {
                $coordinates = $this->geocodeService->getCoordinatesForAddress(
                    (string)$record[$this->sourceFields['address']],
                    (string)$record[$this->sourceFields['zip']],
                    (string)$record[$this->sourceFields['city']],
                    (string)$record[$this->sourceFields['country']]
                );
                $this->updateRecord($record['uid'], $coordinates);
            }
        } catch (NominatimException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }

    public function geocodeSingle()
    {
        // TODO: Implement geocodeSingle() method.
    }

    private function fetchAllNonGeocodedRecords(): array
    {
        return $this->queryBuilder->select('*')
            ->from($this->table)
            ->where(
                $this->queryBuilder->expr()->isNull(
                    $this->targetFields['latitude']
                ),
                $this->queryBuilder->expr()->isNull(
                    $this->targetFields['longitude']
                )
            )
            ->execute()
            ->fetchAll();
    }

    private function updateRecord(int $uid, array $coordinates)
    {
        return $this->queryBuilder
            ->update($this->table)
            ->where(
                $this->queryBuilder->expr()->eq('uid', $this->queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
            )
            ->set($this->targetFields['latitude'], $coordinates['latitude'])
            ->set($this->targetFields['longitude'], $coordinates['longitude'])
            ->execute();
    }
}
