<?php

namespace Lucinda\Project\DAO;

use Lucinda\Project\Persistence\Builders\PageBuilder;
use Lucinda\Project\Persistence\Queries\Permissions\FindPageById;
use Lucinda\Project\Persistence\Queries\Permissions\FindPageByUrl;
use Lucinda\WebSecurity\DAO\PageAuthorization as PageAuthorizationContract;

/**
 * Resolves persisted route policies for database-backed authorization
 *
 * Unknown pages remain unresolved and cannot become public through a default.
 */
final class PageAuthorization extends SqlBasedDAO implements
    PageAuthorizationContract
{
    /**
     * Resolves a validated route to its database resource identifier
     *
     * @param string $pageURL Validated route supplied by the request
     * @return int|null Persisted resource identifier or null when unknown
     * @throws \Throwable If lookup or row validation fails
     */
    public function getID(string $pageURL): ?int
    {
        $selection = $this->sql->read(new FindPageByUrl($pageURL));
        $row = $selection->toRow();
        if ($row === null) {
            return null;
        }
        $builder = new PageBuilder();
        $page = $builder->build($row);
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
        $selection = $this->sql->read(new FindPageById($pageID));
        $row = $selection->toRow();
        if ($row === null) {
            return false;
        }
        $builder = new PageBuilder();
        $page = $builder->build($row);
        return $page->isPublic();
    }
}
