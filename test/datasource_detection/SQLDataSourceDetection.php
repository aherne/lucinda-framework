<?php
require_once(str_replace("/test/","/src/",__FILE__));
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/sql-data-access/loader.php");

// create test environment
$xml = '
<server driver="mysql" host="localhost" port="3306" username="test_user" password="test_password" schema="test_schema" charset="utf8"/>
';

// instance class
$test = new SQLDataSourceDetection(simplexml_load_string($xml));

// run tests
echo "driver: ".($test->getDataSource()->getDriverName()=="mysql"?"OK":"NOK")."\n";
echo "host: ".($test->getDataSource()->getHost()=="localhost"?"OK":"NOK")."\n";
echo "port: ".($test->getDataSource()->getPort()=="3306"?"OK":"NOK")."\n";
echo "username: ".($test->getDataSource()->getUserName()=="test_user"?"OK":"NOK")."\n";
echo "password: ".($test->getDataSource()->getPassword()=="test_password"?"OK":"NOK")."\n";
echo "schema: ".($test->getDataSource()->getSchema()=="test_schema"?"OK":"NOK")."\n";
echo "charset: ".($test->getDataSource()->getCharset()=="utf8"?"OK":"NOK");
