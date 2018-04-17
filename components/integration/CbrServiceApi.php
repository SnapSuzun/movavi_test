<?php

namespace app\components\integration;


use app\helpers\ArrayHelper;
use app\helpers\XmlHelper;

/**
 * Class CbrServiceApi
 * @package app\components\integration
 */
class CbrServiceApi extends CurrencyServiceAPI
{
    /** Веб-адрес сервиса */
    const API_URL = 'http://www.cbr.ru/scripts';

    /** Путь до АПИ для получения ежедневного курса валют */
    const API_DAILY_CURRENCY_RATES_LOCATION = 'XML_daily.asp';


    /**
     * Получение курса валюты за определенную дату по отношению к локальной
     * @param string $currencyCharCode
     * @param string|null $date
     * @return float
     */
    public static function getExchangeRateToLocalCurrency(string $currencyCharCode, string $date = null): float
    {
        $currencyRates = static::dailyCurrencyRates($date);
        return $currencyRates[$currencyCharCode] ?? false;
    }

    /**
     * Получение курсов валют за передаваемую дату в формате "чар-код валюты" => "курс валюты"
     * @param string|null $date
     * @return array
     * @throws \HttpRequestException
     * @throws \InvalidArgumentException
     */
    public static function dailyCurrencyRates(string $date = null): array
    {
        $result = static::query(static::prepareUrlForDailyCurrencyRates(), static::prepareParams($date));
        if ($result->success) {
            $response = static::prepareResponse($result->response);
            if ($error = static::getErrorFromResponse($response)) {
                throw new \HttpRequestException("Currency service response with error '{$error}'.");
            }
            if (!isset($response['Valute'])) {
                throw new \InvalidArgumentException("Could not find any information about currency rates in response from currency service.");
            }
            $currencyRates = ArrayHelper::map(static::prepareRates($response['Valute']), 'CharCode', 'Value');
            return $currencyRates;
        } else {
            throw new \HttpRequestException("Currency service response with code {$result->httpCode}.");
        }
    }

    /**
     * Преобразование ответа от сервиса в удобный формат
     * @param string $response
     * @return array
     */
    protected static function prepareResponse(string $response): array
    {
        $xmlElement = simplexml_load_string($response);
        /** Если пришла одна валюта, то в итоге будет просто объект, а не массив, поэтому приводим к одному типу */
        if (isset($xmlElement->Valute) && $xmlElement->Valute instanceof \SimpleXMLElement) {
            $xmlElement->Valute = array($xmlElement->Valute);
        }
        return XmlHelper::xml2Array($xmlElement);
    }

    /**
     * Подготовка параметров запроса
     * @param string $date
     * @return array
     */
    protected static function prepareParams(string $date): array
    {
        $params = [];
        $params['date_req'] = static::prepareDate($date);
        return $params;
    }

    /**
     * Генерация веб-адреса для получения ежедневных курсов
     * @return string
     */
    protected static function prepareUrlForDailyCurrencyRates(): string
    {
        return rtrim('/', static::API_URL) . '/' . ltrim('/', static::API_DAILY_CURRENCY_RATES_LOCATION);
    }

    /**
     * Поиск ошибки в ответе от сервиса
     * @param array $response
     * @return string
     */
    protected static function getErrorFromResponse(array $response): string
    {
        if (isset($response['Valute'])) {
            return null;
        }

        return $response[0] ?? '';
    }

    /**
     * Приведение курса валют в единый формат и номинал
     * @param array $valutes
     * @return array
     */
    protected static function prepareRates(array $valutes): array
    {
        return array_map(function ($item) {
            if (isset($item['Value'])) {
                /** В качестве разделителя дробной части сервис возвращает запятую, поэтому меняем ее принудительно на точку */
                $item['Value'] = floatval(str_replace([',', ' ', '.'], ['.', '', ''], $item)) / ($item['Nominal'] ?? 1);
            }
            return $item;
        }, $valutes);
    }
}