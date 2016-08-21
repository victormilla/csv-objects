<?php

namespace CSVObjects\CSVObjectsBundle;

use CSVObjects\CSVObjectsBundle\DependencyInjection\CSVObjectsExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CSVObjectsBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new CSVObjectsExtension();
    }
}
