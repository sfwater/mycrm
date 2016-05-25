<?php

namespace Admin\ConsoleBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AdminConsoleExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('config.yml');
    }
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration();
    }

    class Configuration implements ConfigurationInterface
    {
        /**
         * {@inheritdoc}
         */
        public function getConfigTreeBuilder()
        {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('Console');
            // Here you should define the parameters that are allowed to
            // configure your bundle. See the documentation linked above for
            // more information on that topic.
            $rootNode->children()
                    ->scalarNode('name')->end()
                ->end()
            ->end();

            return $treeBuilder;
        }
    }
}
