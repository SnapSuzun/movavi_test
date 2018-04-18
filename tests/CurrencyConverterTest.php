<?php

namespace app\tests;

use app\components\CurrencyConverter;
use PHPUnit\Framework\TestCase;

/**
 * Class CurrencyConverterTest
 */
class CurrencyConverterTest extends TestCase
{
    /**
     * Проверка на корректность рассчитываемых данных
     * @dataProvider providerGetExchangeRate
     * @param $currencyCharCode
     * @param $date
     * @param $expected
     */
    public function testGetExchangeRate($currencyCharCode, $date, $expected)
    {
        $this->assertEquals($expected, CurrencyConverter::getExchangeRateToLocalCurrency($currencyCharCode, $date));
    }

    public function providerGetExchangeRate()
    {
        return [
            ['USD', '01-01-2010', 30.1851],
            ['EUR', '02-05-2012', 38.9203],
            ['DKK', '29-05-2005', 4.72532],
            ['JPY', '20-01-2000', 0.2703]
        ];
    }
}