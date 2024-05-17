<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->group("api", function ($routes) {
    $routes->post("account", "Register::index");
    $routes->get("account/(:segment)", "User::index/$1", ['filter' => 'authFilter']);
    $routes->post("user", "User::editUser", ['filter' => 'authFilter']);
    $routes->post("token", "Token::index");
    $routes->get("validate/(:segment)", "Token::validate/$1");
    $routes->post("refresh-token/(:segment)/token", "Token::refreshToken/$1");
});
