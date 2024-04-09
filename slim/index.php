<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/dbConection.php';
require __DIR__ . '/src/localidadesController.php';
require __DIR__ . '/src/tiposPropiedadController.php';

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

$app->get('/localidades', function(Request $request, Response $response){
    getLocalidades($request, $response);
    return $response;
});

$app->post('/localidades', function(Request $request, Response $response){
    postLocalidades($request, $response);
    return $response;
});

$app->get('/tipos_propiedad', function(Request $request, Response $response){
    getTiposPropiedad($request, $response);
    return $response;
});

$app->run();
