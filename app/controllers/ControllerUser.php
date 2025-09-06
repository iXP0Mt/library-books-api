<?php

namespace app\controllers;

use app\core\Controller;
use app\core\View;
use app\models\ModelUser;
use app\util\HttpStatus;
use app\util\ShareResult;

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
        if ($result === false) {
            View::renderToJson($output, HttpStatus::SERVER_ERROR);
            return;
        }

        View::renderToJson($output, HttpStatus::OK);
    }

    public function share($granteeUserId): void
    {
        $output = [];
        $isSuccess = $this->model->shareValid($granteeUserId, $output);
        if ($isSuccess === null) {
            View::renderToJson($output, HttpStatus::SERVER_ERROR);
            return;
        } elseif ($isSuccess === false) {
            View::renderToJson($output, HttpStatus::BAD_REQUEST);
            return;
        }

        $shareResult = $this->model->shareAccessToLibrary((int)$granteeUserId, $output);
        $httpStatus = match ($shareResult) {
            ShareResult::GRANTED => HttpStatus::OK,
            ShareResult::ALREADY_GRANTED => HttpStatus::CONFLICT,
            ShareResult::INVALID_USER => HttpStatus::BAD_REQUEST,
            ShareResult::SERVER_ERROR => HttpStatus::SERVER_ERROR,
        };

        View::renderToJson($output, $httpStatus);
    }
}
