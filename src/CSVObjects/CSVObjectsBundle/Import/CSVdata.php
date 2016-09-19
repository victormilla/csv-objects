<?php

namespace CSVObjects\CSVObjectsBundle\Import;

use CSVObjects\CSVObjectsBundle\ObjectProcurer\ObjectProcurer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Process;

class CSVData
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var int[]
     */
    private $resultRows;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function setResultRow($resultId, $dataRow)
    {
        $this->resultRows[$resultId] = $dataRow;
    }

    public function getLineOfResult($number)
    {
        return isset($this->resultRows[$number]) ? $this->resultRows[$number] : null;
    }

    public function getDataForResult($number, $asString = false)
    {
        if (!isset($this->resultRows[$number]) || !isset($this->data[$this->resultRows[$number]])) {
            return $asString ? '' : array();
        }

        if ($asString) {
            return implode(', ', $this->data[$this->resultRows[$number]]);
        }

        return $this->data[$this->resultRows[$number]];
    }
}
