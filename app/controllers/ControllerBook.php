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

    public function createBook(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $output = [];

        $data = $this->model->validateCreateBook($input, $output);
        if($data === null) {
            View::renderToJson($output, HttpStatus::BAD_REQUEST);
            return;
        }

        $isSuccess = $this->model->createBook($data, $output);
        if($isSuccess === null) {
            View::renderToJson($output, HttpStatus::SERVER_ERROR);
            return;
        }

        View::renderToJson($output, HttpStatus::OK);
    }

    public function getUsersBook($bookId): void
    {
        $output = [];

        $isSuccess = $this->model->getBookInputValid($bookId, $output);
        if(!$isSuccess) {
            View::renderToJson($output, HttpStatus::BAD_REQUEST);
            return;
        }

        $isSuccess = $this->model->getUsersBookById((int)$bookId, $output);
        if($isSuccess === null) {
            View::renderToJson($output, HttpStatus::SERVER_ERROR);
            return;
        } else if($isSuccess === false) {
            View::renderToJson($output, HttpStatus::NOT_FOUND);
            return;
        }

        View::renderToJson($output, HttpStatus::OK);
    }

    public function saveBook(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $output = [];

        $isSuccess = $this->model->saveBookInputValid($input, $output);
        if($isSuccess === false) {
            View::renderToJson($output, HttpStatus::BAD_REQUEST);
            return;
        }

        $isSuccess = $this->model->saveEditedBook($input, $output);
        if($isSuccess === null) {
            View::renderToJson($output, HttpStatus::SERVER_ERROR);
            return;
        } else if($isSuccess === false) {
            View::renderToJson($output, HttpStatus::BAD_REQUEST);
            return;
        }

        View::renderToJson($output, HttpStatus::OK);
    }
}
