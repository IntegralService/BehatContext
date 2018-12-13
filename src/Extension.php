<?php

namespace IntegralService;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;

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
    }

    /**
     * @param ArrayNodeDefinition $builder
     */
    public function configure(ArrayNodeDefinition $builder)
    {
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
