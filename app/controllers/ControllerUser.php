<?php

namespace app\controllers;

use app\core\Controller;
use app\core\View;
use app\models\ModelUser;
use app\util\HttpStatus;

class ControllerUser extends Controller
{
    public function __construct()
    {
        $this->model = new ModelUser();
    }

    public function listUsers(): void
    {
        $output = [];
        $result = $this->model->getListUsers($output);
        if($result === false) {
            View::renderToJson($output, HttpStatus::SERVER_ERROR);
            return;
        }

        View::renderToJson($output, HttpStatus::OK);
    }
}