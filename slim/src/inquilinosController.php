<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function getInquilinos(Request $request, Response $response){

    // Conexion a base de datos
    $pdo = getConnection();

    // Consulta a la base de datos
    $sql = "SELECT * FROM inquilinos";
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

function postInquilinos(Request $request, Response $response){

    // Obtiene la informacion
    $data = $request->getParsedBody();

    if (!isset($data['nombre'])){ // Verifica si el campo nombre esta vacio
        $payload = json_encode([
            'error' => 'El campo nombre es requerido',
            'code' => 400
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(400);
    } elseif (!isset($data['apellido'])) { // Verifica si el campo apellido esta vacio
        $payload = json_encode([
            'error' => 'El campo apellido es requerido',
            'code' => 400
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(400);
    } elseif (!isset($data['documento'])) { // Verifica si el campo documento esta vacio
        $payload = json_encode([
            'error' => 'El campo documento es requerido',
            'code' => 400
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(400);
    } elseif (!isset($data['email'])) { // Verifica si el campo email esta vacio
        $payload = json_encode([
            'error' => 'El campo email es requerido',
            'code' => 400
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(400);
    }  elseif (!isset($data['activo'])) { // Verifica si el campo activo se encuentra vacio
        $payload = json_encode([
            'error' => 'El campo activo es requerido',
            'code' => 400
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(400);
    } else {
        try {
            // Conexion a base de datos
            $pdo = getConnection();

            // Recupera el dato email y verifica que no este repetido en la base de datos
            $email = $data['email'];
            $sql = "SELECT * FROM inquilinos WHERE email = (:email)";
            $consulta = $pdo->prepare($sql);
            $consulta->bindValue(':email', $email);
            $consulta->execute();
            if ($consulta->rowCount() > 0) {
                $payload = json_encode([
                    'error' => 'El campo email no debe repetirse',
                    'code' => 400
                ]);
                $response->getBody()->write($payload);
                return $response->withStatus(400);
            } else {

                // Recupera el dato documento y verifica que no este repetido en la base de datos
                $documento = $data['documento'];
                $sql = "SELECT * FROM inquilinos WHERE documento = (:documento)";
                $consulta = $pdo->prepare($sql);
                $consulta->bindValue(':documento', $documento, PDO::PARAM_STR);
                $consulta->execute();
                if ($consulta->rowCount() > 0) {
                    $payload = json_encode([
                        'error' => 'El campo documento no debe repetirse',
                        'code' => 400
                    ]);
                    $response->getBody()->write($payload);
                    return $response->withStatus(400);
                } else {

                    // Recupera los datos nombre, apellido y activo e inserta el nuevo inquilino a la base de datos
                    $nombre = $data['nombre'];
                    $apellido = $data['apellido'];
                    $activo = $data['activo'];
                    $sql = "INSERT INTO inquilinos (nombre, apellido, documento, email, activo) VALUES (:nombre, :apellido, :documento, :email, :activo)";
                    $consulta = $pdo->prepare($sql);
                    $consulta->bindValue(':nombre', $nombre);
                    $consulta->bindValue(':apellido', $apellido);
                    $consulta->bindValue(':documento', $documento);
                    $consulta->bindValue(':email', $email);
                    $consulta->bindValue(':activo', $activo);
                    $consulta->execute();
                    $payload = json_encode([
                        'code' => 201,
                        'message' => 'Inquilino creado con éxito'
                    ]);
                    $response->getBody()->write($payload);
                    return $response->withStatus(201);
                }
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

function getInquilino(Request $request, Response $response, array $args){
    try {

        //obtiene la informacion
        $id = $args['id'];

        // Conexion a base de datos
        $pdo = getConnection();

        // Consulta si existe el id
        $sql = "SELECT * FROM inquilinos WHERE id = '" . $id . "'";
        $consulta = $pdo->query($sql);
        if ($consulta->rowCount() == 0) {
            $payload = json_encode([
                    'error' => 'Not Found',
                    'code' => 404
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(404);
        } else {
            $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

            // Retorna el resultado en un JSON
            $payload = json_encode([
                'status' => 'success',
                'code' => 200,
                'data' => $resultados
            ]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
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
};