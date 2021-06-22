<?php
namespace Test\Lucinda\Project\DAO;

use Lucinda\Project\DAO\NoSQLMigrationCache;

class NoSQLMigrationCacheTest extends AbstractMigrationCacheTest
{
    public function __construct()
    {
        $this->object = new NoSQLMigrationCache();
    }
}
