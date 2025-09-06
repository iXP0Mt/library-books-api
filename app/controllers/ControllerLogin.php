<?php

namespace app\controllers;

use app\core\Controller;
use app\core\View;
use app\models\ModelLogin;
use app\util\HttpStatus;

class ControllerLogin extends Controller
{
    public function __construct()
    {
        $this->model = new ModelLogin();
    }

    public function index(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $output = [];

        $isSuccess = $this->model->isValid($input, $output);
        if (!$isSuccess) {
            View::renderToJson($output, HttpStatus::BAD_REQUEST);
            return;
        }

        $isSuccess = $this->model->login($input, $output);
        if (!$isSuccess) {
            View::renderToJson($output, HttpStatus::UNAUTHORIZED);
            return;
        }

        View::renderToJson($output, HttpStatus::OK);
    }
}
