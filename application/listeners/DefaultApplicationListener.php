<?php
require_once("libraries/sql-data-access-api/classes/DatabaseConnectionSingleton.php");

class DefaultApplicationListener extends ApplicationListener {
	public function run() {
		// detect environment
		$this->setEnvironment();

		// inject into datasource
		$this->setDataSource();

		// detect default password new users will be registered with
		$this->setDefaultPassword();

		// detect secret key remember me cookie will be encoded with
		$this->setRememberMeSecret();

		// detect view compilations folder
		$this->setCompilationsFolder();

		// detect authorization code
		$this->setAuthorizationCode();

		// detect parent schema
		$this->setParentSchema();

		// detect htaccess location
		$this->setHtaccessLocation();
	}

	/**
	 * Detects execution environment and saves it into "environment" attribute
	 */
	private function setEnvironment() {
		// detect environment
		$environment = "";
		if($_SERVER["SERVER_NAME"]==(string) $this->application->getXML()->application->web_servers->live) {
			$environment = "live";
		} else if($_SERVER["SERVER_NAME"]==(string) $this->application->getXML()->application->web_servers->dev) {
			$environment = "dev";
		} else {
			$environment = "local";
		}
		// save detected environment
		$this->application->setAttribute("environment", $environment);
	}

	/**
	 * Creates a datasource instance and injects it into DatabaseCOnnectionSingleton
	 */
	private function setDataSource() {
		// detect database info
		$environment = $this->application->getAttribute("environment");
		$databaseInfo = $this->application->getXML()->database->$environment;

		// set datasource and inject it
		$dataSource = new DataSource();
		$dataSource->setDriverName((string) $databaseInfo->driver);
		$dataSource->setHost((string) $databaseInfo->host);
		$dataSource->setPort((string) $databaseInfo->port);
		$dataSource->setUserName((string) $databaseInfo->username);
		$dataSource->setPassword((string) $databaseInfo->password);
		$dataSource->setDriverName((string) $databaseInfo->driver);
		$dataSource->setSchema((string) $databaseInfo->schema);

		// inject datasource into connection
		DatabaseConnectionSingleton::setDataSource($dataSource);
	}

	/**
	 * Sets default password for new accounts.
	 */
	private function setDefaultPassword() {
		$this->application->setAttribute("default_password", (string) $this->application->getXML()->application->default_password);
	}

	/**
	 * Detect secret key remember me cookie will be encoded with
	 */
	private function setRememberMeSecret() {
		$this->application->setAttribute("remember_me_secret", (string) $this->application->getXML()->application->secrets->remember_me);
	}

	/**
	 * Sets compilations folder
	 */
	private function setCompilationsFolder() {
		$environment = $this->application->getAttribute("environment");
		$compilationsInfo = (string) $this->application->getXML()->application->paths->compilations->$environment;
		$this->application->setAttribute("compilations_folder", $compilationsInfo);
	}

	/**
	 * Sets authorization code to query datamother api with.
	 */
	private function setAuthorizationCode() {
		$this->application->setAttribute("authorization_code", (string) $this->application->getXML()->application->secrets->authorization_code);
	}

	/**
	 * Set parent site's schema. (eg: casinosf_db)
	 */
	private function setParentSchema() {
		// detect database info
		$environment = $this->application->getAttribute("environment");
		$databaseInfo = $this->application->getXML()->database->$environment;

		$this->application->setAttribute("parent_schema", (string) $databaseInfo->parent_schema);
	}

	/**
	 * Sets parent site's htaccess location
	 */
	private function setHtaccessLocation() {
		$environment = $this->application->getAttribute("environment");
		$htaccessLocation = (string) $this->application->getXML()->application->paths->htaccess->$environment;
		$this->application->setAttribute("htaccess_location", $htaccessLocation);
	}
}