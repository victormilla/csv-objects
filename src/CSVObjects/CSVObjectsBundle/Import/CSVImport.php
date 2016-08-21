<?php

namespace CSVObjects\CSVObjectsBundle\Import;

use CSVObjects\CSVObjectsBundle\ObjectProcurer\ObjectProcurer;
use Symfony\Component\HttpFoundation\File\File;

class CSVImport
{
    /**
     * @var ImportDefinition
     */
    private $definition;

    /**
     * @var string[]
     */
    private $columnNames;

    /**
     * @var string
     */
    private $returnClass;

    /**
     * @var ObjectProcurer[]
     */
    private $classes;

    public function __construct(ImportDefinition $definition)
    {
        $this->definition  = $definition;
        $this->columnNames = $definition->getColumnNames();
        $this->classes     = $definition->getClasses();
        $this->returnClass = $definition->getReturnClass();
    }

    /**
     * @param array  $definition
     * @param string $filename
     *
     * @return object[]
     */
    public static function import(array $definition, string $filename)
    {
        $import = new CSVImport(new ImportDefinition($definition));
        $file   = new File($filename);

        return $import->extractResultsFromFile($file);
    }

    /**
     * @param File $file
     *
     * @return array
     */
    private function extractResultsFromFile(File $file)
    {
        $data = $this->readFile($file);

        $this->addColumnCopies($data);
        $this->validate($data);

        $headings = $data[0];
        $results  = array();

        for ($i = 1; $i < count($data); $i++) {
            foreach ($this->createResults(array_combine($headings, $data[$i])) as $result) {
                $results[] = $result;
            }
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
        $data      = array();
        $extension = strtolower($file->getExtension());

        if (!in_array($extension, ['csv', 'xlsx'])) {
            $extension = $file->guessExtension();
        }

        if ('csv' === $extension) {
            $fileHandle = fopen($file->getPathname(), 'r');
            $data       = array();

            while (!feof($fileHandle)) {
                $line = fgetcsv($fileHandle);

                if (false !== $line) {
                    $data[] = $line;
                }
            }

            fclose($fileHandle);
        } elseif ('xlsx' === $extension) {
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
            throw new \InvalidArgumentException('File does not have content');
        }

        if (count($data[0]) !== count($this->columnNames)) {
            throw new \InvalidArgumentException('File does not have the required number of columns');
        }

        foreach ($data[0] as $column) {
            if (!in_array($column, $this->columnNames)) {
                throw new \InvalidArgumentException('File contains an unexpected column ' . $column);
            }
        }
    }

    /**
     * @param array $row
     *
     * @return object[]
     */
    private function createResults(array $row)
    {
        $r = array();

        foreach ($this->definition->getArgumentsByData($row) as $instance) {
            $r[] = $this->classes[$this->returnClass]->procure($instance);
        }

        return $r;
    }

    /**
     * @param array $data
     */
    private function addColumnCopies(array &$data)
    {
        if (!isset($data[0])) {
            return;
        }

        $headings = array_flip($data[0]);

        foreach ($this->definition->getColumnCopies() as $newColumnName => $columnName) {
            if (!isset($headings[$columnName])) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'The description said to copy the column \'%s\' from the column \'%s\', but it doesn\'t exist on the file.',
                        $newColumnName,
                        $columnName
                    )
                );
            }

            // Headings
            $data[0][] = $newColumnName;

            // Data
            for ($i = 1; $i < count($data); $i++) {
                $data[$i][] = $data[$i][$headings[$columnName]];
            }
        }
    }
}
