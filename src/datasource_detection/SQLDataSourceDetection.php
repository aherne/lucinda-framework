<?php
require_once("DataSourceDetection.php");

/**
 * Encapsulates SQLDataSource detection (itself encapsulating database server settings) based on <server> XML tag contents
 */
class SQLDataSourceDetection extends DataSourceDetection {
    protected function setDataSource(SimpleXMLElement $databaseInfo) {
        $dataSource = new SQLDataSource();
        $dataSource->setDriverName((string) $databaseInfo["driver"]);
        $dataSource->setDriverOptions((array) $databaseInfo["options"]);
        $dataSource->setHost((string) $databaseInfo["host"]);
        $dataSource->setPort((string) $databaseInfo["port"]);
        $dataSource->setUserName((string) $databaseInfo["username"]);
        $dataSource->setPassword((string) $databaseInfo["password"]);
        $dataSource->setSchema((string) $databaseInfo["schema"]);
        $dataSource->setCharset((string) $databaseInfo["charset"]);
        $this->dataSource = $dataSource;
    }
}