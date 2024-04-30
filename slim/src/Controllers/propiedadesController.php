<?php

$longCampoPropiedades = array('domicilio' => 225, 'tipo_imagen' => 50);
$propiedadesCamposRequeridos = ['domicilio', 'localidad_id', 'cantidad_huespedes', 'fecha_inicio_disponibilidad', 'cantidad_dias', 'disponible', 'valor_noche', 'tipo_propiedad_id'];

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function getPropiedades(Request $request, Response $response)
{

    $pdo = getConnection();

    $sql = "SELECT * FROM propiedades
    ORDER BY disponible DESC, localidad_id ASC, fecha_inicio_disponibilidad ASC, cantidad_huespedes ASC;";
    $consulta = $pdo->query($sql);
    $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

    if (isset($resultados) && is_array($resultados) && !empty($resultados)) {
        $payload = json_encode([
            'status' => 'successss',
            'code' => 200,
            'data' => $resultados
        ]);
    } else {
        $payload = json_encode([
            'status' => 'failed',
            'code' => 400,
            'error' => 'No hay propiedades en la base'
        ]);
    }

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
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
            $payload = json_encode([
                'code' => 201,
                'message' => 'Propiedad creado con éxito'
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(201);
        } catch (\Exception $e) {
            $payload = json_encode([
                'code' => '500',
                'error' => $e->getMessage()
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(500);
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
            $payload = json_encode([
                'code' => '500',
                'error' => $e->getMessage()
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(500);
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
            $payload = json_encode([
                'error' => 'ID Not Found',
                'code' => 404
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(404);
        } else {
            $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $payload = json_encode([
                'status' => 'success',
                'code' => 200,
                'data' => $resultados
            ]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    } catch (\Exception $e) {
        $payload = json_encode([
            'code' => '500',
            'error' => $e->getMessage()
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(500);
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
            $payload = json_encode([
                'code' => 201,
                'message' => 'Propiedad eliminada con éxito'
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(201);
        }
    } catch (\Exception $e) {
        $payload = json_encode([
            'code' => '500',
            'error' => $e->getMessage()
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(500);
    }
}
