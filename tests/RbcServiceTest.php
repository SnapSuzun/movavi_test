<?php
namespace app\tests;

use app\components\http\HttpRequestException;
use app\components\integration\RbcCurrencyServiceAPI;
use PHPUnit\Framework\TestCase;

/**
 * Class RbcServiceTest
 */
class RbcServiceTest extends TestCase
{
    /**
     * Проверка на доступность сервиса
     */
    public function testServiceIsAvailable()
    {
        $this->assertTrue(is_float(RbcCurrencyServiceAPI::getExchangeRateToLocalCurrency('USD', '14-04-2018')));
    }

    /**
     * Проверка на использование различных форматов дат
     * @dataProvider providerAvailableDates
     *
     * @param $date
     */
    public function testAvailableDateFormats($date)
    {
        $this->assertNotEmpty(RbcCurrencyServiceAPI::getExchangeRateToLocalCurrency('USD', $date), 'RBC service has not data for date');
    }

    /**
     * Проверка на ошибки при использовании невалидных аргументов
     * @dataProvider providerInvalidArguments
     *
     * @param $currencyCharCodeFrom
     * @param $currencyCharCodeTo
     * @param $amount
     * @param $date
     */
    public function testInvalidArguments($currencyCharCodeFrom, $currencyCharCodeTo, $amount, $date)
    {
        $this->expectException(\InvalidArgumentException::class);
        RbcCurrencyServiceAPI::convertCurrency($currencyCharCodeFrom, $currencyCharCodeTo, $amount, $date);
    }

    /**
     * @dataProvider providerHandlingRequestExceptions
     *
     * @param $currencyCharCodeFrom
     * @param $currencyCharCodeTo
     * @param $amount
     * @param $date
     * @param $source
     */
    public function testHandlingRequestExceptions($currencyCharCodeFrom, $currencyCharCodeTo, $amount, $date, $source)
    {
        $this->expectException(HttpRequestException::class);
        RbcCurrencyServiceAPI::convertCurrency($currencyCharCodeFrom, $currencyCharCodeTo, $amount, $date, $source);
    }

    /**
     * @dataProvider providerCorrectData
     *
     * @param $currencyCharCodeFrom
     * @param $currencyCharCodeTo
     * @param $amount
     * @param $date
     * @param $expected
     */
    public function testOnCorrectData($currencyCharCodeFrom, $currencyCharCodeTo, $amount, $date, $expected)
    {
        $this->assertEquals($expected, RbcCurrencyServiceAPI::convertCurrency($currencyCharCodeFrom, $currencyCharCodeTo, $amount, $date, RbcCurrencyServiceAPI::SOURCE_CBRF));
    }

    public function providerInvalidArguments()
    {
        return [
            ['USD', 'RUR', 123, '2000-1222-87898'],
            ['USD', 'RUR', 123, date('d-m-Y', strtotime('+1year'))],
            ['USD', 'RUR', 123, strtotime('+1month')],
            ['USD', 'RUR', 123, '02/22/200'],
            ['USD', 'RUR', 123, '100-100-100'],
            ['USD', 'RUR', 123, '13213123'],
            ['USD', 'RUR', 123, 'vbdsajhads'],
            ['USD', 'RUR', 0, '01-01-2000'],
            ['USD', 'USD', 1, '01-01-2000'],
        ];
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

    public function providerHandlingRequestExceptions()
    {
        return [
            ['RU', 'USD', 12312, '01-01-2000', 'cbrf'],
            ['RUR', '', 12312, '01-01-2000', 'cbrf'],
        ];
    }

    public function providerCorrectData()
    {
        return [
            ['USD', 'RUR', 100, '01-01-2010', 3018.5098],
            ['EUR', 'RUR', 100, '01-01-2010', 4346.0499],
            ['DKK', 'RUR', 100, '01-01-2010', 583.964],
            ['DKK', 'RUR', -100, '01-01-2010', -583.964],
            ['RUR', 'USD', 100, '01-01-2010', 3.3129],
        ];
    }
}