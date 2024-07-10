<?php

$tiposPropiedadCamposRequeridos = ['nombre'];
$longitudCampoTipoPropiedades = array('nombre' => 50);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function getTiposPropiedad(Request $request, Response $response){
    try {
        $pdo = getConnection();

        $sql = "SELECT * FROM tipo_propiedades";
        $consulta = $pdo->query($sql);
        

        if($consulta->rowCount() != 0){
            $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
            return responseWithData($response, $resultados, 200);
        } else {
            return responseWithError($response, 'No se encontraron tipos de propiedades', 404);
        }
    } catch (\Exception $e) {
        return responseWithError($response, $e, 500);
    }
};

function postTiposPropiedad(Request $request, Response $response){

    $data = $request->getParsedBody();

    global $tiposPropiedadCamposRequeridos;
    global $longitudCampoTipoPropiedades;
    $erroresValidacion = validarCampo($data, $tiposPropiedadCamposRequeridos, $longitudCampoTipoPropiedades);

    if (!empty($erroresValidacion)){ 
        return responseWithError($response, $erroresValidacion, 400);
    } else {
        try {

            $pdo = getConnection();

            $nombre = $data['nombre'];

            $validarExistentes = array('nombre' => $nombre);

            $erroresExistentes = validarExistenteDB($pdo, 'tipo_propiedades', $validarExistentes);

            if (!empty($erroresExistentes)) { 
                return responseWithError($response, $erroresExistentes, 400);
            } else {

                $sql = "INSERT INTO tipo_propiedades (nombre) VALUES (:nombre)";
                $consulta = $pdo->prepare($sql);
                $consulta->bindValue(':nombre', $nombre);
                $consulta->execute();
                return responseWithSuccess($response, 'Tipo de propiedad creada con éxito', 201);
            }
        } catch (\Exception $e) {

            return responseWithError($response, $e, 500);
        }
    }
};

function putTiposPropiedad(Request $request, Response $response, array $args){

    $data = $request->getParsedBody();

    global $tiposPropiedadCamposRequeridos;
    global $longitudCampoTipoPropiedades;
    $erroresValidacion = validarCampo($data, $tiposPropiedadCamposRequeridos, $longitudCampoTipoPropiedades);

    if (!empty($erroresValidacion)){ 
        return responseWithError($response, $erroresValidacion, 400);
    } else {
        try {

            $id = $args['id'];
            $error['id'] = validarTipo('id', $id);
            if (!(isset($error['id']))) {
                $pdo = getConnection();

                $sql = "SELECT * FROM tipo_propiedades WHERE id = '" . $id . "'";
                $existe = $pdo->query($sql);
                if ($existe->rowCount() == 0) {
                    return responseWithError($response, 'No se encontró tipo de propiedad', 404);
                } else {

                    $nombre = $data['nombre'];

                    $validarExistentes = array('nombre' => $nombre);
                    $opcional = 'AND id != ' . $id;
                    $erroresExistentes = validarExistenteDB($pdo, 'tipo_propiedades', $validarExistentes, $opcional);

                    if (!empty($erroresExistentes)) {
                        return responseWithError($response, $erroresExistentes, 400);
                    } else {

                        $sql = "UPDATE tipo_propiedades SET nombre = (:nombre) WHERE id = (:id)";
                        $consulta = $pdo->prepare($sql);
                        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
                        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                        $consulta->execute();
                        return responseWithSuccess($response, 'Tipo de propiedad editada con éxito', 201);
                    }
                }
            } else {
                return responseWithError($response, $error, 400);
            }
        } catch (\Exception $e) {

            return responseWithError($response, $e, 500);
        }
    }
};

function deleteTiposPropiedad(Request $request, Response $response, array $args){
    try {

        $id = $args['id'];
        $error['id'] = validarTipo('id', $id);
        if (!(isset($error['id']))) {
            $pdo = getConnection();

            $sql = "SELECT * FROM tipo_propiedades WHERE id = '" . $id . "'";
            $existe = $pdo->query($sql);
            if ($existe->rowCount() == 0) {
                    return responseWithError($response, 'No se encontró tipo de propiedad', 404);
            } else {
                $sql = "SELECT * FROM propiedades WHERE tipo_propiedad_id = '" . $id . "'";
                $resultado = $pdo->query($sql);
                if ($resultado->rowCount() == 0) {
                    $sql = "DELETE FROM tipo_propiedades WHERE id = (:id)";
                    $consulta = $pdo->prepare($sql);
                    $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                    $consulta->execute();
                    $stmt = $pdo->prepare("ALTER TABLE tipo_propiedades AUTO_INCREMENT = 1");
                    $stmt->execute();
                    return responseWithSuccess($response, 'El tipo de propiedad eliminada con éxito', 200);
                } else {
                    return responseWithError($response, 'El tipo de propiedad se esta utilizando en otro registro', 400);
                }
            }
        } else {
            return responseWithError($response, $error, 400);
        }
    } catch (\Exception $e) {

        return responseWithError($response, $e, 500);
    }
};