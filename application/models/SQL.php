<?php
/**
 * Automates prepared statement execution and results retrieval
 *
 * @param string $query SQL query to prepare
 * @param string[string] $parameters Parameters to bind by key (param name) and value (param value)
 * @return \Lucinda\SQL\StatementResults Object that encapsulates execution results.
 */
function SQL($query, $parameters=array())
{
    $preparedStatement = Lucinda\SQL\ConnectionSingleton::getInstance()->createPreparedStatement();
    $preparedStatement->prepare($query);
    return $preparedStatement->execute($parameters);
}
