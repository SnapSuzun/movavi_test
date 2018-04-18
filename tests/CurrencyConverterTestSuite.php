<?php

namespace app\tests;

use PHPUnit\Framework\TestSuite;

/**
 * Class CurrencyConverterTestSuite
 */
class CurrencyConverterTestSuite extends TestSuite
{
    /**
     * Пачка тестов
     * @return CurrencyConverterTestSuite
     */
    public static function suite()
    {
        $suite = new CurrencyConverterTestSuite('CurrencyConverterTests');
        $suite->addTestSuite(CbrServiceTest::class);
        $suite->addTestSuite(RbcServiceTest::class);
        $suite->addTestSuite(CurrencyConverterTest::class);

        return $suite;
    }
}