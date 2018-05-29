<?php
abstract class DataSourceDetection {
    protected $dataSource;
    
    public function __construct(SimpleXMLElement $databaseInfo) {
        $this->setDataSource($databaseInfo);
    }
    
    abstract protected function setDataSource(SimpleXMLElement $databaseInfo);
    
    public function getDataSource() {
        return $this->dataSource;
    }
}