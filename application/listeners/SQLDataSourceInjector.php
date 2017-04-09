<?php
require_once("libraries/php-sql-data-access-api/loader.php");

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
 *  	<sql>
 *  		<{ENVIRONMENT_NAME}>
 *  			<server>
 *  				<driver>...</driver>
 *  				<host>...</host>
 *  				<port>...</port>
 *  				...{MORE CREDENTIALS}...
 *  			</server>
 *  		</{ENVIRONMENT_NAME}>
 *  		...{MORE ENVIRONMENTS}...
 *  	</sql>
 *  	...
 *  </database>
 *  
 *  The XML to define multiple sql database servers used by application:
 *  <database>
 *  	<sql>
 *  		<{ENVIRONMENT_NAME}>
 *  			<server name="server1">
 *  				<driver>...</driver>
 *  				<host>...</host>
 *  				<port>...</port>
 *  				...{MORE CREDENTIALS}...
 *  			</server>
 *  			...{MORE <server> TAGS}...
 *  		</{ENVIRONMENT_NAME}>
 *  		...{MORE ENVIRONMENTS}...
 *  	</sql>
 *  </database>
 */
class SQLDataSourceInjector extends ApplicationListener {
	public function run() {
		$environment = $this->application->getAttribute("environment");
		
		// detect & inject sql data sources
		$xml = $this->application->getXML()->servers->sql->$environment;
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
		if(!$xml->server) throw new ServletException("Server not set for environment!");
		$xml = (array) $xml;
		if(is_array($xml["server"])) {
			foreach($xml["server"] as $element) {
				if(!isset($element["name"])) throw new ServletException("Attribute 'name' not set for <server> tag!");
				SQLConnectionFactory::setDataSource((string) $element["name"], $this->createDataSource($element));
			}
		} else {
			SQLConnectionSingleton::setDataSource($this->createDataSource($xml["server"]));
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
		$dataSource->setDriverOptions((array) $databaseInfo->options);
		$dataSource->setHost((string) $databaseInfo->host);
		$dataSource->setPort((string) $databaseInfo->port);
		$dataSource->setUserName((string) $databaseInfo->username);
		$dataSource->setPassword((string) $databaseInfo->password);
		$dataSource->setSchema((string) $databaseInfo->schema);
		return $dataSource;
	}
}