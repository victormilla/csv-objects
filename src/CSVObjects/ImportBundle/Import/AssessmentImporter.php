<?php

namespace CSVObjects\ImportBundle\Import;

use Symfony\Component\Finder\Finder;

class AssessmentImporter
{
    /**
     * @var ImportFormat[]
     */
    private $formats;

    /**
     * @var SchoolRepository
     */
    private $schoolRepository;

    public function __construct(SchoolRepository $schoolRepository)
    {

        $this->schoolRepository = $schoolRepository;
    }

    /**
     * @return ImportFormat[]
     */
    public function getFormats()
    {
        if (null === $this->formats) {
            $this->loadImportFormats();
        }

        return $this->formats;
    }

    /**
     * @param string $id
     *
     * @return ImportFormat|null
     */
    public function getFormat($id)
    {
        if (null === $this->formats) {
            $this->loadImportFormats();
        }

        return (isset($this->formats[$id])) ? $this->formats[$id] : null;
    }

    private function readYmlFolder($name)
    {
        $container = Legacy::getContainer();
        Legacy::loadLegacyEnvironment();

        $yamlWarmer = Legacy::getContainer()->get('systems_data_cacher.yaml_warmer');
        $ccrPaths   = $container->get('ccr.paths');
        $paths      = $ccrPaths->getConfigurationPaths();
        $folder     = $ccrPaths->getLocalCCRPath() . $paths['transition'][$name];

        $finder = new Finder();
        $finder->name('*.yml');
        $contents = array();
        /** @var \SplFileInfo $file */
        foreach ($finder->in($folder) as $file) {
            $contents[$file->getBasename('.yml')] = $yamlWarmer->parse($file->getRealPath());
        }

        return $contents;
    }

    private function loadImportFormats()
    {
        $formats = $this->readYmlFolder('importFormats');

        foreach ($formats as $id => $format) {
            $columns         = array();
            $schoolColumn    = null;
            $schoolSearchBy  = null;
            $studentColumn   = null;
            $studentSearchBy = null;
            $assessments     = array();
            foreach ($format['columns'] as $name => $data) {
                $columns[] = $name;
                if (null !== $data) {
                    if (isset($data['school'])) {
                        $schoolColumn   = $name;
                        $schoolSearchBy = $data['school'];
                    }
                    if (isset($data['student'])) {
                        $studentColumn   = $name;
                        $studentSearchBy = $data['student'];
                    }
                    if (isset($data['assessment'])) {
                        $assessments[$name] = new FullResultId(
                            $data['assessment'][AssessmentInfo::QUALIFICATION],
                            $data['assessment'][AssessmentInfo::ASPECT],
                            $data['assessment'][AssessmentInfo::POINT],
                            $data['assessment'][AssessmentInfo::COMPONENT]
                        );
                    }
                }
            }

            $format = new ImportFormat(
                $id,
                $format['name'],
                $columns,
                $schoolColumn,
                $schoolSearchBy,
                $studentColumn,
                $studentSearchBy,
                $assessments
            );

            $this->formats[$format->getId()] = $format;
        }
    }
}
