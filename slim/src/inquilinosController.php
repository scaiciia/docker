<?php

$campoRequerido = ['nombre', 'apellido', 'documento', 'email', 'activo'];

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function getInquilinos(Request $request, Response $response){
    try {
        // Conexion a base de datos
        $pdo = getConnection();

        // Consulta a la base de datos
        $sql = "SELECT * FROM inquilinos";
        $consulta = $pdo->query($sql);
        $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

        // Retorna el resultado en un JSON
        return responseWithData($response, $resultados, 200);
    } catch (\Exception $e) {
        return responseWithError($response, $e, 500);
    }
};

function postInquilinos(Request $request, Response $response){
    
    // Obtiene la informacion
    $data = $request->getParsedBody();

    global $campoRequerido;
    $erroresValidacion = validarCampoVacio($data, $campoRequerido);

    if (!empty($erroresValidacion)){ // Verifica si el campo nombre esta vacio
        return responseWithError($response, $erroresValidacion, 400);
    } else {
        try {
            // Conexion a base de datos
            $pdo = getConnection();

            $email = $data['email'];
            $documento = $data['documento'];

            $validarExistentes = array('email' => $email, 'documento' => $documento);

            $erroresExistentes = validarExistenteDB($pdo, $validarExistentes);

            if (!empty($erroresExistentes)) { // Verifica si email y documento no estan repetidos
                return responseWithError($response, $erroresExistentes, 400);
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
                return responseWithSuccess($response, 'Inquilino creado con éxito', 201);
            }  
        } catch (\Exception $e) {

            // Se prdujo un error al crear
            return responseWithError($response, $e, 500);
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
            return responseWithError($response, 'Not Found', 404);
        } else {
            $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

            // Retorna el resultado en un JSON
            return responseWithData($response, $resultados, 200);
        }
    } catch (\Exception $e) {

        // Se prdujo un error al crear
        return responseWithError($response, $e, 500);
    }
};

function putInquilino(Request $request, Response $response, array $args) {
    // Obtiene la informacion
    $data = $request->getParsedBody();

    global $campoRequerido;
    $erroresValidacion = validarCampoVacio($data, $campoRequerido);

    if (!empty($erroresValidacion)){ // Verifica si el campo nombre esta vacio
        return responseWithError($response, $erroresValidacion, 400);
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
                return responseWithError($response, 'Not Found', 404);
            } else {
                // Recupera el dato email
                $opcional = "AND id != '". $id . "'";
                $email = $data['email'];
                $documento = $data['documento'];

                $validarExistentes = array('email' => $email, 'documento' => $documento);

                $erroresExistentes = validarExistenteDB($pdo, $validarExistentes, $opcional);

                if (!empty($erroresExistentes)) { // Verifica si email y documento no estan repetidos
                    return responseWithError($response, $erroresExistentes, 400);
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
                        return responseWithSuccess($response, 'Inquilino modificado con éxito', 201);
                }
            }
        } catch (\Exception $e) {
            // Se prdujo un error al crear
            return responseWithError($response, $e, 500);
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
            return responseWithError($response, 'Not Found', 404);
        } else {
            // Elimina el dato de la base de datos
            $sql = "DELETE FROM inquilinos WHERE id = (:id)";
            $consulta = $pdo->prepare($sql);
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            return responseWithSuccess($response, 'Inquilino eliminado con éxito', 201);
        }
    } catch (\Exception $e) {

        // Se prdujo un error al eliminar
        return responseWithError($response, $e, 500);
    }
};

function getInquilinoReservas(Request $request, Response $response, array $args){
    try {
        //obtiene la informacion
        $id = $args['id'];

        // Conexion a base de datos
        $pdo = getConnection();
        // Consulta si existe el id
        $sql = "SELECT * FROM inquilinos WHERE id = '" . $id . "'";
        $consulta = $pdo->query($sql);
        if ($consulta->rowCount() == 0) {
            return responseWithError($response, 'Not Found', 404);
        } else {
            $sql = "SELECT * FROM reservas WHERE inquilino_id = ?";
            $consulta = $pdo->prepare($sql);
            $consulta->execute([$id]);
            $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

            while ($dato = $resultados) {
                $propiedad_id = $dato['propiedad_id'];
                $sql = "SELECT * FROM propiedades WHERE id = ?";
                $consulta = $pdo->prepare($sql);
                $consulta->execute([$propiedad_id]);
                $dato['propiedad_id'] = $consulta->fetchAll(PDO::FETCH_ASSOC);
                $localidad_id = $dato['propiedad_id']['localidad_id'];
                $sql = "SELECT * FROM localidades WHERE id = ?";
                $consulta = $pdo->prepare($sql);
                $consulta->execute([$localidad_id]);
                $dato['propiedad_id']['localidad_id'] = $consulta->fetchAll(PDO::FETCH_ASSOC);
                $tipo_propiedad_id = $dato['propiedad_id']['tipo_propiedad_id'];
                $sql = "SELECT * FROM tipo_propiedades WHERE id = ?";
                $consulta = $pdo->prepare($sql);
                $consulta->execute([$tipo_propiedad_id]);
                $dato['propiedad_id']['tipo_propiedad_id'] = $consulta->fetchAll(PDO::FETCH_ASSOC);
            }
            // Retorna el resultado en un JSON
            return responseWithData($response, $resultados, 200);
        }
    } catch (\Exception $e) {
        // Se prdujo un error al eliminar
        return responseWithError($response, $e, 500);
    }
};

