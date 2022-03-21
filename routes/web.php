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
$router->post('/login', 'AuthController@login');
$router->post('/register', 'AuthController@register');
$router->get('/email/verify', 'AuthController@verify');
$router->post('/forgot-password', 'AuthController@forgot');
$router->get('/reset-password', 'AuthController@resetpassword');
$router->post('/save-new-password', 'AuthController@saveNewPassword');

$router->post('/eskck/save', 'EskckController@save');
$router->get('/eskck/keperluan-berlaku', 'EskckController@keperluan');
$router->get('/eskck/history', 'EskckController@history');
$router->get('/eskck/detail/{eskck_id}', 'EskckController@detail');

$router->post('/eskck/search', 'EskckController@search');

$router->get('/user/profile', 'UserController@user');

$router->post('/payment', 'PaymentController@payment');
$router->post('/payment/callback', 'PaymentController@payCallback');
$router->post('/payment/fvaUpdate', 'PaymentController@fvaUpdate');

$router->post('/feedback/save', 'FeedbackController@save');
$router->get('/feedback/rating', 'FeedbackController@ratingApp');
$router->post('/feedback/list', 'FeedbackController@list');
