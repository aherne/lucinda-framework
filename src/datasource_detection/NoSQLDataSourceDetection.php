<?php
require_once("DataSourceDetection.php");

class NoSQLDataSourceDetection extends DataSourceDetection {    
    protected function setDataSource(SimpleXMLElement $databaseInfo) {
        $driver = (string) $databaseInfo["driver"];
        if(!$driver) throw new ApplicationException("Child tag <driver> is mandatory for <server> tags!");
        switch($driver) {
            case "couchbase":
                $host = (string) $databaseInfo["host"];
                $userName = (string) $databaseInfo["username"];
                $password = (string) $databaseInfo["password"];
                $bucket = (string) $databaseInfo["bucket_name"];
                if(!$host || !$userName || !$password || !$bucket) throw new ApplicationException("For COUCHBASE driver following attributes are mandatory: host, username, password, bucket_name");
                
                require_once("vendor/lucinda/nosql-data-access/src/CouchbaseDriver.php");
                
                $dataSource = new CouchbaseDataSource();
                $dataSource->setHost($host);
                $dataSource->setAuthenticationInfo($userName, $password);
                $dataSource->setBucketInfo($bucket, (string) $databaseInfo["bucket_password"]);
                $this->dataSource = $dataSource;
                break;
            case "memcache":
                require_once("vendor/lucinda/nosql-data-access/src/MemcacheDriver.php");
                
                $dataSource = new MemcacheDataSource();
                $this->setServerInfo($databaseInfo, $dataSource);
                $this->dataSource = $dataSource;
            case "memcached":
                require_once("vendor/lucinda/nosql-data-access/src/MemcachedDriver.php");
                
                $dataSource = new MemcachedDataSource();
                $this->setServerInfo($databaseInfo, $dataSource);
                $this->dataSource = $dataSource;
            case "redis":
                require_once("vendor/lucinda/nosql-data-access/src/RedisDriver.php");
                
                $dataSource = new RedisDataSource();
                $this->setServerInfo($databaseInfo, $dataSource);
                $this->dataSource = $dataSource;
            case "apc":
                require_once("vendor/lucinda/nosql-data-access/src/APCDriver.php");
                
                $this->dataSource = new APCDataSource();
            case "apcu":
                require_once("vendor/lucinda/nosql-data-access/src/APCuDriver.php");
                
                $this->dataSource = new APCuDataSource();
            default:
                throw new ApplicationException("Nosql driver not supported: ".$driver);
                break;
        }
    }
    
    private function setServerInfo(SimpleXMLElement $databaseInfo, NoSQLServerDataSource $dataSource) {
        // set host and ports
        $temp = (string) $databaseInfo["host"];
        if(!$temp) throw new ApplicationException("Driver attribute 'host' is mandatory");
        $hosts = explode(",",$temp);
        foreach($hosts as $hostAndPort) {
            $hostAndPort = trim($hostAndPort);
            $position = strpos($hostAndPort,":");
            if($position!==false) {
                $dataSource->addServer(substr($hostAndPort, 0, $position), substr($hostAndPort,$position+1));
            } else {
                $dataSource->addServer($hostAndPort);
            }
        }
        
        // set timeout
        $timeout= (string) $databaseInfo["timeout"];
        if($timeout) {
            $dataSource->setTimeout($timeout);
        }
        
        // set persistent
        $persistent = (string) $databaseInfo["persistent"];
        if($persistent) {
            $dataSource->setPersistent();
        }
    }
}