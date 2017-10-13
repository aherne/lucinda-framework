<?php
require_once("libraries/php-nosql-data-access-api/loader.php");

/**
 * Reads xml for nosql database servers credentials based on detected environment, creates datasource objects on these then injects datasource 
 * objects into classes that insure a single connection is (re)used for a single database server in the duration of a request through:
 * - singletons: if we're using no more than one nosql server in application. In this case, a connection instance will be retrieved by:
 *  NoSQLConnectionSingleton::getInstance() : this returns (or creates, if it doesn't exist) a NoSQLConnection object for the single NoSQL server we're using
 * - singleton factories: if we're using more than one sql/nosql server in application. In this case, a connection instance will be retrieved by:
 *  NoSQLConnectionFactory::getInstance(serverName) : this returns (or creates, if it doesn't exist) an SQLConnection object for the NoSQL server identified by serverName
 *  
 *  The XML to define the single nosql database server used by application:
 *  <database>
 *  	<nosql>
 *  		<{ENVIRONMENT_NAME}>
 *  			<server driver="..." host="..." port="..." .../>
 *  		</{ENVIRONMENT_NAME}>
 *  		...{MORE ENVIRONMENTS}...
 *  	</nosql>
 *  	...
 *  </database>
 *  
 *  The XML to define multiple sql database servers used by application:
 *  <database>
 *  	<nosql>
 *  		<{ENVIRONMENT_NAME}>
 *  			<server name="..." driver="..." host="..." port="..." .../>
 *  			...{MORE <server> TAGS}...
 *  		</{ENVIRONMENT_NAME}>
 *  		...{MORE ENVIRONMENTS}...
 *  	</nosql>
 *  </database>
 */
class NoSQLDataSourceInjector extends ApplicationListener {
	public function run() {
		$environment = $this->application->getAttribute("environment");
		
		// detect & inject nosql data sources
		$xml = $this->application->getXML()->servers->nosql->$environment;
		if(!empty($xml)) {
			$this->injectDataSources($xml);
		}
	}
	
	/**
	 * Creates NoSQLDataSource entries based on XML info and injects them into NoSQLConnectionFactory/NoSQLConnectionSingleton
	 * 
	 * @param SimpleXMLElement $xml Content of database.{ENVIRONMENT_NAME}.nosql XML tag.
	 * @throws ServletException If tags syntax is invalid.
	 */
	private function injectDataSources(SimpleXMLElement $xml) {
		if(!$xml->server) throw new ServletException("Server not set for environment!");
		$xml = (array) $xml;
		if(is_array($xml["server"])) {
			foreach($xml["server"] as $element) {
				if(!isset($element["name"])) throw new ServletException("Attribute 'name' not set for <server> tag!");
				NoSQLConnectionFactory::setDataSource((string) $element["name"], $this->createDataSource($element));
			}
		} else {
			NoSQLConnectionSingleton::setDataSource($this->createDataSource($xml["server"]));
		}
	}

	/**
	 * Creates a driver-specific NoSQLDataSource entry based on XML info.
	 *
	 * @param SimpleXMLElement $databaseInfo
	 * @return NoSQLDataSource
	 * @throws ServletException If tags syntax is invalid or driver is not supported
	 */
	private function createDataSource(SimpleXMLElement $databaseInfo) {
		$driver = (string) $databaseInfo["driver"];
		if(!$driver) throw new ServletException("Child tag <driver> is mandatory for <server> tags!");
		switch($driver) {
			case "couchbase":
				require_once("libraries/php-nosql-data-access-api/src/CouchbaseDriver.php");
				
				$dataSource = new CouchbaseDataSource();
				$dataSource->setHost((string) $databaseInfo["host"]);
				$dataSource->setPort((string) $databaseInfo["port"]);
				$dataSource->setUserName((string) $databaseInfo["username"]);
				$dataSource->setPassword((string) $databaseInfo["password"]);
				
				$bucket = (string) $databaseInfo["bucket"];
				if($bucket) {
					$dataSource->setBucketInfo($bucket, (string) $databaseInfo["bucket_password"]);
				}
				return $dataSource;
				break;
			case "memcache":
				require_once("libraries/php-nosql-data-access-api/src/MemcacheDriver.php");
				
				$dataSource = new MemcacheDataSource();
				$dataSource->setHost((string) $databaseInfo["host"]);
				$dataSource->setPort((string) $databaseInfo["port"]);
				return $dataSource;
			case "memcached":
				require_once("libraries/php-nosql-data-access-api/src/MemcachedDriver.php");
				
				$dataSource = new MemcachedDataSource();
				$dataSource->setHost((string) $databaseInfo["host"]);
				$dataSource->setPort((string) $databaseInfo["port"]);
				return $dataSource;
			case "redis":
				require_once("libraries/php-nosql-data-access-api/src/RedisDriver.php");
				
				$dataSource = new RedisDataSource();
				$dataSource->setHost((string) $databaseInfo["host"]);
				$dataSource->setPort((string) $databaseInfo["port"]);
				return $dataSource;
			case "apc":
				require_once("libraries/php-nosql-data-access-api/src/APCDriver.php");
				
				$dataSource = new APCDataSource();
				return $dataSource;
			case "apcu":
				require_once("libraries/php-nosql-data-access-api/src/APCuDriver.php");
				
				$dataSource = new APCuDataSource();
				return $dataSource;
			default:
				throw new ServletException("Nosql driver not supported: ".$driver);
				break;
		}
		return $dataSource;
	}
}