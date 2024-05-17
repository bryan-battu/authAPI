<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->group("api", function ($routes) {
    $routes->post("register", "Register::index");
    $routes->post("login", "Login::index");
    $routes->get("user", "User::index", ['filter' => 'authFilter']);
    $routes->put("user", "User::editUser", ['filter' => 'authFilter']);
    $routes->get("refreshToken", "Token::refreshToken", ['filter' => 'authFilter']);
});
