<?php
namespace Lucinda\Project\Cacheables;
use Lucinda\Framework\AbstractCacheable;
use Lucinda\Project\Persistence\NoSqlExecutor;
use Lucinda\Project\Persistence\NoSql\RepresentationStore;
/** Last-Modified time for a rendered representation; requires NoSQL persistence. */
final class Date extends AbstractCacheable
{
    public const EXPIRATION = 86400;
    protected function setTime(): void
    {
        $identity = $this->request->getServer()->getName().'#'.$this->response;
        $executor = new NoSqlExecutor();
        $store = new RepresentationStore($executor, self::EXPIRATION);
        $this->lastModifiedTime = $store->getTime($identity);
    }
    protected function setEtag(): void {}
}
