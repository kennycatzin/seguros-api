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

// API route group
$router->group(['prefix' => 'api'], function () use ($router) {
    // Matches "/api/register
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');
    $router->get('agentes/{id}', 'AgenteController@singleUser');
    $router->get('agentes', 'AgenteController@allUsers');
    $router->group(['prefix' => 'polizas'], function () use ($router) {
        $router->post('guardar-poliza', 'PolizaController@guardarPoliza');
        $router->get('get-polizas/{id}/{index}', 'PolizaController@getPolizasPorAgente');

        

     });
     $router->group(['prefix' => 'clientes'], function () use ($router) {
        $router->post('guardar-cliente/{idAgente}', 'ClienteController@guardarCliente');
        $router->get('get-clientes/{id}/{index}', 'ClienteController@getClientesPorAgente');
        $router->put('actualizar-cliente', 'ClienteController@actualizarCliente');
        $router->post('eliminar-cliente', 'ClienteController@eliminarCliente');        
    });


 });

$router->get('/', function () use ($router) {
    return $router->app->version();
});
