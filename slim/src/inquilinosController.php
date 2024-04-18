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

            // Recupera el dato email
            $email = $data['email'];
            $sql = "SELECT * FROM inquilinos WHERE email = (:email)";
            $consulta_email = $pdo->prepare($sql);
            $consulta_email->bindValue(':email', $email);
            $consulta_email->execute();

            // Recupera el dato documento
            $documento = $data['documento'];
            $sql = "SELECT * FROM inquilinos WHERE documento = (:documento)";
            $consulta_dni = $pdo->prepare($sql);
            $consulta_dni->bindValue(':documento', $documento, PDO::PARAM_STR);
            $consulta_dni->execute();

            if (($consulta_dni->rowCount() > 0) and ($consulta_email->rowCount() > 0)) { // Verifica si email y documento no estan repetidos
                $payload = json_encode([
                    'error' => 'El campo documento ni email no debe repetirse',
                    'code' => 400
                ]);
                $response->getBody()->write($payload);
                return $response->withStatus(400);
            } elseif ($consulta_dni->rowCount() > 0) { // Verifica si documento no esta repetido
                $payload = json_encode([
                    'error' => 'El campo documento no debe repetirse',
                    'code' => 400
                ]);
                $response->getBody()->write($payload);
                return $response->withStatus(400);
            } elseif ($consulta_email->rowCount() > 0) { // Verifica si email no esta repetido
                $payload = json_encode([
                    'error' => 'El campo email no debe repetirse',
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

function putInquilino(Request $request, Response $response, array $args) {
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
                // Recupera el dato email
                $email = $data['email'];
                $sql = "SELECT * FROM inquilinos WHERE email = (:email) AND id != '". $id . "'";
                $consulta_email = $pdo->prepare($sql);
                $consulta_email->bindValue(':email', $email, PDO::PARAM_STR);
                $consulta_email->execute();

                // Recupera el dato documento
                $documento = $data['documento'];
                $sql = "SELECT * FROM inquilinos WHERE documento = (:documento) AND id != '". $id . "'";
                $consulta_dni = $pdo->prepare($sql);
                $consulta_dni->bindValue(':documento', $documento, PDO::PARAM_STR);
                $consulta_dni->execute();

                if (($consulta_dni->rowCount() > 0) and ($consulta_email->rowCount() > 0)) { // Verifica si email y documento no estan repetidos
                $payload = json_encode([
                    'error' => 'Los campos documento y email son utilizados por otro inquilino',
                    'code' => 400
                ]);
                $response->getBody()->write($payload);
                return $response->withStatus(400);
                } elseif ($consulta_dni->rowCount() > 0) { // Verifica si documento no esta repetido
                    $payload = json_encode([
                        'error' => 'El campo documento es utilizado por otro inquilino',
                        'code' => 400
                    ]);
                    $response->getBody()->write($payload);
                    return $response->withStatus(400);
                } elseif ($consulta_email->rowCount() > 0) { // Verifica si email no esta repetido
                    $payload = json_encode([
                        'error' => 'El campo email es utilizado por otro inquilino',
                        'code' => 400
                    ]);
                    $response->getBody()->write($payload);
                    return $response->withStatus(400);
                } else {
                        $nombre = $data['nombre'];
                        $apellido = $data['apellido'];
                        $activo = $data['activo'];
                        $sql = "UPDATE inquilinos SET documento = (:documento), email = (:email), nombre = (:nombre), apellido = (:apellido), activo = (:activo) WHERE id = '". $id . "'";
                        $consulta = $pdo->prepare($sql);
                        $consulta->bindValue(':documento', $documento);
                        $consulta->bindValue(':email', $email);
                        $consulta->bindValue(':nombre', $nombre);
                        $consulta->bindValue(':apellido', $apellido);
                        $consulta->bindValue(':activo', $activo);
                        $consulta->execute();
                        $payload = json_encode([
                            'code' => 201,
                            'message' => 'Inquilino modificado con éxito'
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

function deleteInquilino(Request $request, Response $response, array $args){
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
            // Elimina el dato de la base de datos
            $sql = "DELETE FROM inquilinos WHERE id = (:id)";
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