<?php

namespace CSVObjects\CSVObjectsBundle\Import;

use CSVObjects\CSVObjectsBundle\ObjectProcurer\ObjectProcurer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Process;

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

    /**
     * @var string[]
     */
    private $data = array();

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
    public static function import(array $definition, $filename)
    {
        $import = new CSVImport(new ImportDefinition($definition));
        $file   = new File($filename);

        return $import->extractResultsFromFile($file);
    }

    /**
     * @param File $file
     * @param bool $rememberData
     *
     * @return array
     */
    public function extractResultsFromFile(File $file, $rememberData = false)
    {
        $data = $this->readFile($file);

        $this->addColumnCopies($data);
        $this->validate($data);

        $headings = $data[0];
        $results  = array();

        if ($rememberData) {
            $this->data = $data;
        }

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
        $extension = $file->guessExtension();

        if ('xlsx' === $extension) {
            $pathname = sys_get_temp_dir() . uniqid('/xlsx2csv_', true);
            $process = new Process('python ' . __DIR__ . '/xlsx2csv.py ' . $file->getPathname() . ' ' . $pathname);
            $process->run();

            if (!$process->isSuccessful()) {
                unlink($pathname);
                throw new \InvalidArgumentException('Unable to convert XLSX file into CSV format');
            }
        } elseif ('csv' === $extension || 'txt' === $extension) {
            $pathname = $file->getPathname();
        } else {
            throw new \InvalidArgumentException('Unrecognised file type. The valid types are CSV and XLSX');
        }

        $fileHandle = fopen($pathname, 'r');
        $data       = array();

        while (!feof($fileHandle)) {
            $line = fgetcsv($fileHandle);

            if (false !== $line) {
                $data[] = $line;
            }
        }

        fclose($fileHandle);
        if ('xlsx' === $extension) {
            unlink($pathname);
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

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        foreach ($this->classes as $class) {
            $class->setContainer($container);
        }
    }

    /**
     * @return string[]
     */
    public function getData()
    {
        return $this->data;
    }
}
