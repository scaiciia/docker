<?php

//NO FUNCIONA POR EL MOMENTO. IGNORAR

namespace src\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/vendor/autoload.php';

class LocalidadesController {

    function getConnection() {
        $dbhost = "db";
        $dbname = "seminariophp";
        $dbuser = "seminariophp";
        $dbpass = "seminariophp";
        $connection = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $connection;
    }

    Public static function Listar(Request $request, Response $response) {
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
        return $response;
    }
}