<?php
require_once("libraries/php-nosql-data-access-api/loader.php");

/**
 * Reads xml for nosql database servers credentials based on detected environment, creates datasource objects on these then injects datasource 
 * objects into classes that insure a single connection is (re)used for a single database server in the duration of a request through:
 * - singletons: if we're using no more than one nosql server in application. In this case, a connection instance will be retrieved by:
 *  NoSQLConnectionSingleton::getInstance() : this returns (or creates, if it doesn't exist) an SQLConnection object for the single NoSQL server we're using
 * - singleton factories: if we're using more than one sql/nosql server in application. In this case, a connection instance will be retrieved by:
 *  NoSQLConnectionFactory::getInstance(serverName) : this returns (or creates, if it doesn't exist) an SQLConnection object for the NoSQL server identified by serverName
 *  
 *  The XML to define the single nosql database server used by application:
 *  <database>
 *  	<{ENVIRONMENT_NAME}>
 *  		<nosql>
 *  			<driver>...</driver>
 *  			<host>...</host>
 *  			<port>...</port>
 *  			...{MORE CREDENTIALS}...
 *  		</nosql>
 *  	</{ENVIRONMENT_NAME}>
 *  	...{MORE ENVIRONMENTS}...
 *  </database>
 *  
 *  The XML to define multiple nosql database servers used by application:
 *  <database>
 *  	<{ENVIRONMENT_NAME}>
 *  		<nosql>
 *  			<server name="server1">
 *  				<driver>...</driver>
 *  				<host>...</host>
 *  				<port>...</port>
 *  				...{MORE CREDENTIALS}...
 *  			</server>
 *  			...{MORE <server> TAGS}...
 *  		</nosql>
 *  	</{ENVIRONMENT_NAME}>
 *  	...{MORE ENVIRONMENTS}...
 *  </database>
 */
class NoSQLDataSourceInjector extends ApplicationListener {
	public function run() {
		$environment = $this->application->getAttribute("environment");
		
		// detect & inject nosql data sources
		$xml = $this->application->getXML()->database->nosql->$environment;
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
		if($xml->server) {
			$xml = (array) $xml;
			$entries = is_array($xml["server"])?$xml["server"]:array($xml["server"]);
			// inject them into factory
			foreach($entries as $element) {
				if(!isset($element["name"])) throw new ServletException("Attribute 'name' not set for <server> tag!");
				NoSQLConnectionFactory::setDataSource((string) $element["name"], $this->createDataSource($element));
			}
		} else {
			// inject them into singleton
			NoSQLConnectionSingleton::setDataSource($this->createDataSource($xml));
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
		$driver = (string) $databaseInfo->driver;
		if(!$driver) throw new ServletException("Child tag <driver> is mandatory for <server> tags!");
		switch($driver) {
			case "couchbase":
				require_once("libraries/php-nosql-data-access-api/src/CouchbaseConnection.php");
				
				$dataSource = new CouchbaseDataSource();
				$dataSource->setHost((string) $databaseInfo->host);
				$dataSource->setPort((string) $databaseInfo->port);
				$dataSource->setUserName((string) $databaseInfo->username);
				$dataSource->setPassword((string) $databaseInfo->password);
				
				$bucket = (string) $databaseInfo->bucket;
				if($bucket) {
					$dataSource->setBucketInfo($bucket, (string) $databaseInfo->bucket_password);
				}
				return $dataSource;
				break;
			case "memcache":
				require_once("libraries/php-nosql-data-access-api/src/MemcacheConnection.php");
				
				$dataSource = new MemcacheDataSource();
				$dataSource->setHost((string) $databaseInfo->host);
				$dataSource->setPort((string) $databaseInfo->port);
				return $dataSource;
			case "memcached":
				require_once("libraries/php-nosql-data-access-api/src/MemcachedConnection.php");
				
				$dataSource = new MemcachedDataSource();
				$dataSource->setHost((string) $databaseInfo->host);
				$dataSource->setPort((string) $databaseInfo->port);
				return $dataSource;
			case "redis":
				require_once("libraries/php-nosql-data-access-api/src/RedisConnection.php");
				
				$dataSource = new RedisDataSource();
				$dataSource->setHost((string) $databaseInfo->host);
				$dataSource->setPort((string) $databaseInfo->port);
				return $dataSource;
			default:
				throw new ServletException("Nosql driver not supported: ".$driver);
				break;
		}
		return $dataSource;
	}
}