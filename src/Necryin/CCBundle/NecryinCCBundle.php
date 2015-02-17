<?php

namespace Necryin\CCBundle;

use Necryin\CCBundle\DependencyInjection\Compiler\ProviderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class NecryinCCBundle
 */
class NecryinCCBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ProviderCompilerPass());
    }

}
