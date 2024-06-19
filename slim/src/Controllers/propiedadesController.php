<?php

$longCampoPropiedades = array('domicilio' => 225, 'tipo_imagen' => 50);
$propiedadesCamposRequeridos = ['domicilio', 'localidad_id', 'cantidad_huespedes', 'fecha_inicio_disponibilidad', 'cantidad_dias', 'disponible', 'valor_noche', 'tipo_propiedad_id'];

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// function getPropiedades(Request $request, Response $response)
// {

//     $pdo = getConnection();

//     $sql = "SELECT * FROM propiedades
//     ORDER BY disponible DESC, localidad_id ASC, fecha_inicio_disponibilidad ASC, cantidad_huespedes ASC;";
//     // filtrar por query params
//     $consulta = $pdo->query($sql);
//     $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

//     if (isset($resultados) && is_array($resultados) && !empty($resultados)) {
//         responseWithSuccess($response, $resultados, 200);
//     } else {
//         responseWithError($response, 'No hay propiedades en la base', 400);
//     }
//     return $response;
// }

function getPropiedades(Request $request, Response $response)
{
    $pdo = getConnection();

    $params = $request->getQueryParams();

    // Construir la consulta SQL base
    $sql = "SELECT * FROM propiedades WHERE 1=1";
    // Agregar filtros si están presentes en los parámetros de consulta
    if (isset($params['disponible'])) {
        $sql .= " AND disponible = :disponible";
    }
    if (isset($params['localidad_id'])) {
        $sql .= " AND localidad_id = :localidad_id";
    }
    if (isset($params['fecha_inicio_disponibilidad'])) {
        $sql .= " AND fecha_inicio_disponibilidad >= :fecha_inicio_disponibilidad";
    }
    if (isset($params['cantidad_huespedes'])) {
        $sql .= " AND cantidad_huespedes = :cantidad_huespedes";
    }

    // Preparar la consulta
    $consulta = $pdo->prepare($sql);

    // Vincular los valores de los parámetros de consulta
    if (isset($params['disponible'])) {
        // Convertir 'true' a 1 y 'false' a 0
        $disponible = $params['disponible'] === 'true' ? 1 : 0;
        $consulta->bindValue(':disponible', $disponible, PDO::PARAM_INT);
    }
    if (isset($params['localidad_id'])) {
        $consulta->bindValue(':localidad_id', $params['localidad_id'], PDO::PARAM_INT);
    }
    if (isset($params['fecha_inicio_disponibilidad'])) {
        $consulta->bindValue(':fecha_inicio_disponibilidad', $params['fecha_inicio_disponibilidad'], PDO::PARAM_STR);
    }
    if (isset($params['cantidad_huespedes'])) {
        $consulta->bindValue(':cantidad_huespedes', $params['cantidad_huespedes'], PDO::PARAM_INT);
    }

    // Ejecutar la consulta
    //var_dump($consulta);die();
    $consulta->execute();
    $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);


    // Generar la respuesta
    if (isset($resultados) && is_array($resultados) && !empty($resultados)) {
        responseWithSuccess($response, $resultados, 200);
    } else {
        responseWithError($response, 'No hay propiedades en la base', 400);
    }

    return $response;
}

function postPropiedades(Request $request, Response $response)
{
    $data = $request->getParsedBody();

    global $propiedadesCamposRequeridos;
    global $longCampoPropiedades;
    $erroresValidacion = validarCampo($data, $propiedadesCamposRequeridos, $longCampoPropiedades);

    if (!empty($erroresValidacion)) {
        return responseWithError($response, $erroresValidacion, 400);
    } else {
        try {
            $pdo = getConnection();

            $tipo_propiedad_id = $data['tipo_propiedad_id'];
            $localidad_id = $data['localidad_id'];
            $domicilio = $data['domicilio'];
            $cantidad_habitaciones = isset($data['cantidad_habitaciones']) ? $data['cantidad_habitaciones'] : null;
            $cantidad_banios = isset($data['cantidad_banios']) ? $data['cantidad_banios'] : null;
            $cochera = isset($data['cochera']) ? $data['cochera'] : null;
            $cantidad_huespedes = $data['cantidad_huespedes'];
            $fecha_inicio_disponibilidad = $data['fecha_inicio_disponibilidad'];
            $cantidad_dias = $data['cantidad_dias'];
            $disponible = $data['disponible'];
            $valor_noche = $data['valor_noche'];
            $imagen = isset($data['imagen']) ? $data['imagen'] : null;
            $tipo_imagen = isset($data['tipo_imagen']) ? $data['tipo_imagen'] : null;
            $disponible = ($disponible === 'true') ? 1 : 0;
            $sql = "INSERT INTO propiedades (domicilio, localidad_id, cantidad_habitaciones, cantidad_banios, cochera, cantidad_huespedes, fecha_inicio_disponibilidad, cantidad_dias, disponible, valor_noche, tipo_propiedad_id, imagen, tipo_imagen) VALUES (:domicilio, :localidad_id, :cantidad_habitaciones, :cantidad_banios, :cochera, :cantidad_huespedes, :fecha_inicio_disponibilidad, :cantidad_dias, :disponible, :valor_noche, :tipo_propiedad_id, :imagen, :tipo_imagen)";
            $consulta = $pdo->prepare($sql);
            $consulta->bindValue(':domicilio', $domicilio);
            $consulta->bindValue(':localidad_id', $localidad_id);
            $consulta->bindValue(':cantidad_habitaciones', $cantidad_habitaciones);
            $consulta->bindValue(':cantidad_banios', $cantidad_banios);
            $consulta->bindValue(':cochera', $cochera);
            $consulta->bindValue(':cantidad_huespedes', $cantidad_huespedes);
            $consulta->bindValue(':fecha_inicio_disponibilidad', $fecha_inicio_disponibilidad);
            $consulta->bindValue(':cantidad_dias', $cantidad_dias);
            $consulta->bindValue(':disponible', $disponible);
            $consulta->bindValue(':valor_noche', $valor_noche);
            $consulta->bindValue(':tipo_propiedad_id', $tipo_propiedad_id);
            $consulta->bindValue(':imagen', $imagen);
            $consulta->bindValue(':tipo_imagen', $tipo_imagen);
            $consulta->execute();
            responseWithSuccess($response, 'Propiedad creado con éxito', 201);
            return $response;
        } catch (\Exception $e) {
            responseWithError($response, $e, 500);
            return $response;
        }
    }
}

