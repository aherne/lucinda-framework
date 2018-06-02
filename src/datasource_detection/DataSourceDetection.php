<?php
/**
 * Encapsulates data source detection (itself encapsulating database server settings) from an XML tag
 */
abstract class DataSourceDetection {
    protected $dataSource;

    /**
     * DataSourceDetection constructor.
     * @param SimpleXMLElement $databaseInfo XML tag containing data source info.
     */
    public function __construct(SimpleXMLElement $databaseInfo) {
        $this->setDataSource($databaseInfo);
    }

    /**
     * Detects data source (itself encapsulating database server settings) from an XML tag
     *
     * @param SimpleXMLElement $databaseInfo
     * @return mixed
     */
    abstract protected function setDataSource(SimpleXMLElement $databaseInfo);

    /**
     * Gets detected data source
     *
     * @return object
     */
    public function getDataSource() {
        return $this->dataSource;
    }
}