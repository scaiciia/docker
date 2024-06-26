<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/dbConection.php';
require __DIR__ . '/src/Validators/validaciones.php';
require __DIR__ . '/src/Models/responseModels.php';
require __DIR__ . '/src/Controllers/localidadesController.php';
require __DIR__ . '/src/Controllers/tiposPropiedadController.php';
require __DIR__ . '/src/Controllers/inquilinosController.php';
require __DIR__ . '/src/Controllers/propiedadesController.php';
require __DIR__ . '/src/Controllers/reservasController.php';


$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);
$app->add( function ($request, $handler) {
    $response = $handler->handle($request);

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'OPTIONS, GET, POST, PUT, PATCH, DELETE')
        ->withHeader('Content-Type', 'application/json')
    ;
});

// ACÁ VAN LOS ENDPOINTS

// Localidades
$app->get('/localidades', function(Request $request, Response $response){
    getLocalidades($request, $response);
    return $response;
});

$app->post('/localidades', function(Request $request, Response $response){
    postLocalidades($request, $response);
    return $response;
});

$app->put('/localidades/{id}', function(Request $request, Response $response, array $args){
    putLocalidades($request, $response, $args);
    return $response;
});

$app->delete('/localidades/{id}', function(Request $request, Response $response, array $args){
    deleteLocalidades($request, $response, $args);
    return $response;
});

// Tipos propiedad
$app->get('/tipos_propiedad', function(Request $request, Response $response){
    getTiposPropiedad($request, $response);
    return $response;
});

$app->post('/tipos_propiedad', function(Request $request, Response $response){
    postTiposPropiedad($request, $response);
    return $response;
});

$app->put('/tipos_propiedad/{id}', function(Request $request, Response $response, array $args){
    putTiposPropiedad($request, $response, $args);
    return $response;
});

$app->delete('/tipos_propiedad/{id}', function(Request $request, Response $response, array $args){
    deleteTiposPropiedad($request, $response, $args);
    return $response;
});

// Inquilinos
$app->get('/inquilinos', function(Request $request, Response $response){
    getInquilinos($request, $response);
    return $response;
});

$app->post('/inquilinos', function(Request $request, Response $response){
    postInquilinos($request, $response);
    return $response;
});

$app->get('/inquilinos/{id}', function(Request $request, Response $response, array $args) {
    getInquilino($request, $response, $args);
    return $response;
});

$app->put('/inquilinos/{id}', function(Request $request, Response $response, array $args){
    putInquilino($request, $response, $args);
    return $response;
});

$app->delete('/inquilinos/{id}', function(Request $request, Response $response, array $args){
    deleteInquilino($request, $response, $args);
    return $response;
});

$app->get('/inquilinos/{id}/reservas', function(Request $request, Response $response, array $args){
    getInquilinoReservas($request, $response, $args);
    return $response;
});


// Propiedades
$app->post('/propiedades', function(Request $request, Response $response){
    postPropiedades($request, $response);
    return $response;
});

$app->put('/propiedades/{id}', function(Request $request, Response $response, array $args){
    putPropiedades($request, $response, $args);
    return $response;
});

$app->delete('/propiedades/{id}', function(Request $request, Response $response, array $args){
    deletePropiedades($request, $response, $args);
    return $response;
});

$app->get('/propiedades', function(Request $request, Response $response, $args){
    getPropiedades($request, $response, $args);
    return $response;
});

$app->get('/propiedades/{id}', function(Request $request, Response $response, array $args){
    getPropiedad($request, $response, $args);
    return $response;
});

// Reserva

$app->post('/reservas', function(Request $request, Response $response){
    postReservas($request, $response);
    return $response;
});

$app->put('/reservas/{id}', function(Request $request, Response $response, array $args){
    putReservas($request, $response, $args);
    return $response;
});

$app->delete('/reservas/{id}', function(Request $request, Response $response, array $args){
    deleteReservas($request, $response, $args);
    return $response;
});

$app->get('/reservas', function(Request $request, Response $response){
    getReservas($request, $response);
    return $response;
});

$app->run();
