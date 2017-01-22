<?php
require_once("application/php-sql-data-access-api/loader.php");

/**
 * Reads xml for sql database servers credentials based on detected environment, creates datasource objects on these then injects datasource 
 * objects into classes that insure a single connection is (re)used for a single database server in the duration of a request through:
 * - singletons: if we're using no more than one sql server in application. In this case, a connection instance will be retrieved by:
 * 	SQLConnectionSingleton::getInstance() : this returns (or creates, if it doesn't exist) an SQLConnection object for the single SQL server we're using
 * - singleton factories: if we're using more than one sql server in application. In this case, a connection instance will be retrieved by:
 * 	SQLConnectionFactory::getInstance(serverName) : this returns (or creates, if it doesn't exist) an SQLConnection object for the SQL server identified by serverName
 *  
 *  The XML to define the single sql database server used by application:
 *  <database>
 *  	<{ENVIRONMENT_NAME}>
 *  		<sql>
 *  			<driver>...</driver>
 *  			<host>...</host>
 *  			<port>...</port>
 *  			...{MORE CREDENTIALS}...
 *  		</sql>
 *  	</{ENVIRONMENT_NAME}>
 *  	...{MORE ENVIRONMENTS}...
 *  </database>
 *  
 *  The XML to define multiple sql database servers used by application:
 *  <database>
 *  	<{ENVIRONMENT_NAME}>
 *  		<sql>
 *  			<server name="server1">
 *  				<driver>...</driver>
 *  				<host>...</host>
 *  				<port>...</port>
 *  				...{MORE CREDENTIALS}...
 *  			</server>
 *  			...{MORE <server> TAGS}...
 *  		</sql>
 *  	</{ENVIRONMENT_NAME}>
 *  	...{MORE ENVIRONMENTS}...
 *  </database>
 */
class SQLDataSourceInjector extends ApplicationListener {
	public function run() {
		$environment = $this->application->getAttribute("environment");
		
		// detect & inject sql data sources
		$xml = $this->application->getXML()->database->sql->$environment;
		if(!empty($xml)) {
			$this->injectDataSources($xml);
		}
	}
	
	/**
	 * Creates SQLDataSource entries based on XML info and injects them into SQLConnectionFactory/SQLConnectionSingleton
	 * 
	 * @param SimpleXMLElement $xml Content of database.{ENVIRONMENT_NAME}.sql XML tag.
	 * @throws ServletException If tags syntax is invalid.
	 */
	private function injectDataSources(SimpleXMLElement $xml) {
		$xml = (array) $xml;
		if(isset($xml["server"])) {
			$entries = is_array($xml["server"])?$xml["server"]:array($xml["server"]);
			// inject them into factory
			foreach($entries as $element) {
				if(!isset($element["name"])) throw new ServletException("Attribute 'name' not set for <server> tag!");
				SQLConnectionFactory::setDataSource((string) $element["name"], $this->createDataSource($element));
			}
		} else {
			// inject them into singleton
			SQLConnectionSingleton::setDataSource($this->createDataSource($xml));
		}
	}
	
	/**
	 * Creates NoSQLDataSource entries based on XML info and injects them into NoSQLConnectionFactory/NoSQLConnectionSingleton
	 * 
	 * @param SimpleXMLElement $xml Content of database.{ENVIRONMENT_NAME}.sql XML tag.
	 * @throws ServletException If tags syntax is invalid.
	 */
	private function injectNoSQLDataSources(SimpleXMLElement $xml) {
		$xml = (array) $xml;
		if(isset($xml["server"])) {
			$entries = is_array($xml["server"])?$xml["server"]:array($xml["server"]);
			// inject them into factory
			foreach($entries as $element) {
				if(!isset($element["name"])) throw new ServletException("Attribute 'name' not set for <server> tag!");
				NoSQLConnectionFactory::setDataSource((string) $element["name"], $this->createNoSQLDataSource($element));
			}
		} else {
			// inject them into singleton
			NoSQLConnectionSingleton::setDataSource($this->createNoSQLDataSource($xml));
		}
	}
	
	/**
	 * Creates and returns a SQLDataSource object based on XML info.
	 * 
	 * @param SimpleXMLElement $databaseInfo
	 * @return SQLDataSource
	 */
	private function createDataSource(SimpleXMLElement $databaseInfo) {
		$dataSource = new SQLDataSource();
		$dataSource->setDriverName((string) $databaseInfo->driver);
		$dataSource->setDriverOptions((string) $databaseInfo->options);
		$dataSource->setHost((string) $databaseInfo->host);
		$dataSource->setPort((string) $databaseInfo->port);
		$dataSource->setUserName((string) $databaseInfo->username);
		$dataSource->setPassword((string) $databaseInfo->password);
		$dataSource->setSchema((string) $databaseInfo->schema);
		return $dataSource;
	}
}