<?php
namespace StrIo\Nominatim\Service;

use maxh\Nominatim\Exceptions\NominatimException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GeocodeTableService
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var GeocodeService
     */
    protected $geocodeService;

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @var array
     */
    protected $sourceFields = [];

    /**
     * @var array
     */
    protected $targetFields = [];

    public function setTable(string $table)
    {
        $this->table = $table;
    }

    public function setSourceFields(array $sourceFields)
    {
        $this->sourceFields = $sourceFields;
    }

    public function setTargetFields(array $targetFields)
    {
        $this->targetFields = $targetFields;
    }

    public function __construct(
        GeocodeService $geocodeService
    ) {
        $this->geocodeService = $geocodeService;
    }

    public function geocodeAllUnProcessedRecords()
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
                    (string)$record[$this->sourceFields['postalcode']],
                    (string)$record[$this->sourceFields['city']],
                    (string)$record[$this->sourceFields['country']]
                );
                $this->updateRecord($record['uid'], $coordinates);
            }
        } catch (NominatimException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }

    public function geocodeSingleRecord(array &$record)
    {
        $coordinates = $this->geocodeService->getCoordinatesForAddress(
            (string)$record[$this->sourceFields['address']],
            (string)$record[$this->sourceFields['postalcode']],
            (string)$record[$this->sourceFields['city']],
            (string)$record[$this->sourceFields['country']]
        );
        $record[$this->targetFields['latitude']] = $coordinates['latitude'];
        $record[$this->targetFields['longitude']] = $coordinates['longitude'];
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
