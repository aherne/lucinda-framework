<?php
use Lucinda\SQL\ConnectionSingleton;

/**
 * Automates prepared statement execution and results retrieval
 *
 * @param string $query SQL query to prepare
 * @param string[string] $parameters Parameters to bind by key (param name) and value (param value)
 * @return \Lucinda\SQL\StatementResults Object that encapsulates execution results.
 */
function SQL(string $query, array $parameters=array()): Lucinda\SQL\StatementResults
{
    $preparedStatement = ConnectionSingleton::getInstance()->preparedStatement();
    $preparedStatement->prepare($query);
    return $preparedStatement->execute($parameters);
}
