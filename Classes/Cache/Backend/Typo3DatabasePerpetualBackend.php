<?php
namespace StrIo\Nominatim\Cache\Backend;

use TYPO3\CMS\Core\Cache\Backend\TaggableBackendInterface;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

/**
 * Perpetual cache backend. Used for geocoding data, which will NEVER change
 */
class Typo3DatabasePerpetualBackend extends Typo3DatabaseBackend implements TaggableBackendInterface
{
    /**
     * Setup cache with deviant table prefixes. This is mandatory, as
     * the "cache:flush --force" command from typo3_console truncates
     * all tables with "_cf" prefix
     *
     * @see \Helhum\Typo3Console\Service\CacheService::_forceFlushCoreDatabaseCaches
     * @param FrontendInterface $cache The frontend for this backend
     * @api
     */
    public function setCache(FrontendInterface $cache)
    {
        parent::setCache($cache);
        $this->cacheTable = 'cfperpetual_' . $this->cacheIdentifier;
        $this->tagsTable = 'cfperpetual_' . $this->cacheIdentifier . '_tags';
    }

    public function set($entryIdentifier, $data, array $tags = [], $lifetime = null)
    {
        parent::set($entryIdentifier, $data, $tags, self::FAKED_UNLIMITED_EXPIRE);
    }

    public function flush()
    {
    }

    public function flushByTags(array $tags)
    {
    }

    public function flushByTag($tag)
    {
    }
}
