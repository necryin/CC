<?php
/**
 * User: human
 * Date: 18.02.15
 */
namespace Necryin\CCBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class NecryinCCExtension
 */
class NecryinCCExtension extends Extension
{

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'necryin_cc';
    }

    /**
     * Загружаем конфигурацию бандла
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }
}
