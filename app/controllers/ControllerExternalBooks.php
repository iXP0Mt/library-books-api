<?php

namespace app\controllers;

use app\core\Controller;
use app\core\View;
use app\models\ModelExternalBooks;
use app\util\HttpStatus;

class ControllerExternalBooks extends Controller
{
    public function __construct()
    {
        $this->model = new ModelExternalBooks();
    }

    public function search(): void
    {
        $output = [];
        $isSuccess = $this->model->isValid($output);
        if(!$isSuccess) {
            View::renderToJson($output, HttpStatus::BAD_REQUEST);
            return;
        }

        $this->model->externalSearch($this->model->getSearchQuery(), $output);
        View::renderToJson($output, HttpStatus::OK);
    }

    public function save(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $output = [];

        $isSuccess = $this->model->saveInputValid($input, $output);
        if(!$isSuccess) {
            View::renderToJson($output, HttpStatus::BAD_REQUEST);
            return;
        }

        $isSuccess = $this->model->saveExternalBook($input, $output);
        if(!$isSuccess) {
            View::renderToJson($output, HttpStatus::BAD_REQUEST);
            return;
        }

        View::renderToJson($output, HttpStatus::OK);
    }
}