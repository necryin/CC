<?php
/**
 * User: human
 * Date: 24.02.15
 */

namespace Necryin\CCBundle\Tests\Unit;

use Necryin\CCBundle\Exception\InvalidArgumentException;
use Necryin\CCBundle\Object\Currency;

class CurrencyTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider provider
     */
    public function testConvertDivideByZero($input, $output)
    {
        if (isset($output['exception']))
        {
            $this->setExpectedException($output['exception']['class'], $output['exception']['message']);
        }

        $currency = new Currency($input);
        if (!isset($output['exception']))
        {
            $this->assertEquals($currency->getCurrencyCode(), $output);
        }

    }

    public function provider()
    {
        return [
            [
                [1],
                [
                    'exception' => [
                        'class'   => InvalidArgumentException::class,
                        'message' => 'Invalid currency code'
                    ]
                ]
            ],
            [
                'FFF',
                [
                    'exception' => [
                        'class'   => InvalidArgumentException::class,
                        'message' => 'Unknown currency code FFF'
                    ]
                ]
            ],
            [
                'XXX',
                'XXX'
            ],
            [
                643,
                'RUB'
            ],
            [
                666,
                [
                    'exception' => [
                        'class'   => InvalidArgumentException::class,
                        'message' => 'Unknown currency code 666'
                    ]
                ]
            ],
        ];
    }
}
