<?php

namespace app\tests;

use app\components\http\HttpRequestException;
use app\components\integration\CbrServiceApi;
use PHPUnit\Framework\TestCase;

/**
 * Class CbrServiceTest
 */
class CbrServiceTest extends TestCase
{
    /**
     * Проверка на доступность сервиса
     */
    public function testServiceIsAvailable()
    {
        $this->assertTrue(is_float(CbrServiceApi::getExchangeRateToLocalCurrency('USD', '14-04-2018')));
    }

    /**
     * Проверка на использование различных форматов дат
     * @dataProvider providerAvailableDates
     * @param $date
     */
    public function testAvailableDateFormats($date)
    {
        $this->assertNotEmpty(CbrServiceApi::dailyCurrencyRates($date), 'CBR service has not data for date');
    }

    /**
     * Проверка на ошибки при использовании невалидных аргументов
     * @dataProvider providerInvalidArguments
     * @param $currencyCharCode
     * @param $date
     */
    public function testInvalidArguments($currencyCharCode, $date)
    {
        $this->expectException(\InvalidArgumentException::class);
        CbrServiceApi::getExchangeRateToLocalCurrency($currencyCharCode, $date);
    }

    /**
     * Проверка на обработку ошибок от сервиса
     */
    public function testHandlingRequestErrors()
    {
        $this->expectException(HttpRequestException::class);
        CbrServiceApi::dailyCurrencyRates('01-01-1990');
    }

    /**
     * Проверка на корректность курса валют
     * @dataProvider providerForCorrectData
     * @param $date
     * @param $currencyCharCode
     * @param $value
     */
    public function testOnCorrectData($date, $currencyCharCode, $value)
    {
        $this->assertEquals($value, CbrServiceApi::getExchangeRateToLocalCurrency($currencyCharCode, $date));
    }

    public function providerAvailableDates()
    {
        return [
            ['-15days'],
            ['-2months'],
            ['01-01-2000'],
            [date('d-m-Y')],
            ['03/02/2009'],
            ['2009/03/02'],
            [date('Y-m-d')],
            ['01.01.2000'],
            ['01-01-1999'],
        ];
    }

    public function providerInvalidArguments()
    {
        return [
            ['USD', '2000-1222-87898'],
            ['EUR', date('d-m-Y', strtotime('+1year'))],
            ['', strtotime('+1month')],
            ['RUR', '02/22/200'],
            ['DKK', '100-100-100'],
            ['SEK', '13213123'],
            ['', 'vbdsajhads'],
            ['US', '01-01-2000'],
            ['', '01-01-2000'],
        ];
    }

    public function providerForCorrectData()
    {
        return [
            ['01-01-2000', 'USD', 27],
            ['01-01-2000', 'BEF', 0.6743],
            ['01-01-2000', 'DKK', 3.6580],
            ['01-01-2000', 'GRD', 0.08246],
        ];
    }
}