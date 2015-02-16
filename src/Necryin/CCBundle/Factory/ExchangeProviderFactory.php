<?php
/**
 * User: go
 * Date: 13.02.15
 */

namespace Necryin\CCBundle\Factory;

use Necryin\CCBundle\Provider\ExchangeProviderInterface;

class ExchangeProviderFactory
{
    private $providers;

    public function __construct()
    {
        $this->providers = [];
    }

    public function addProvider(ExchangeProviderInterface $provider, $alias)
    {
        $this->providers[$alias] = $provider;
    }

    public function getProvider($alias)
    {
        if (empty($this->providers))
        {
            throw new \Exception('There are no exchange providers');
        }
        if (array_key_exists($alias, $this->providers))
        {
            return $this->providers[$alias];
        }
        return array_values($this->providers)[0];
    }
}
