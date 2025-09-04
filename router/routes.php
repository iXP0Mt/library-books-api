<?php

$router = new Bramus\Router\Router();

$router->setNamespace('\app\controllers');

$router->mount('/api/v1', function() use ($router) {
    $router->post("/auth/register", 'ControllerRegistration@index');
    $router->post("/auth/login", 'ControllerLogin@index');

    $router->get("/users", 'ControllerUser@listUsers');
});

$router->run();