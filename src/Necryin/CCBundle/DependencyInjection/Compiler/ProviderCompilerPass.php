<?php
/**
 * User: go
 * Date: 13.02.15
 */

namespace Necryin\CCBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ProviderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('necryin.exchange_provider_factory'))
        {
            return;
        }

        $definition = $container->getDefinition('necryin.exchange_provider_factory');
        $taggedServices = $container->findTaggedServiceIds('necryin.exchange_provider');

        foreach ($taggedServices as $id => $tags)
        {
            foreach ($tags as $attributes)
            {
                $definition->addMethodCall(
                    'addProvider',
                    array(new Reference($id), $attributes["alias"])
                );
            }
        }
    }
}
