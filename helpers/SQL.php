<?php
use Lucinda\SQL\ConnectionSingleton;
use Lucinda\SQL\StatementResults;

/**
 * Automates prepared statement execution and results retrieval
 *
 * @param string $query SQL query to prepare
 * @param array<string, mixed> $parameters Parameters to bind by key (param name) and value (param value)
 * @return StatementResults Object that encapsulates execution results.
 * @throws \Lucinda\SQL\Exception
 * @throws \Lucinda\SQL\StatementException
 */
function SQL(string $query, array $parameters = array()): StatementResults
{
    $preparedStatement = ConnectionSingleton::getInstance()->preparedStatement();
    $preparedStatement->prepare($query);
    return $preparedStatement->execute($parameters);
}
