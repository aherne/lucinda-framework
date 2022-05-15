<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\Project\DAO\SQLMigrationCache;

class SQLMigrationCacheTest extends AbstractMigrationCacheTest
{
    public function __construct()
    {
        $this->object = new SQLMigrationCache();
    }
}
