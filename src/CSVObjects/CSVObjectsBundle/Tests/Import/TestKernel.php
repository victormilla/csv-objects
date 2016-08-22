<?php

namespace CSVObjects\CSVObjectsBundle\Tests\Import;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    public function getRootDir()
    {
        return __DIR__.'/../../Resources';
    }

    public function registerBundles()
    {
        return array(
            new \CSVObjects\CSVObjectsBundle\CSVObjectsBundle(),
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/../../Resources/config/config_'.$this->getEnvironment().'.yml');
    }
}
