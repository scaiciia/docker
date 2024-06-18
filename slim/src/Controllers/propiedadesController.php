<?php

$longCampoPropiedades = array('domicilio' => 225, 'tipo_imagen' => 50);
$propiedadesCamposRequeridos = ['domicilio', 'localidad_id', 'cantidad_huespedes', 'fecha_inicio_disponibilidad', 'cantidad_dias', 'disponible', 'valor_noche', 'tipo_propiedad_id'];


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function getPropiedades(Request $request, Response $response, $args) {
    $filtros = ['disponible', 'localidad_id', 'fecha_inicio_disponibilidad', 'cantidad_huespedes']; 
    $data = $request->getQueryParams();
    if (isset($data['disponible'])) {
        $data['disponible'] = filter_var($data['disponible'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
    $data['localidad_id'] = empty($data['localidad_id']) ? null : intval($data['localidad_id']);
    $data['cantidad_huespedes'] = empty($data['cantidad_huespedes']) ? null : intval($data['cantidad_huespedes']);
    $data['fecha_inicio_disponibilidad'] = empty($data['fecha_inicio_disponibilidad']) ? null : $data['fecha_inicio_disponibilidad'];

    $filtro = "";
    $error = [];
    foreach ($filtros as $valor) {
        if (isset($data[$valor])) {
            $tipoInvalido = [];
            $tipoInvalido[$valor] = validarTipo($valor, $data[$valor]);

            if (empty($tipoInvalido[$valor])) {
                if (is_bool($data[$valor])) {
                    $data[$valor] = $data[$valor] ? 1 : 0;
                }
                if (empty($filtro)) {
                    $filtro = " WHERE ";
                } else {
                    $filtro .= " AND ";
                }
                $filtro .= "$valor = '" . $data[$valor] . "'";
            } else {
                $error = array_merge($error, $tipoInvalido);
            }
        }
    }

    try {
        $pdo = getConnection();

        $sql = "SELECT * FROM propiedades";
        if (!empty($filtro)) {
            $sql .= $filtro;
        }
        $consulta = $pdo->query($sql);
        $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

        if (isset($resultados) && is_array($resultados) && !empty($resultados)) {
            $resultadoFinal = [];
            foreach ($resultados as $resultado) {
                $sql = "SELECT * FROM localidades WHERE id = ?";
                $consulta = $pdo->prepare($sql);
                $consulta->execute([$resultado['localidad_id']]);
                $resultado['localidad_id'] = $consulta->fetch(PDO::FETCH_ASSOC);

                $sql = "SELECT * FROM tipo_propiedades WHERE id = ?";
                $consulta = $pdo->prepare($sql);
                $consulta->execute([$resultado['tipo_propiedad_id']]);
                $resultado['tipo_propiedad_id'] = $consulta->fetch(PDO::FETCH_ASSOC);

                $resultadoFinal[] = $resultado;
            }
            responseWithData($response, $resultadoFinal, 200, $error);
        } else {
            responseWithError($response, 'No hay propiedades', 404);
        }
        return $response;
    } catch (\Exception $e) {
        responseWithError($response, $e, 500);
        return $response;
    }
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
            $sql = "SELECT * FROM tipo_propiedades";
            $consulta = $pdo->query($sql);
            if ($consulta->rowCount() > 0) {
                $localidad_id = $data['localidad_id'];
                $sql = "SELECT * FROM localidades";
                $consulta = $pdo->query($sql);
                if ($consulta->rowCount() > 0) {
                    $domicilio = $data['domicilio'];
                    $cantidad_habitaciones = isset($data['cantidad_habitaciones']) ? $data['cantidad_habitaciones'] : null;
                    $cantidad_banios = isset($data['cantidad_banios']) ? $data['cantidad_banios'] : null;
                    $cochera = isset($data['cochera']) ? $data['cochera'] : null;
                    $cantidad_huespedes = $data['cantidad_huespedes'];
                    $fecha_inicio_disponibilidad = $data['fecha_inicio_disponibilidad'];
                    $cantidad_dias = $data['cantidad_dias'];
                    $disponible = $data['disponible'];
                    $disponible = $data['disponible'] ? 1 : 0;
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
                    return responseWithSuccess($response, 'Propiedad creado con éxito', 201);
                } else {
                    return responseWithError($response, 'No se encontro localidad', 404);
                }
            } else{
                return responseWithError($response, 'No se encontro tipo de propiedad', 404);
            }
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
            $sql = "SELECT * FROM tipo_propiedades";
            $consulta = $pdo->query($sql);
            if ($consulta->rowCount() > 0) {
                $localidad_id = $data['localidad_id'];
                $sql = "SELECT * FROM localidades";
                $consulta = $pdo->query($sql);
                if ($consulta->rowCount() > 0) {
                    $domicilio = $data['domicilio'];
                    $cantidad_habitaciones = isset($data['cantidad_habitaciones']) ? $data['cantidad_habitaciones'] : null;
                    $cantidad_banios = isset($data['cantidad_banios']) ? $data['cantidad_banios'] : null;
                    $cochera = isset($data['cochera']) ? $data['cochera'] : null;
                    $cantidad_huespedes = $data['cantidad_huespedes'];
                    $fecha_inicio_disponibilidad = $data['fecha_inicio_disponibilidad'];
                    $cantidad_dias = $data['cantidad_dias'];
                    $disponible = $data['disponible'];
                    $disponible = $data['disponible'] ? 1 : 0;
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
                } else {
                    return responseWithError($response, 'No se encontro localidad', 404);
                }
            } else{
                return responseWithError($response, 'No se encontro tipo de propiedad', 404);
            }
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
