<?php

namespace CSVObjects\CSVObjectsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('csv_objects');

        $rootNodeChildren = $rootNode->children();

        $rootNodeChildren->variableNode('classes')->end();

        return $treeBuilder;
    }
}
