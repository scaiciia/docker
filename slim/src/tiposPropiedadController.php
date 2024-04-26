<?php

$tiposPropiedadCamposRequeridos = ['nombre'];
$longitudCampo = array('nombre' => 50);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function getTiposPropiedad(Request $request, Response $response){
    try {
        // Conexion a base de datos
        $pdo = getConnection();

        // Consulta a la base de datos
        $sql = "SELECT * FROM tipo_propiedades";
        $consulta = $pdo->query($sql);
        $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

        // Retorna el resultado en un JSON
        return responseWithData($response, $resultados, 200);
    } catch (\Exception $e) {
        return responseWithError($response, $e, 500);
    }
};

function postTiposPropiedad(Request $request, Response $response){

    // Obtiene la informacion
    $data = $request->getParsedBody();

    // Verifica si hay información del campo nombre
    global $tiposPropiedadCamposRequeridos;
    global $longitudCampo;
    $erroresValidacion = validarCampo($data, $tiposPropiedadCamposRequeridos, $longitudCampo);

    if (!empty($erroresValidacion)){ // Verifica si el campo nombre esta vacio
        return responseWithError($response, $erroresValidacion, 400);
    } else {
        try {

            // Conexion a base de datos
            $pdo = getConnection();

            // Obtiene el dato del campo nombre
            $nombre = $data['nombre'];

            // Realiza una consulta a la base de datos para ver si ese dato ya existe.
            $validarExistentes = array('nombre' => $nombre);

            $erroresExistentes = validarExistenteDB($pdo, 'tipo_propiedades', $validarExistentes);

            if (!empty($erroresExistentes)) { // Verifica si nombre no esta repetido
                return responseWithError($response, $erroresExistentes, 400);
            } else {

                // Inserta el nuevo dato en la base de datos
                $sql = "INSERT INTO tipo_propiedades (nombre) VALUES (:nombre)";
                $consulta = $pdo->prepare($sql);
                $consulta->bindValue(':nombre', $nombre);
                $consulta->execute();
                return responseWithSuccess($response, 'Tipo de propiedad creada con éxito', 201);
            }
        } catch (\Exception $e) {

            // Se prdujo un error al crear
            return responseWithError($response, $e, 500);
        }
    }
};

function putTiposPropiedad(Request $request, Response $response, array $args){

    // Obtiene la informacion
    $data = $request->getParsedBody();

    // Verifica si hay información del campo nombre
    global $tiposPropiedadCamposRequeridos;
    $erroresValidacion = validarCampo($data, $tiposPropiedadCamposRequeridos);

    if (!empty($erroresValidacion)){ // Verifica si el campo nombre esta vacio
        return responseWithError($response, $erroresValidacion, 400);
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
                return responseWithError($response, 'Not Found', 404);
            } else {

                // Obtiene el dato
                $nombre = $data['nombre'];

                // Realiza una consulta a la base de datos para ver si ese nombre ya existe.
                $validarExistentes = array('nombre' => $nombre);

                $erroresExistentes = validarExistenteDB($pdo, 'tipo_propiedades', $validarExistentes);

                if (!empty($erroresExistentes)) {
                    return responseWithError($response, $erroresExistentes, 400);
                } else {

                    // Actualiza el nombre
                    $sql = "UPDATE tipo_propiedades SET nombre = (:nombre) WHERE id = (:id)";
                    $consulta = $pdo->prepare($sql);
                    $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
                    $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                    $consulta->execute();
                    return responseWithSuccess($response, 'Localidad editada con éxito', 201);
                }
            }
        } catch (\Exception $e) {

            //se prdujo un error al editar
            return responseWithError($response, $e, 500);
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
                return responseWithError($response, 'Not Found', 404);
        } else {

            // Elimina el dato de la base de datos
            $sql = "DELETE FROM tipo_propiedades WHERE id = (:id)";
            $consulta = $pdo->prepare($sql);
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            return responseWithSuccess($response, 'Localidad eliminada con éxito', 201);
        }
    } catch (\Exception $e) {

        // Se prdujo un error al eliminar
        return responseWithError($response, $e, 500);
    }
};