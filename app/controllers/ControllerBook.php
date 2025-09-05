<?php

namespace app\controllers;

use app\core\Controller;
use app\core\View;
use app\models\ModelBook;
use app\util\HttpStatus;

class ControllerBook extends Controller
{
    public function __construct()
    {
        $this->model = new ModelBook();
    }

    public function userBooks(): void
    {
        $output = [];
        $isSuccess = $this->model->validUserBooks($output);
        if(!$isSuccess) {
            View::renderToJson($output, HttpStatus::SERVER_ERROR);
            return;
        }

        $isSuccess = $this->model->getUserBooks($output);
        if(!$isSuccess) {
            View::renderToJson($output, HttpStatus::SERVER_ERROR);
            return;
        }

        View::renderToJson($output, HttpStatus::OK);
    }
}
