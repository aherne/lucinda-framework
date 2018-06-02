<?php
require_once(str_replace("/test/","/src/",__FILE__));
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/nosql-data-access/loader.php");

// create test environment
chdir(dirname(dirname(__DIR__)));

// test memcached
echo "MEMCACHEd\n";
$xml = '
<server driver="memcached" host="localhost" timeout="123" persistent="1"/>
';
$test = new NoSQLDataSourceDetection(simplexml_load_string($xml));
echo "getServers: ".(implode(",", array_keys($test->getDataSource()->getServers()))=="localhost"?"OK":"NOK")."\n";
echo "getTimeout: ".($test->getDataSource()->getTimeout()=="123"?"OK":"NOK")."\n";
echo "isPersistent: ".($test->getDataSource()->isPersistent()?"OK":"NOK")."\n";

// test memcache
echo "MEMCACHE\n";
$xml = '
<server driver="memcache" host="host1:1003" timeout="123"/>
';
$test = new NoSQLDataSourceDetection(simplexml_load_string($xml));
echo "getServers: ".(implode(",", array_keys($test->getDataSource()->getServers()))=="host1"?"OK":"NOK")."\n";
echo "getTimeout: ".($test->getDataSource()->getTimeout()=="123"?"OK":"NOK")."\n";

// test redis
echo "REDIS\n";
$xml = '
<server driver="redis" host="host1:123, host2:456"/>
';
$test = new NoSQLDataSourceDetection(simplexml_load_string($xml));
echo "getServers: ".(implode(",", array_keys($test->getDataSource()->getServers()))=="host1,host2"?"OK":"NOK")."\n";

// test couchbase
echo "COUCHBASE\n";
$xml = '
<server driver="couchbase" host="localhost" username="user" password="pass" bucket_name="bucket" bucket_password="bpass"/>
';
$test = new NoSQLDataSourceDetection(simplexml_load_string($xml));
echo "getHost: ".($test->getDataSource()->getHost()=="localhost"?"OK":"NOK")."\n";
echo "getUserName: ".($test->getDataSource()->getUserName()=="user"?"OK":"NOK")."\n";
echo "getPassword: ".($test->getDataSource()->getPassword()=="pass"?"OK":"NOK")."\n";
echo "getBucketName: ".($test->getDataSource()->getBucketName()=="bucket"?"OK":"NOK")."\n";
echo "getBucketPassword: ".($test->getDataSource()->getBucketPassword()=="bpass"?"OK":"NOK")."\n";

// test apc
$xml = '
<server driver="apc"/>
';
$test = new NoSQLDataSourceDetection(simplexml_load_string($xml));
echo "APC: ".($test->getDataSource() instanceof APCDataSource?"OK":"NOK")."\n";

// test apcu
$xml = '
<server driver="apcu"/>
';
$test = new NoSQLDataSourceDetection(simplexml_load_string($xml));
echo "APCu: ".($test->getDataSource() instanceof APCuDataSource?"OK":"NOK");
