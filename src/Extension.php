<?php

namespace IntegralService;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use IntegralService\BehatContext\CoverageContext;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 *
 */
class Extension implements ExtensionInterface
{
    /**
     * @return string
     */
    public function getConfigKey()
    {
        return 'integralservice';
    }

    /**
     * @param ExtensionManager $extensionManager
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadClassResolver($container);

        CoverageContext::setWhitelist($config['whitelist']);
        CoverageContext::setResultFile($config['result_file']);
    }

    /**
     * @param ArrayNodeDefinition $builder
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $defaultWhitelist = ['src'];
        $defaultResultFile = 'results/behat_coverage.xml';

        $builder->children()
                    ->arrayNode('whitelist')
                        ->beforeNormalization()
                            ->castToArray()
                            ->ifEmpty()->then(function () use ($defaultWhitelist) {return $defaultWhitelist;})
                        ->end()

                        ->scalarPrototype()->end()
                        ->defaultValue($defaultWhitelist)
                    ->end()

                    ->scalarNode('result_file')
                        ->cannotBeEmpty()
                        ->defaultValue($defaultResultFile)
                    ->end()
                ->end()
        ;
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadClassResolver(ContainerBuilder $container)
    {
        $definition = new Definition('IntegralService\BehatContext\ContextClass\ClassResolver');
        $definition->addTag(ContextExtension::CLASS_RESOLVER_TAG);
        $container->setDefinition('integralservice.class_resolver', $definition);
    }

    /**
     * @return array
     */
    public function getCompilerPasses()
    {
        return [];
    }
}
