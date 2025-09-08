<?php

namespace app\core;

use app\util\HttpStatus;

abstract class View
{
    public static function renderToJson($output, HttpStatus $statusCode): void
    {
        http_response_code($statusCode->value);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($output, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
