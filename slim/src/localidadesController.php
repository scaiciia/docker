<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function getLocalidades(Request $request, Response $response){
    $pdo = getConnection();
    $sql = "SELECT * FROM localidades";
    $consulta = $pdo->query($sql);
    $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $payload = json_encode([
        'status' => 'success',
        'code' => 200,
        'data' => $resultados
    ]);
    $response->getBody()->write($payload);
    return $response->withStatus(200);
};

function postLocalidades(Request $request, Response $response){
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
};

function putLocalidades(Request $request, Response $response, array $args){
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
            //obtiene la informacion
            $id = $args['id'];

            $pdo = getConnection();
            $sql = "SELECT * FROM localidades WHERE id = '" . $id . "'";
            $existe = $pdo->query($sql);
            if ($existe->rowCount() == 0) {
                $payload = json_encode([
                        'error' => 'Not Found',
                        'code' => '404'
                ]);
                $response->getBody()->write($payload);
                return $response->withStatus(404);
            } else {
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
                    $sql = "UPDATE localidades SET nombre = (:nombre) WHERE id = (:id)";
                    $consulta = $pdo->prepare($sql);
                    $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
                    $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                    $consulta->execute();
                    $payload = json_encode([
                        'code' => 201,
                        'message' => 'Localidad editada con éxito'
                    ]);
                    $response->getBody()->write($payload);
                    return $response->withStatus(201);
                }
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
};

function deleteLocalidades(Request $request, Response $response, array $args){
    try {
        //obtiene la informacion
        $id = $args['id'];

        $pdo = getConnection();
        $sql = "SELECT * FROM localidades WHERE id = '" . $id . "'";
        $existe = $pdo->query($sql);
        if ($existe->rowCount() == 0) {
            $payload = json_encode([
                    'error' => 'Not Found',
                    'code' => '404'
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(404);
        } else {
            $sql = "DELETE FROM localidades WHERE id = (:id)";
            $consulta = $pdo->prepare($sql);
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $payload = json_encode([
                'code' => 201,
                'message' => 'Localidad eliminada con éxito'
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
};