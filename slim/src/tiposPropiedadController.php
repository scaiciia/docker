<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function getTiposPropiedad(Request $request, Response $response){

    // Conexion a base de datos
    $pdo = getConnection();

    // Consulta a la base de datos
    $sql = "SELECT * FROM tipo_propiedades";
    $consulta = $pdo->query($sql);
    $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

    // Retorna el resultado en un JSON
    $payload = json_encode([
        'status' => 'success',
        'code' => 200,
        'data' => $resultados
    ]);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
};

function postTiposPropiedad(Request $request, Response $response){

    // Obtiene la informacion
    $data = $request->getParsedBody();

    // Verifica si hay información del campo nombre
    if (!isset($data['nombre'])){
        $payload = json_encode([
            'error' => 'El campo nombre es requerido',
            'code' => '400'
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(400);
    } else {
        try {

            // Conexion a base de datos
            $pdo = getConnection();

            // Obtiene el dato del campo nombre
            $nombre = $data['nombre'];

            // Realiza una consulta a la base de datos para ver si ese dato ya existe.
            $sql = "SELECT * FROM tipo_propiedades WHERE nombre = '" . $nombre . "'";
            $consulta_repetido = $pdo->query($sql);
            if ($consulta_repetido->rowCount() > 0) {
                $payload = json_encode([
                    'error' => 'El campo nombre no debe repetirse',
                    'code' => '400'
                ]);
                $response->getBody()->write($payload);
                return $response->withStatus(400);
            } else {

                // Inserta el nuevo dato en la base de datos
                $sql = "INSERT INTO tipo_propiedades (nombre) VALUES (:nombre)";
                $consulta = $pdo->prepare($sql);
                $consulta->bindValue(':nombre', $nombre);
                $consulta->execute();
                $payload = json_encode([
                    'code' => 201,
                    'message' => 'Tipo de propiedad creado con éxito'
                ]);
                $response->getBody()->write($payload);
                return $response->withStatus(201);
            }
        } catch (\Exception $e) {

            // Se prdujo un error al crear
            $payload = json_encode([
                'code' => '500',
                'error' => $e->getMessage()
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(500);
        }
    }
};

function putTiposPropiedad(Request $request, Response $response, array $args){

    // Obtiene la informacion
    $data = $request->getParsedBody();

    // Verifica si hay información del campo nombre
    if (!isset($data['nombre'])){
        $payload = json_encode([
            'error' => 'El campo nombre es requerido',
            'code' => '400'
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(400);

    } else {
        try {

            // Obtiene la informacion
            $id = $args['id'];

            // Conexion a base de datos
            $pdo = getConnection();

            // Consulta si existe el id
            $sql = "SELECT * FROM tipo_propiedades WHERE id = '" . $id . "'";
            $existe = $pdo->query($sql);
            if ($existe->rowCount() == 0) {
                $payload = json_encode([
                        'error' => 'Not Found',
                        'code' => '404'
                ]);
                $response->getBody()->write($payload);
                return $response->withStatus(404);
            } else {

                // Obtiene el dato
                $nombre = $data['nombre'];

                // Realiza una consulta a la base de datos para ver si ese nombre ya existe.
                $sql = "SELECT * FROM tipo_propiedades WHERE nombre = '" . $nombre . "'";
                $consulta_repetido = $pdo->query($sql);
                if ($consulta_repetido->rowCount() > 0) {
                    $payload = json_encode([
                        'error' => 'El campo nombre no debe repetirse',
                        'code' => '400'
                    ]);
                    $response->getBody()->write($payload);
                    return $response->withStatus(400);
                } else {

                    // Actualiza el nombre
                    $sql = "UPDATE tipo_propiedades SET nombre = (:nombre) WHERE id = (:id)";
                    $consulta = $pdo->prepare($sql);
                    $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
                    $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                    $consulta->execute();
                    $payload = json_encode([
                        'code' => 201,
                        'message' => 'Tipo de propiedad editada con éxito'
                    ]);
                    $response->getBody()->write($payload);
                    return $response->withStatus(201);
                }
            }
        } catch (\Exception $e) {

            //se prdujo un error al editar
            $payload = json_encode([
                'code' => '500',
                'error' => $e->getMessage()
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(500);
        }
    }
};

function deleteTiposPropiedad(Request $request, Response $response, array $args){
    try {

        //obtiene la informacion
        $id = $args['id'];

        // Conexion a base de datos
        $pdo = getConnection();

        // Consulta si existe el id
        $sql = "SELECT * FROM tipo_propiedades WHERE id = '" . $id . "'";
        $existe = $pdo->query($sql);
        if ($existe->rowCount() == 0) {
            $payload = json_encode([
                    'error' => 'Not Found',
                    'code' => '404'
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(404);
        } else {

            // Elimina el dato de la base de datos
            $sql = "DELETE FROM tipo_propiedades WHERE id = (:id)";
            $consulta = $pdo->prepare($sql);
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $payload = json_encode([
                'code' => 201,
                'message' => 'Tipo de propiedad eliminada con éxito'
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(201);
        }
    } catch (\Exception $e) {

        // Se prdujo un error al eliminar
        $payload = json_encode([
            'code' => '500',
            'error' => $e->getMessage()
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(500);
    }
};