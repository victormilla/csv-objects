<?php

namespace CSVObjects\CSVObjectsBundle\Tests\Repositories;

use CSVObjects\CSVObjectsBundle\Tests\Objects\School;

class SchoolRepository
{
    /**
     * @var School[]
     */
    private $schoolsByName;

    public function addSchool(School $school)
    {
        $this->schoolsByName[$school->getName()] = $school;
    }

    /**
     * @param string $name
     *
     * @return School|null
     */
    public function findSchoolByName($name)
    {
        return isset($this->schoolsByName[$name])
            ? $this->schoolsByName[$name]
            : null;
    }

    /**
     * @return School[]
     */
    public function getAllSchools()
    {
        return $this->schoolsByName;
    }
}
