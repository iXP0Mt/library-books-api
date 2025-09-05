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
}