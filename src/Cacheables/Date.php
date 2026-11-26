<?php

namespace Lucinda\Project\Cacheables;

use Lucinda\Framework\AbstractCacheable;
use Lucinda\Framework\NoSQL\AtomicDriver;
use Lucinda\Framework\Persistence\NoSqlExecutor;
use Lucinda\Framework\NoSQL\RepresentationStore;

/**
 * Retains the modification timestamp associated with a rendered representation
 *
 * The original construction path does not provide the storage dependency now
 * required by NoSqlExecutor. Construction fails until that separate integration
 * is addressed.
 */
final class Date extends AbstractCacheable
{
    /** Representation timestamp retention in seconds */
    public const EXPIRATION = 86400;
    private static ?AtomicDriver $atomicDriver = null;

    public static function setDriver(AtomicDriver $atomicDriver): void
    {
        self::$atomicDriver = $atomicDriver;
    }

    /**
     * Loads or creates the retained modification time for this representation
     *
     * The host and response bytes identify the representation. The timestamp
     * remains stable until the stored retention deadline expires.
     *
     * @return void
     * @throws \Throwable If NoSQL storage cannot be constructed or accessed
     */
    protected function setTime(): void
    {
        if (self::$atomicDriver===null) {
            throw new \Exception("Atomic driver not set!");
        }
        $identity = $this->request->getServer()->getName()
            . '#' . $this->response;
        $executor = new NoSqlExecutor(self::$atomicDriver);
        $store = new RepresentationStore($executor, self::EXPIRATION);
        $this->lastModifiedTime = $store->getTime($identity);
    }

    /**
     * Leaves the entity tag empty because this policy uses modification time
     *
     * @return void
     */
    protected function setEtag(): void
    {
    }
}
