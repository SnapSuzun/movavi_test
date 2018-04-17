<?php

namespace app\helpers;

/**
 * Доп. класс для работы над массивами
 *
 * Class ArrayHelper
 * @package app\helpers
 */
class ArrayHelper
{
    /**
     * Преобразование массива в формат ключ-значение
     * @param array $array
     * @param string $from
     * @param string $to
     * @return array
     */
    public static function map(array $array, string $from, string $to): array
    {
        $result = [];

        foreach ($array as $item) {
            $result[$item[$from] ?? null] = $item[$to] ?? null;
        }

        return $result;
    }
}