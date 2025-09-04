<?php

namespace app\controllers;

use app\core\Controller;
use app\core\View;
use app\models\ModelRegistration;
use app\util\HttpStatus;

class ControllerRegistration extends Controller
{
    public function __construct()
    {
        $this->model = new ModelRegistration();
    }

    public function index(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $output = [];

        $isSuccess = $this->model->isValid($input, $output);
        if(!$isSuccess) {
            View::renderToJson($output, HttpStatus::BAD_REQUEST);
            return;
        }

        $isSuccess = $this->model->registration($input, $output);
        if($isSuccess === false) {
            View::renderToJson($output, HttpStatus::CONFLICT);
            return;
        } else if($isSuccess === null) {
            View::renderToJson($output, HttpStatus::SERVER_ERROR);
            return;
        }

        View::renderToJson($output, HttpStatus::CREATED);
    }
}