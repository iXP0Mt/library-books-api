<?php

$router = new Bramus\Router\Router();

$router->setNamespace('\app\controllers');

$router->mount('/api/v1', function() use ($router) {

});

$router->run();