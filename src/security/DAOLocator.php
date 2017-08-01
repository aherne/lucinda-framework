<?php
/**
 * Utility class for security listener, used to locates data access objects based on XML & class signature.
 */
class DAOLocator {
	private $daoPath;
	
	/**
	 * Creates an instance and detects DAOs folder from XML.
	 * 
	 * @param SimpleXMLElement $xml Root XML folder
	 * @throws ApplicationException If no path was given to DAOs
	 */
	public function __construct(SimpleXMLElement $xml) {
		$path = (string) $xml->application->paths->dao;
		if(!$path) throw new ApplicationException("Tag application.paths.dao is mandatory!");
		$this->daoPath = $path;
	}

	/**
	 * Locates a DAO from XML, then loads it from disk, then checks if its class has right signature then returns an instance of that class.
	 * 
	 * @param SimpleXMLElement $xml XML tag that should contain a reference to a DAO.
	 * @param string $parameterName XML property whose value should be the DAO class name.
	 * @param string $parentClassName Blueprint (interface) to verify DAO against 
	 * @throws ApplicationException If DAO could not be located in XML/disk or its signature does not match desired blueprint. 
	 * @return object
	 */
	public function locate(SimpleXMLElement $xml, $parameterName, $parentClassName) {
		$dao = (string) $xml[$parameterName];
		if(!$dao) throw new ApplicationException("'$parameterName' attribute is missing!");

		// load file
		$daoFile = $this->daoPath."/".$dao.".php";
		if(!file_exists($daoFile)) throw new ApplicationException("DAO file not found: ".$daoFile."!");
		require_once($daoFile);

		// locate class
		$daoObject = new $dao();
		if(!($daoObject instanceof $parentClassName)) throw new ApplicationException($dao." must be instance of ".$parentClassName."!");
		return $daoObject;
	}
}