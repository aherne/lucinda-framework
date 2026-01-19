<?php

use Lucinda\NoSQL\Driver;
use Lucinda\Framework\ServiceRegistry;
use Lucinda\Framework\NoSqlDriverProvider;

/**
 * Automates retrieval for a key-value store driver
 *
 * @param  string $serverName
 * @return Driver
 * @throws \Lucinda\NoSQL\ConnectionException
 */
function NoSQL(string $serverName = ""): Driver
{
    $provider = ServiceRegistry::get(NoSqlDriverProvider::class);
    return $provider->getDriver($serverName);
}
