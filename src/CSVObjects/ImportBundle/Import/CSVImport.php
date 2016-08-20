<?php

namespace CSVObjects\ImportBundle\Import;

use Symfony\Component\HttpFoundation\File\File;

class CSVImport
{
    const SCHOOL_FROM_ID = 'Name';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $columns;

    /**
     * @var string
     */
    private $schoolColumn;

    /**
     * @var string
     */
    private $schoolSearchBy;

    /**
     * @var string
     */

    private $studentColumn;

    /**
     * @var string
     */
    private $studentSearchBy;

    /**
     * @var string
     */
    private $id;

    /**
     * @var FullResultId[]
     */
    private $assessments;

    public function __construct($id, $name, $columns, $schoolColumn, $schoolSearchBy, $studentColumn, $studentSearchBy, $assessments)
    {
        $this->id              = $id;
        $this->name            = $name;
        $this->columns         = array_flip($columns);
        $this->schoolColumn    = $schoolColumn;
        $this->schoolSearchBy  = $schoolSearchBy;
        $this->studentColumn   = $studentColumn;
        $this->studentSearchBy = $studentSearchBy;
        $this->assessments     = $assessments;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param File $file
     *
     * @return array
     */
    public function extractResultsFromFile(File $file)
    {
        $data = $this->readFile($file);
        $this->validate($data);

        $header  = array_flip($data[0]);
        $results = array();
        for ($i = 1; count($data); $i++) {
            $results[] = $this->createResult($header, $data[$i]);
        }

        return $results;
    }

    /**
     * @param File $file
     *
     * @return string[]
     */
    private function readFile(File $file)
    {
        $data = array();

        if ('csv' === $file->guessExtension()) {
            $fileHandle = fopen($file->getPathname(), 'r');
            $data       = array();

            while (!feof($fileHandle)) {
                $data[] = fgetcsv($fileHandle);
            }

            fclose($fileHandle);
        } elseif ('xlsx' === $file->guessExtension()) {
            // @TODO read excel to array
        } else {
            throw new \InvalidArgumentException('Unrecognised file type. The valid types are CSV and XLSX');
        }

        return $data;
    }

    /**
     * @param string[] $data
     */
    private function validate($data)
    {
        if (!is_array($data) || !isset($data[0])) {
            throw new \InvalidArgumentException('File does not have CSV content');
        }

        if (count($data[0]) !== count($this->columns)) {
            throw new \InvalidArgumentException('File does not have the required number of columns');
        }

        foreach ($data[0] as $column) {
            if (!isset($this->columns[$column])) {
                throw new \InvalidArgumentException('File contains an unexpected column ' . $column);
            }
        }
    }

    /**
     * @param string[] $header
     * @param string[] $row
     *
     * @return FullResult
     */
    private function createResult($header, $row)
    {
        // @TODO return parsed row

        return null;
    }
}
