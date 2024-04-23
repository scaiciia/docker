<?php

$localidadesCamposRequeridos = ['nombre'];

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function getLocalidades(Request $request, Response $response){
    try {
          // Conexion a base de datos
          $pdo = getConnection();

          // Consulta a la base de datos
          $sql = "SELECT * FROM localidades ORDER BY id" ;
          $consulta = $pdo->query($sql);
          $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

          // Retorna el resultado en un JSON
          if(isset($resultados) && is_array($resultados) && !empty($resultados)){
              return responseWithData($response, $resultados, 200);
          } else {
              $payload = json_encode([
                    'status' => 'failed',
                    'code' => 400,
                    'error' => 'No hay localidades en la base'
              ]);
          }
    } catch (\Exception $e) {
        return responseWithError($response, $e, 500);
    }
};

function postLocalidades(Request $request, Response $response){

    // Obtiene la informacion
    $data = $request->getParsedBody();

    global $localidadesCamposRequeridos;
    $erroresValidacion = validarCampoVacio($data, $localidadesCamposRequeridos);

    if (!empty($erroresValidacion)){ // Verifica si el campo nombre esta vacio
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
    $erroresValidacion = validarCampoVacio($data, $localidadesCamposRequeridos);

    if (!empty($erroresValidacion)){ // Verifica si el campo nombre esta vacio
        return responseWithError($response, $erroresValidacion, 400);
    } else {
        try {

            // Obtiene la informacion
                $id = $args['id'];

            // Conexion a base de datos
            $pdo = getConnection();

            // Consulta si existe el id
            $sql = "SELECT * FROM localidades WHERE id = '" . $id . "'";
            $existe = $pdo->query($sql);
            if ($existe->rowCount() == 0) {
                return responseWithError($response, 'Not Found', 404);
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

        // Conexion a base de datos
        $pdo = getConnection();

        // Consulta si existe el id
        $sql = "SELECT * FROM localidades WHERE id = '" . $id . "'";
        $existe = $pdo->query($sql);
        if ($existe->rowCount() == 0) {
                return responseWithError($response, 'Not Found', 404);
        } else {

            // Elimina el dato de la base de datos
            $sql = "DELETE FROM localidades WHERE id = (:id)";
            $consulta = $pdo->prepare($sql);
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            return responseWithSuccess($response, 'Localidad eliminada con éxito', 201);
        }
    } catch (\Exception $e) {

        //se prdujo un error al eliminar
        return responseWithError($response, $e, 500);
    }
};