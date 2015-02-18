<?php
/**
 * User: human
 * Date: 18.02.15
 */

namespace Necryin\CCBundle\Tests\Unit\Provider;

use Necryin\CCBundle\Exception\ExchangeProviderManagerException;
use Necryin\CCBundle\Manager\ExchangeProviderManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Тест менеджера провайдеров
 * Class ExchangeProviderManager
 */
class ExchangeProviderManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testManager()
    {
        $providers = ['cb', 'openexchange'];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects($this->once())
            ->method('has')
            ->with('1')
            ->will($this->returnValue(true));

        $container->expects($this->once())
            ->method('get')
            ->with('1')
            ->will($this->returnValue('1'));

        $exchangeProviderManager = new ExchangeProviderManager($container);

        foreach($providers as $provider)
        {
            $exchangeProviderManager->addProvider('1', $provider);
        }

        $this->assertEquals($providers, $exchangeProviderManager->getAliases());
        $this->assertEquals('1', $exchangeProviderManager->getProvider('cb'));
    }

    public function testManagerFail()
    {
        $this->setExpectedException(ExchangeProviderManagerException::class);
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exchangeProviderManager = new ExchangeProviderManager($container);
        $exchangeProviderManager->getProvider('df');
    }
}
