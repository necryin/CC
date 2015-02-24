<?php
/**
 * User: human
 * Date: 18.02.15
 */

namespace Necryin\CCBundle\Tests\Unit\Provider;

use Necryin\CCBundle\Exception\InvalidArgumentException;
use Necryin\CCBundle\Manager\ExchangeProviderManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Тест менеджера провайдеров
 *
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

        $container->expects($this->any())
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

    /**
     * @dataProvider providerFailGet
     */
    public function testManagerFailGet($exchangeProvider)
    {
        $this->setExpectedException(InvalidArgumentException::class,
            'Invalid exchange provider name');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exchangeProviderManager = new ExchangeProviderManager($container);
        $exchangeProviderManager->getProvider($exchangeProvider);
    }

    /**
     * @dataProvider providerFailAdd
     */
    public function testManagerFailAdd($exchangeProvider)
    {
        $this->setExpectedException(InvalidArgumentException::class,
            "Container doesn't have provider service {$exchangeProvider}");

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exchangeProviderManager = new ExchangeProviderManager($container);
        $exchangeProviderManager->addProvider($exchangeProvider, $exchangeProvider);
    }

    public function providerFailGet()
    {
        return [
          ['df'],
          [[1]]
        ];
    }

    public function providerFailAdd()
    {
        return [
            ['dd'],
            [null]
        ];
    }
}
