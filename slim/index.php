<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

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

//CONEXION A DB

function getConnection() {
    $dbhost = "db";
    $dbname = "seminariophp";
    $dbuser = "seminariophp";
    $dbpass = "seminariophp";
    $connection = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $connection;
}

// ACÁ VAN LOS ENDPOINTS

$app->get('/localidades', function(Request $request, Response $response){
    $pdo = getConnection();
    $sql = "SELECT nombre FROM localidades";
    $consulta = $pdo->query($sql);
    $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $payload = json_encode([
        'status' => 'success',
        'code' => 200,
        'data' => $resultados
    ]);
    $response->getBody()->write($payload);
    return $response->withStatus(200);
});

$app->post('/localidades', function(Request $request, Response $response){
    //obtiene la informacion
    $data = $request->getParsedBody();
    //verifica si hay información del campo nombre
    if (!isset($data['nombre'])){
        $payload = json_encode([
            'error' => 'El campo nombre es requerido',
            'code' => '400'
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(400);
    } else {
        try {
            //conexion a base de datos
            $pdo = getConnection();
            //obtiene el dato del campo nombre
            $nombre = $data['nombre'];
            //realiza una consulta a la base de datos para ver si ese dato ya existe.
            $sql = "SELECT * FROM localidades WHERE nombre = '" . $nombre . "'";
            $consulta_repetido = $pdo->query($sql);
            if ($consulta_repetido->rowCount() > 0) {
                $payload = json_encode([
                    'error' => 'El campo nombre no debe repetirse',
                    'code' => '400'
                ]);
                $response->getBody()->write($payload);
                return $response->withStatus(400);
            } else {
                $sql = "INSERT INTO localidades (nombre) VALUES (:nombre)";
                $consulta = $pdo->prepare($sql);
                $consulta->bindValue(':nombre', $nombre);
                $consulta->execute();
                $payload = json_encode([
                    'code' => 201,
                    'message' => 'Localidad creado con éxito'
                ]);
                $response->getBody()->write($payload);
                return $response->withStatus(201);
            }
        } catch (\Exception $e) {
            //se prdujo un error al crear
            $payload = json_encode([
                'code' => '500',
                'error' => $e->getMessage()
            ]);
            $response = getBody()->write($payload);
            return $response->withStatus(500);
        }
    }
});

$app->get('/tipos_propiedad', function(Request $request, Response $response){
    $pdo = getConnection();
    $sql = "SELECT nombre FROM tipo_propiedades";
    $consulta = $pdo->query($sql);
    $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $payload = json_encode([
        'status' => 'success',
        'code' => 200,
        'data' => $resultados
    ]);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});



$app->run();
