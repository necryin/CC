<?php

namespace Necryin\CCBundle;

use Necryin\CCBundle\DependencyInjection\Compiler\ProviderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Бандл конвертера валют
 */
class NecryinCCBundle extends Bundle
{
    /**
     * Сборка DI контейнера
     *
     * @param ContainerBuilder $container DI контейнер
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ProviderCompilerPass());
    }

}
