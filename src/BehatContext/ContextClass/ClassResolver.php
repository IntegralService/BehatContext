<?php

namespace IntegralService\BehatContext\ContextClass;

use Behat\Behat\Context\ContextClass\ClassResolver as BaseClassResolver;

/**
 * ClassResolver class
 */
class ClassResolver implements BaseClassResolver
{
    /**
     * @param string $contextClass
     * @return bool
     */
    public function supportsClass($contextClass)
    {
        return (strpos($contextClass, 'integralservice:context:') === 0);
    }

    /**
     * @param string $contextClass
     * @return string
     */
    public function resolveClass($contextClass)
    {
        $className = preg_replace_callback('/(^\w|:\w)/', function ($matches) {
            return str_replace(':', '\\', strtoupper($matches[0]));
        }, $contextClass);

        $validNamespaceClassname = str_replace(["Integralservice", "Context"], ["IntegralService", "BehatContext"], $className);

        return $validNamespaceClassname . 'Context';
    }
}
