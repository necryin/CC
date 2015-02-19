<?php
/**
 * User: human
 * Date: 18.02.15
 */
namespace Necryin\CCBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Компилирует DI контейнер
 */
class ProviderCompilerPass implements CompilerPassInterface
{
    /**
     * Сохраняем информацию о всех провайдерах курсов валют в менеджер провайдеров
     *
     * @param ContainerBuilder $container DI контейнер
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
                $definition->addMethodCall('addProvider', [$id, $attributes['alias']]);
            }
        }
    }
}
