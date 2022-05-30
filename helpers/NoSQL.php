<?php
use Lucinda\NoSQL\Driver;
use Lucinda\NoSQL\ConnectionFactory;

/**
 * Automates retrieval for a key-value store driver
 *
 * @param string $serverName
 * @return Driver
 * @throws \Lucinda\NoSQL\ConnectionException
 */
function NoSQL(string $serverName = ""): Driver
{
    return ConnectionFactory::getInstance($serverName);
}