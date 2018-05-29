<?php
require_once("vendor/lucinda/nosql-data-access/loader.php");
require_once("src/datasource_detection/NoSQLDatasourceDetection.php");

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
        $xml = $this->application->getXML()->servers->sql->$environment;
        if(!empty($xml)) {
            if(!$xml->server) throw new ApplicationException("Server not set for environment!");
            $xml = (array) $xml;
            if(is_array($xml["server"])) {
                foreach($xml["server"] as $element) {
                    if(!isset($element["name"])) throw new ApplicationException("Attribute 'name' not set for <server> tag!");
                    $dsd = new NoSQLDataSourceDetection($element);
                    NoSQLConnectionFactory::setDataSource((string) $element["name"], $dsd->getDataSource());
                }
            } else {
                $dsd = new NoSQLDataSourceDetection($xml["server"]);
                NoSQLConnectionSingleton::setDataSource($dsd->getDataSource());
            }
        }
    }
}