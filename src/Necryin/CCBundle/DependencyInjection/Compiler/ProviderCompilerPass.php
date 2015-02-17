<?php
/**
 * User: go
 * Date: 13.02.15
 */

namespace Necryin\CCBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Компилирует DI контейнер
 * Class ProviderCompilerPass
 */
class ProviderCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if(!$container->hasDefinition('necryin.exchange_provider_manager'))
        {
            return;
        }

        $definition = $container->getDefinition('necryin.exchange_provider_manager');
        $taggedServices = $container->findTaggedServiceIds('necryin.exchange_provider');

        foreach($taggedServices as $id => $tags)
        {
            foreach($tags as $attributes)
            {
                $definition->addMethodCall('addProvider', [$id, $attributes["alias"]]);
            }
        }
    }
}
