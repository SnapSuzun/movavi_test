<?php

namespace app\components;


use app\components\integration\CbrServiceApi;
use app\components\integration\CurrencyServiceAPI;
use app\components\integration\RbcCurrencyServiceAPI;

/**
 * Конвертер валют
 * Class CurrencyConverter
 * @package app\components
 */
class CurrencyConverter
{
    /**
     * Получение курса среднего курса валют относительно локальной на основе нескольких источников
     * @param string $currencyCharCode
     * @param string|null $date
     * @return float
     */
    public static function getExchangeRateToLocalCurrency(string $currencyCharCode, string $date = ''): float
    {
        $currencyServices = static::getCurrencyServiceClassNames();
        $exchangeRate = 0;
        if (!empty($currencyServices)) {
            foreach ($currencyServices as $className) {
                $exchangeRate += $className::getExchangeRateToLocalCurrency($currencyCharCode, $date);
            }
            $exchangeRate = floatval($exchangeRate / count($currencyServices));
        }

        return $exchangeRate;
    }

    /**
     * Получение списка сервисов предоставляющих курс валют
     * @return CurrencyServiceAPI[]
     */
    protected static function getCurrencyServiceClassNames(): array
    {
        return [
            CbrServiceApi::class,
            RbcCurrencyServiceAPI::class,
        ];
    }
}