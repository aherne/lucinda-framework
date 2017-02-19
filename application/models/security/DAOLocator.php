<?php
class DAOLocator {
	private $daoPath;

	public function __construct(SimpleXMLElement $xml) {
		$path = (string) $xml->application->paths->dao;
		if(!$path) throw new ApplicationException("Tag application.paths.dao is mandatory!");
		$this->daoPath = $path;
	}

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