<?php
require_once("vendor/lucinda/sql-data-access/loader.php");
require_once("src/datasource_detection/SQLDatasourceDetection.php");

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
 *  			<server driver="..." host="..." port="..." .../>
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
 *  			<server name="..." driver="..." host="..." port="..." .../>
 *  			...{MORE <server> TAGS}...
 *  		</{ENVIRONMENT_NAME}>
 *  		...{MORE ENVIRONMENTS}...
 *  	</sql>
 *  </database>
 */
class SQLDataSourceInjector extends ApplicationListener {
	public function run() {
		$environment = $this->application->getAttribute("environment");
		$xml = $this->application->getXML()->servers->sql->$environment;
		if(!empty($xml)) {
		    if(!$xml->server) throw new ApplicationException("Server not set for environment!");
		    $xml = (array) $xml;
		    if(is_array($xml["server"])) {
		        foreach($xml["server"] as $element) {
		            if(!isset($element["name"])) throw new ApplicationException("Attribute 'name' not set for <server> tag!");
		            $dsd = new SQLDataSourceDetection($element);
		            SQLConnectionFactory::setDataSource((string) $element["name"], $dsd->getDataSource());
		        }
		    } else {
		        $dsd = new SQLDataSourceDetection($xml["server"]);
		        SQLConnectionSingleton::setDataSource($dsd->getDataSource());
		    }
		}
	}
}