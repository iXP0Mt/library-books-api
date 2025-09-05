<?php

namespace app\controllers;

use app\core\Controller;
use app\core\View;
use app\models\ModelMiddleware;
use app\util\HttpStatus;

class ControllerMiddleware extends Controller
{
    public function __construct()
    {
        $this->model = new ModelMiddleware();
    }

    public function index(): void
    {
        $output = [];
        $isSuccess = $this->model->checkAccess($output);
        if(!$isSuccess) {
            View::renderToJson($output, HttpStatus::UNAUTHORIZED);
            return;
        }
    }
}