<?php

namespace app\components\integration;

/**
 * Абстрактный класс для сервисов, содержащих информацию о курсе валют
 *
 * Class CurrencyServiceAPI
 * @package app\components\integration
 */
abstract class CurrencyServiceAPI
{
    /**
     * @param string $currencyCharCode ISO-код валюты, для которой запрашивается курс
     * @param string $date Дата в виде строки
     * @return float
     */
    public static abstract function getExchangeRateToLocalCurrency(string $currencyCharCode, string $date = null): float;

    /**
     * Преобразование даты в единый формат
     * @param string $date
     * @return string
     * @throws \InvalidArgumentException
     */
    protected static function prepareDate(string $date): string
    {
        if (!empty($date)) {
            if (!($timestamp = strtotime($date))) {
                throw new \InvalidArgumentException("Parameter date='{$date}' in incorrect format.");
            }
            $date = date('d-m-Y', $timestamp);
        }

        return $date;
    }

    /**
     * Запрос по указанному урл-адресу с передаваемыми GET параметрами
     *
     * @param string $url Адрес запроса
     * @param array $params Параметры для GET запроса
     * @return HttpResponse
     * @throws \HttpRequestException
     */
    protected static function query(string $url, array $params = []): HttpResponse
    {
        $url = $url . '?' . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            throw new \HttpRequestException("Request was crashed with error: {$error}");
        }

        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return new HttpResponse([
            'success' => $responseCode == 200,
            'httpCode' => $responseCode,
            'response' => $response
        ]);
    }
}