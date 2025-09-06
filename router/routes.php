<?php

$router = new Bramus\Router\Router();

$router->setNamespace('\app\controllers');

$router->mount('/api/v1', function() use ($router) {
    $router->post("/auth/register", 'ControllerRegistration@index');
    $router->post("/auth/login", 'ControllerLogin@index');

    $router->before("GET|POST|PUT|DELETE", "/(?!auth).*", 'ControllerMiddleware@index');

    $router->get("/users", 'ControllerUser@listUsers');
    $router->post("/users/{granteeUserId}/share", 'ControllerUser@share');

    $router->get("/user/books", 'ControllerBook@userBooks');
    $router->post("/user/books/create", 'ControllerBook@createBook');
    $router->get("/books/{bookId}", 'ControllerBook@getUsersBook');
    $router->put("/user/books/save", 'ControllerBook@saveBook');

    $router->get("/external/search", 'ControllerExternalBooks@search');
    $router->post("/external/save", 'ControllerExternalBooks@save');
});

$router->run();