function putPropiedades(Request $request, Response $response, $args)
{
    $data = $request->getParsedBody();

    global $propiedadesCamposRequeridos;
    global $longCampoPropiedades;
    $erroresValidacion = validarCampo($data, $propiedadesCamposRequeridos, $longCampoPropiedades);
    $id = $args['id'];
    $error['id'] = validarTipo('id', $id);

    if (!empty($erroresValidacion)) {
        return responseWithError($response, $erroresValidacion, 400);
    } else if (isset($error['id'])) {
        return responseWithError($response, $error, 400);
    } else {
        try {
            $pdo = getConnection();

            $tipo_propiedad_id = $data['tipo_propiedad_id'];
            $localidad_id = $data['localidad_id'];
            $domicilio = $data['domicilio'];
            $cantidad_habitaciones = isset($data['cantidad_habitaciones']) ? $data['cantidad_habitaciones'] : null;
            $cantidad_banios = isset($data['cantidad_banios']) ? $data['cantidad_banios'] : null;
            $cochera = isset($data['cochera']) ? $data['cochera'] : null;
            $cantidad_huespedes = $data['cantidad_huespedes'];
            $fecha_inicio_disponibilidad = $data['fecha_inicio_disponibilidad'];
            $cantidad_dias = $data['cantidad_dias'];
            $disponible = $data['disponible'];
            $valor_noche = $data['valor_noche'];
            $imagen = isset($data['imagen']) ? $data['imagen'] : null;
            $tipo_imagen = isset($data['tipo_imagen']) ? $data['tipo_imagen'] : null;

            $sql = "UPDATE propiedades SET domicilio = (:domicilio), localidad_id = (:localidad_id), cantidad_habitaciones = (:cantidad_habitaciones), cantidad_banios = (:cantidad_banios), cochera = (:cochera), cantidad_huespedes = (:cantidad_huespedes), fecha_inicio_disponibilidad = (:fecha_inicio_disponibilidad), cantidad_dias = (:cantidad_dias), disponible = (:disponible), valor_noche = (:valor_noche), tipo_propiedad_id = (:tipo_propiedad_id), imagen = (:imagen), tipo_imagen = (:tipo_imagen) WHERE id = (:id)";

            $consulta = $pdo->prepare($sql);
            $consulta->bindValue(':id', $id);
            $consulta->bindValue(':domicilio', $domicilio);
            $consulta->bindValue(':localidad_id', $localidad_id);
            $consulta->bindValue(':cantidad_habitaciones', $cantidad_habitaciones);
            $consulta->bindValue(':cantidad_banios', $cantidad_banios);
            $consulta->bindValue(':cochera', $cochera);
            $consulta->bindValue(':cantidad_huespedes', $cantidad_huespedes);
            $consulta->bindValue(':fecha_inicio_disponibilidad', $fecha_inicio_disponibilidad);
            $consulta->bindValue(':cantidad_dias', $cantidad_dias);
            $consulta->bindValue(':disponible', $disponible);
            $consulta->bindValue(':valor_noche', $valor_noche);
            $consulta->bindValue(':tipo_propiedad_id', $tipo_propiedad_id);
            $consulta->bindValue(':imagen', $imagen);
            $consulta->bindValue(':tipo_imagen', $tipo_imagen);
            $consulta->execute();

            return responseWithSuccess($response, 'Propiedad modificada con éxito', 201);
        } catch (\Exception $e) {
            responseWithError($response, $e, 500);
            return $response;
        }
    }
}

function getPropiedad(Request $request, Response $response, $args)
{
    $id = $args['id'];
    try {
        $pdo = getConnection();
        $sql = "SELECT * FROM propiedades WHERE id = '" . $id . "'";
        $consulta = $pdo->query($sql);
        if ($consulta->rowCount() == 0) {
            responseWithError($response, 'ID Not Found', 404);
            return $response;
        } else {
            $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
            responseWithSuccess($response, $resultados, 200);
            return $response;
        }
    } catch (\Exception $e) {
        responseWithError($response, $e, 500);
        return $response;
    }
}

function deletePropiedades(Request $request, Response $response, $args)
{
    $id = $args['id'];
    $error['id'] = validarTipo('id', $id);
    if (isset($error['id'])) {
        return responseWithError($response, $error, 400);
    }
    try {
        $pdo = getConnection();
        $sql = "SELECT * FROM propiedades WHERE id = '" . $id . "'";
        $consulta = $pdo->query($sql);
        var_dump($id);
        if ($consulta->rowCount() == 0) {
            return responseWithError($response, 'No se encontró una propiedad con ese id', 404);
        } else {
            $sql = "DELETE FROM propiedades WHERE id = (:id)";
            $consulta = $pdo->prepare($sql);
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $stmt = $pdo->prepare("ALTER TABLE propiedades AUTO_INCREMENT = 1");
            $stmt->execute();
            responseWithSuccess($response, 'Propiedad eliminada con éxito', 201);
            return $response;
        }
    } catch (\Exception $e) {
        responseWithError($response, $e, 500);
        return $response;
    }
}
