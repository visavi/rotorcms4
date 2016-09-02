<?php

$router = new AltoRouter();

$router->addMatchTypes(['user' => '[0-9A-Za-z-_]++']);

$router->map( 'GET', '/', function() {
    App::view('index');
});

$router->map('GET', '/book', '/modules/book/index.php', 'book');
$router->map('POST', '/book/[complaint|add:action]', '/modules/book/index.php');
$router->map('GET|POST', '/book/[edit:action]/[i:id]', '/modules/book/index.php');

$router->map('GET', '/login', '/modules/pages/login.php', 'login');
$router->map('GET', '/register', '/modules/pages/registration.php', 'register');
$router->map('GET', '/logout', '/modules/pages/logout.php', 'logout');

Registry::set('router', $router->match());