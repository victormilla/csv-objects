<?php

namespace CSVObjects\CSVObjectsBundle\Import;

use CSVObjects\CSVObjectsBundle\ObjectProcurer\ObjectProcurer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Process;

class ImportedResults
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var int[]
     */
    private $dataRows;

    /**
     * @var array
     */
    private $results;

    public function __construct($rawData = null)
    {
        $this->data = $rawData;
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param $result
     * @param int $dataRow
     */
    public function addResult($result, $dataRow = null)
    {
        $this->results[]  = $result;
        $this->dataRows[] = $dataRow;
    }

    /**
     * @param int $number
     * @return int|null
     */
    public function getLineOfResult($number)
    {
        return isset($this->dataRows[$number]) ? $this->dataRows[$number] : null;
    }

    /**
     * @param int $number
     * @param bool $asString
     * @return string|string[]|null
     */
    public function getRawDataForResult($number, $asString = false)
    {
        if (!isset($this->dataRows[$number]) || !isset($this->data[$this->dataRows[$number]])) {
            return $asString ? '' : array();
        }

        if ($asString) {
            return implode(', ', $this->data[$this->dataRows[$number]]);
        }

        return $this->data[$this->dataRows[$number]];
    }

    /**
     * @return string[]|null
     */
    public function getRawData()
    {
        return $this->data;
    }
}
