<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->group("api", function ($routes) {
    $routes->post("account", "Register::index");
    $routes->post("login", "Login::index");
    $routes->get("account/(:segment)", "User::index/$1", ['filter' => 'authFilter']);
    $routes->post("user", "User::editUser", ['filter' => 'authFilter']);
    $routes->get("refreshToken", "Token::refreshToken", ['filter' => 'authFilter']);
    $routes->post("modifyPassword", "User::modifyPassword", ['filter' => 'authFilter']);
});
