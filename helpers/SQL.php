<?php

use Lucinda\SQL\StatementResults;
use Lucinda\Framework\ServiceRegistry;
use Lucinda\Framework\SqlConnectionProvider;

/**
 * Automates prepared statement execution and results retrieval
 *
 * @param  string               $query      SQL query to prepare
 * @param  array<string, mixed> $parameters Parameters to bind by key (param name) and value (param value)
 * @param  string               $serverName Name of server to perform query on
 * @return StatementResults Object that encapsulates execution results.
 * @throws \Lucinda\SQL\Exception
 * @throws \Lucinda\SQL\StatementException|\Lucinda\SQL\ConnectionException
 */
function SQL(string $query, array $parameters = array(), string $serverName = ""): StatementResults
{
    $provider = ServiceRegistry::get(SqlConnectionProvider::class);
    $preparedStatement = $provider->getConnection($serverName)->preparedStatement();
    $preparedStatement->prepare($query);
    return $preparedStatement->execute($parameters);
}
