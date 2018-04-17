<?php

namespace app\components\integration;

/**
 * Class RbcCurrencyServiceAPI
 * @package app\components\integration
 */
class RbcCurrencyServiceAPI extends CurrencyServiceAPI
{
    /** Адрес сервиса */
    const API_URL = 'https://cash.rbc.ru/cash/json/converter_currency_rate';

    /** Источники получения курса валют */
    const SOURCE_CBRF = 'cbrf';
    const SOURCE_FOREX = 'forex';
    const SOURCE_CASH = 'cash';

    /**
     * Получение курса валюты за определенную дату по отношению к локальной
     * @param string $currencyCharCode
     * @param string|null $date
     * @return float
     */
    public static function getExchangeRateToLocalCurrency(string $currencyCharCode, string $date = null): float
    {
        return static::convertCurrency($currencyCharCode, static::getLocalCurrency(), 1, $date, static::getDefaultSource());
    }

    /**
     * Конвертация суммы из одной валюты в другую
     * @param string $currencyFromCharCode
     * @param string $currencyToCharCode
     * @param string|null $date
     * @param float $amount
     * @param string $source
     * @return float
     * @throws \HttpRequestException
     * @throws \InvalidArgumentException
     */
    public static function convertCurrency(string $currencyFromCharCode, string $currencyToCharCode, float $amount = 1, string $date = null, string $source = self::SOURCE_CBRF): float
    {
        $queryParams = static::prepareParams($currencyFromCharCode, $currencyToCharCode, $date, $amount, $source);
        $result = static::query(static::prepareUrlForConverter(), $queryParams);

        if ($result->success) {
            $response = static::prepareResponse($result->response);
            if (empty($response)) {
                throw new \HttpRequestException("Currency service returned empty response.");
            }

            if ($error = static::getErrorFromResponse($response)) {
                throw new \HttpRequestException("Currency service response with error '{$error}'.");
            }

            if (!isset($response['data']) || !isset($response['data']['sum_result'])) {
                throw new \InvalidArgumentException("Could not find any information about currency rates in response from currency service.");
            }
            return floatval($response['data']['sum_result']);
        } else {
            throw new \HttpRequestException("Currency service response with code {$result->httpCode}.");
        }
    }

    /**
     * Преобразование ответа от сервиса в массив
     * @param string $response
     * @return array
     */
    protected static function prepareResponse(string $response): array
    {
        return json_decode($response, true);
    }

    /**
     * Подготовка параметров запроса к АПИ
     * @param string $currencyFromCharCode
     * @param string $currencyToCharCode
     * @param string|null $date
     * @param float $amount
     * @param string $source
     * @return array
     */
    protected static function prepareParams(string $currencyFromCharCode, string $currencyToCharCode, float $amount = 1, string $date = null, string $source = self::SOURCE_CBRF): array
    {
        return [
            'currency_from' => $currencyFromCharCode,
            'currency_to' => $currencyToCharCode,
            'date' => static::prepareDate($date),
            'sum' => $amount,
            'source' => $source,
        ];
    }

    /**
     * Подготовка веб-адреса для запроса
     * @return string
     */
    protected static function prepareUrlForConverter(): string
    {
        return static::API_URL;
    }

    /**
     * Поиск ошибки в ответе от сервиса
     * @param array $response
     * @return string
     */
    protected static function getErrorFromResponse(array $response): string
    {
        /** @TODO Так и не удалось сделать так, чтобы ошибка была в ответе в формате json (всегда вываливается 404 страница). Возможно в дальнейшем пофиксят сие недоразумение, а у нас уже есть данный метод */
        return isset($response['status']) && $response['status'] == 200 ? false : '';
    }

    /**
     * Возвращает чар-код валюты выставленной по умолчанию
     * @return string
     */
    protected static function getLocalCurrency(): string
    {
        /** @TODO По идее тут мы должны получать значение из глобальных конфигов аппы */
        return 'RUR';
    }

    /**
     * Получение источника по умолчанию
     * @return string
     */
    protected static function getDefaultSource(): string
    {
        /** @TODO По идее тут мы должны получать значение из глобальных конфигов аппы */
        return static::SOURCE_CBRF;
    }
}