<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->post('/login','AuthController@login');
$router->post('/register','AuthController@register');
$router->get('/email/verify','AuthController@verify');

$router->post('/feedback/save','FeedbackController@save');
$router->post('/eskck/save','EskckController@save');
$router->post('/eskck/history','EskckController@history');

$router->post('/user/profile','UserController@user');

$router->post('/payment','PaymentController@payment');
$router->post('/payment/callback','PaymentController@payCallback');