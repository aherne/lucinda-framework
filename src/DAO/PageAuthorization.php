<?php

namespace Lucinda\Project\DAO;

use Lucinda\Project\Persistence\Builders\PageBuilder;
use Lucinda\Project\Persistence\Queries\Permissions\FindPageById;
use Lucinda\Project\Persistence\Queries\Permissions\FindPageByUrl;
use Lucinda\Project\Persistence\SqlExecutor;
use Lucinda\WebSecurity\DAO\PageAuthorization as PageAuthorizationContract;

/**
 * Resolves persisted route policies for database-backed authorization
 *
 * Unknown pages remain unresolved and cannot become public through a default.
 */
final class PageAuthorization implements PageAuthorizationContract
{
    /** Executor used for resource lookup and public access policy */
    private SqlExecutor $sql;

    /**
     * Captures an injected executor or a lazily configured SQL connection
     *
     * @param SqlExecutor|null $sql Executor sharing the caller's connection
     */
    public function __construct(?SqlExecutor $sql = null)
    {
        $this->sql = $sql ?? new SqlExecutor();
    }

    /**
     * Resolves a validated route to its database resource identifier
     *
     * @param string $pageURL Validated route supplied by the request
     * @return int|null Persisted resource identifier or null when unknown
     * @throws \Throwable If lookup or row validation fails
     */
    public function getID(string $pageURL): ?int
    {
        $selection = $this->sql->select(new FindPageByUrl($pageURL));
        $row = $selection->getFirstRow();
        if ($row === null) {
            return null;
        }
        $builder = new PageBuilder($row);
        $page = $builder->build();
        return $page->getID();
    }

    /**
     * Reads whether a known resource permits unauthenticated access
     *
     * @param int $pageID Persisted resource identifier
     * @return bool True only for a persisted public resource
     * @throws \Throwable If lookup or row validation fails
     */
    public function isPublic(int $pageID): bool
    {
        $selection = $this->sql->select(new FindPageById($pageID));
        $row = $selection->getFirstRow();
        if ($row === null) {
            return false;
        }
        $builder = new PageBuilder($row);
        $page = $builder->build();
        return $page->isPublic();
    }
}
