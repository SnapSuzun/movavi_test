<?php

namespace app\components\http;

/**
 * Ответ от внешего сервиса
 *
 * Class HttpResponse
 * @package app\components\integration
 */
class HttpResponse
{
    /**
     * @var bool
     */
    public $success = false;
    /**
     * @var int
     */
    public $httpCode = null;
    /**
     * @var string
     */
    public $response = null;

    /**
     * HttpResponse constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
    }
}