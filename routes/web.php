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

// $router->get('/', function () use ($router) {
//     echo "working";
// });


$router->group(['prefix'=>'api'],function() use ($router){
    $router->get('list/{count}/{user_id}/{type}','PollController@list');
    $router->get('find/{id}','PollController@find');
    $router->get('delete/{id}','PollController@delete');

    $router->post('/save','PollController@save');
    $router->post('/like','PollController@like');
    $router->post('/comment','PollController@comment');
    $router->post('/user/option','PollController@user_option');
});
