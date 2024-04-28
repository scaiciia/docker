<?php

$localidadesCamposRequeridos = ['nombre'];
$longCampoLocalidades = array('nombre' => 50);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function getLocalidades(Request $request, Response $response){
    try {
            // Conexion a base de datos
            $pdo = getConnection();

            // Consulta a la base de datos
            $sql = "SELECT * FROM localidades ORDER BY id" ;
            $consulta = $pdo->query($sql);

            // Retorna el resultado en un JSON
            if($consulta->rowCount() != 0){
                $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
                return responseWithData($response, $resultados, 200);
            } else {
                return responseWithError($response, 'No se encontraron localidades', 404);
            }
    } catch (\Exception $e) {
        return responseWithError($response, $e, 500);
    }
};

function postLocalidades(Request $request, Response $response){

    // Obtiene la informacion
    $data = $request->getParsedBody();
    
    global $localidadesCamposRequeridos;
    global $longCampoLocalidades;
    $erroresValidacion = validarCampo($data, $localidadesCamposRequeridos, $longCampoLocalidades);
    if (!empty($erroresValidacion)){ // Verificacion errores
        return responseWithError($response, $erroresValidacion, 400);
    } else {
        try {

            // Conexion a base de datos
            $pdo = getConnection();

            // Obtiene el dato del campo nombre
            $nombre = $data['nombre'];

            $validarExistentes = array('nombre' => $nombre);

            $erroresExistentes = validarExistenteDB($pdo, 'localidades', $validarExistentes);

            if (!empty($erroresExistentes)) { // Verifica si nombre no esta repetido
                return responseWithError($response, $erroresExistentes, 400);
            } else {

                // Inserta el nuevo dato en la base de datos
                $sql = "INSERT INTO localidades (nombre) VALUES (:nombre)";
                $consulta = $pdo->prepare($sql);
                $consulta->bindValue(':nombre', $nombre);
                $consulta->execute();
                return responseWithSuccess($response, 'Localidad creado con éxito', 201);
            }
        } catch (\Exception $e) {

            // Se prdujo un error al crear
            return responseWithError($response, $e, 500);
        }
    }
};

function putLocalidades(Request $request, Response $response, array $args){

    // Obtiene la informacion
    $data = $request->getParsedBody();
    
    // Verifica si hay información del campo nombre
    global $localidadesCamposRequeridos;
    global $longCampoLocalidades;
    $erroresValidacion = validarCampo($data, $localidadesCamposRequeridos, $longCampoLocalidades);

    if (!empty($erroresValidacion)){ // Verifica si el campo nombre esta vacio
        return responseWithError($response, $erroresValidacion, 400);
    } else {
        try {

            // Obtiene la informacion
            $id = $args['id'];
            $error['id'] = validarTipo('id', $id);
            if (!(isset($error['id']))) {
                // Conexion a base de datos
                $pdo = getConnection();

                // Consulta si existe el id
                $sql = "SELECT * FROM localidades WHERE id = '" . $id . "'";
                $existe = $pdo->query($sql);
                if ($existe->rowCount() == 0) {
                    return responseWithError($response, 'No se encontró localidad', 404);
                } else {

                    // Obtiene el dato
                    $nombre = $data['nombre'];

                    // Realiza una consulta a la base de datos para ver si ese nombre ya existe.
                    $validarExistentes = array('nombre' => $nombre);

                    $erroresExistentes = validarExistenteDB($pdo, 'localidades', $validarExistentes);

                    if (!empty($erroresExistentes)) {
                        return responseWithError($response, $erroresExistentes, 400);
                    } else {

                        // Actualiza el nombre
                        $sql = "UPDATE localidades SET nombre = (:nombre) WHERE id = (:id)";
                        $consulta = $pdo->prepare($sql);
                        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
                        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                        $consulta->execute();
                        return responseWithSuccess($response, 'Localidad editada con éxito', 201);
                    }
                }
            } else {
                return responseWithError($response, $error, 400);
            }
        } catch (\Exception $e) {
            // Se prdujo un error al editar
            return responseWithError($response, $e, 500);
        }
    }
};

function deleteLocalidades(Request $request, Response $response, array $args){
    try {

        //obtiene la informacion
        $id = $args['id'];
        $error['id'] = validarTipo('id', $id);
        if (!(isset($error['id']))) {
            // Conexion a base de datos
            $pdo = getConnection();

            // Consulta si existe el id
            $sql = "SELECT * FROM localidades WHERE id = '" . $id . "'";
            $existe = $pdo->query($sql);
            if ($existe->rowCount() == 0) {
                    return responseWithError($response, 'No se encontró localidad', 404);
            } else {
                $sql = "SELECT * FROM propiedades WHERE localidad_id = '" . $id . "'";
                $resultado = $pdo->query($sql);
                if ($resultado->rowCount() == 0) {
                    // Elimina el dato de la base de datos
                    $sql = "DELETE FROM localidades WHERE id = (:id)";
                    $consulta = $pdo->prepare($sql);
                    $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                    $consulta->execute();
                    $stmt = $pdo->prepare("ALTER TABLE localidades AUTO_INCREMENT = 1");
                    $stmt->execute();
                    return responseWithSuccess($response, 'Localidad eliminada con éxito', 201);
                } else {
                    return responseWithError($response, 'La localidad se esta utilizando en otro registro', 400);
                }
            }
        } else {
            return responseWithError($response, $error, 400);
        }
    } catch (\Exception $e) {

        //se prdujo un error al eliminar
        return responseWithError($response, $e, 500);
    }
};