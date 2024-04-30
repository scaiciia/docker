<?php
require_once __DIR__ . "/../../utils/utils.php";
$longCampoReservas = array('' => 225);
$reservasCamposRequeridos = ['propiedad_id', 'inquilino_id', 'fecha_desde', 'cantidad_noches'];

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function getReservas(Request $request, Response $response)
{
    try {
        $pdo = getConnection();
        $sql = "SELECT * FROM reservas";
        $consulta = $pdo->query($sql);
        $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $payload = json_encode([
            'status' => 'success',
            'code' => 200,
            'data' => $resultados
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(200);
    } catch (\Exception $e) {
        $payload = json_encode([
            'code' => '500',
            'error' => $e->getMessage()
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(500);
    }
};

function postReservas(Request $request, Response $response)
{
    $data = $request->getParsedBody();
    global $longCampoReservas;
    global $reservasCamposRequeridos;
    $erroresValidacion = validarCampo($data, $reservasCamposRequeridos, $longCampoReservas);
    if (!empty($erroresValidacion)) { // Validacion de campos
        return responseWithError($response, $erroresValidacion, 400);
    } else {
        try {
            $pdo = getConnection();

            $inquilino_id = $data['inquilino_id'];
            $sql = "SELECT activo FROM inquilinos WHERE id = '" . $inquilino_id . "'";
            $consulta = $pdo->query($sql);
            $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $active = $resultado[0]['activo'];

            $propiedad_id = $data['propiedad_id'];
            $sql = "SELECT disponible FROM propiedades WHERE id = '" . $propiedad_id . "'";
            $consulta = $pdo->query($sql);
            $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $disponible = $resultado[0]['disponible'];
            if ($active == 1 && $disponible == 1) {
                $cantidad_noches = $data['cantidad_noches'];
                $fecha_desde = $data['fecha_desde'];
                $sql = "SELECT valor_noche FROM propiedades WHERE id = '" . $propiedad_id . "'";
                $consulta = $pdo->query($sql);
                $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
                $valor_total = $resultado[0]['valor_noche'] * $cantidad_noches;
                $sql = "SELECT fecha_inicio_disponibilidad FROM propiedades WHERE id = '" . $propiedad_id . "'";
                $consulta = $pdo->query($sql);
                $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
                $fecha_inicio_disponibilidad = $resultado[0]['fecha_inicio_disponibilidad'];
                $fecha1 = new DateTime($fecha_inicio_disponibilidad);
                $fecha2 = new DateTime($fecha_desde);
                if ($fecha2 < $fecha1) {
                    $payload = json_encode([
                        'code' => 201,
                        'message' => 'La propiedad no esta disponible en esta fecha'
                    ]);
                    $response->getBody()->write($payload);
                    return $response->withStatus(201);
                }

                $sql = "INSERT INTO reservas (propiedad_id,inquilino_id,fecha_desde,cantidad_noches,valor_total) VALUES (:propiedad_id,:inquilino_id,:fecha_desde,:cantidad_noches,:valor_total)";
                $consulta = $pdo->prepare($sql);
                $consulta->bindValue(':propiedad_id', $propiedad_id);
                $consulta->bindValue(':inquilino_id', $inquilino_id);
                $consulta->bindValue(':fecha_desde', $fecha_desde);
                $consulta->bindValue(':cantidad_noches', $cantidad_noches);
                $consulta->bindValue(':valor_total', $valor_total);
                $consulta->execute();
                $payload = json_encode([
                    'code' => 201,
                    'message' => 'Reserva creada con éxito'
                ]);

                $response->getBody()->write($payload);
                return $response->withStatus(201);
            } else {

                if ($disponible == 0) {

                    $payload = json_encode([
                        'code' => '200',
                        'error' => 'La propiedad no se encuenta disponible'
                    ]);
                } else if ($active == 0) {
                    $payload = json_encode([
                        'code' => '200',
                        'error' => 'El inquilino no se encuentra disponible'
                    ]);
                }
                $response->getBody()->write($payload);
                return $response;
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
}

function deleteReservas(Request $request, Response $response, $args)
{
    $id = $args['id'];
    $error['id'] = validarTipo('id', $id);
    if (isset($error['id'])) {
        return responseWithError($response, $error, 400);
    }
    try {
        $pdo = getConnection();
        $sql = "SELECT * FROM reservas WHERE id = '" . $id . "'";
        $consulta = $pdo->query($sql);
        if ($consulta->rowCount() == 0) {
            return responseWithError($response, 'No se encontró una reserva con ese id', 404);
        }
        $sql = "SELECT fecha_desde FROM reservas WHERE id = '" . $id . "'";
        $consulta = $pdo->query($sql);
        $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $fecha_desde = $resultado[0]['fecha_desde'];
        $fecha_actual = date("Y-m-d");
        $fecha1 = new DateTime($fecha_desde);
        $fecha2 = new DateTime($fecha_actual);
        if ($fecha1 < $fecha2) {
            $payload = json_encode([
                'code' => 201,
                'message' => 'La reserva no se puede eliminar una vez iniciada la estadía'
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(201);
        }
        $sql = "DELETE FROM reservas WHERE id = '" . $id . "'";
        $consulta = $pdo->query($sql);
        $stmt = $pdo->prepare("ALTER TABLE reservas AUTO_INCREMENT = 1");
        $stmt->execute();
        $payload = json_encode([
            'code' => 201,
            'message' => 'Reserva borrada con éxito'
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

function putReservas(Request $request, Response $response, $args)
{
    $id = $args['id'];
    global $longCampoReservas;
    global $reservasCamposRequeridos;
    $data = $request->getParsedBody();
    $id = $args['id'];
    $error['id'] = validarTipo('id', $id);
    $erroresValidacion = validarCampo($data, $reservasCamposRequeridos, $longCampoReservas);
    if (!empty($erroresValidacion)) { // Validacion de campos
        return responseWithError($response, $erroresValidacion, 400);
    } else if (isset($error['id'])) {
        return responseWithError($response, $error, 400);
    }
    try {
        $pdo = getConnection();
        $sql = "SELECT fecha_desde FROM reservas WHERE id = '" . $id . "'";
        $consulta = $pdo->query($sql);
        $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $fecha_desde = $resultado[0]['fecha_desde'];
        $fecha_actual = date("Y-m-d");
        $fecha1 = new DateTime($fecha_desde);
        $fecha2 = new DateTime($fecha_actual);
        if ($fecha1 < $fecha2) {
            $payload = json_encode([
                'code' => 201,
                'message' => 'La reserva no se puede modificar una vez iniciada la estadía'
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(201);
        }
        $data = $request->getParsedBody();
        $inquilino_id = $data['inquilino_id'];
        $sql = "SELECT activo FROM inquilinos WHERE id = '" . $inquilino_id . "'";
        $consulta = $pdo->query($sql);
        $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $active = $resultado[0]['activo'];

        $propiedad_id = $data['propiedad_id'];
        $sql = "SELECT disponible FROM propiedades WHERE id = '" . $propiedad_id . "'";
        $consulta = $pdo->query($sql);
        $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $disponible = $resultado[0]['disponible'];
        if ($active == 1 && $disponible == 1) {
            $cantidad_noches = $data['cantidad_noches'];
            $fecha_desde = $data['fecha_desde'];
            $sql = "SELECT valor_noche FROM propiedades WHERE id = '" . $propiedad_id . "'";
            $consulta = $pdo->query($sql);
            $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $valor_total = $resultado[0]['valor_noche'] * $cantidad_noches;
            $sql = "SELECT fecha_inicio_disponibilidad FROM propiedades WHERE id = '" . $propiedad_id . "'";
            $consulta = $pdo->query($sql);
            $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $fecha_inicio_disponibilidad = $resultado[0]['fecha_inicio_disponibilidad'];
            $fecha1 = new DateTime($fecha_inicio_disponibilidad);
            $fecha2 = new DateTime($fecha_desde);
            if ($fecha2 < $fecha1) {
                $payload = json_encode([
                    'code' => 201,
                    'message' => 'La propiedad no esta disponible en esta fecha'
                ]);
                $response->getBody()->write($payload);
                return $response->withStatus(201);
            }

            $sql = "UPDATE reservas SET propiedad_id = :propiedad_id, inquilino_id = :inquilino_id, fecha_desde = :fecha_desde, cantidad_noches = :cantidad_noches, valor_total = :valor_total WHERE id = (:id)";
            $consulta = $pdo->prepare($sql);
            $consulta->bindValue(':id', $id);
            $consulta->bindValue(':propiedad_id', $propiedad_id);
            $consulta->bindValue(':inquilino_id', $inquilino_id);
            $consulta->bindValue(':fecha_desde', $fecha_desde);
            $consulta->bindValue(':cantidad_noches', $cantidad_noches);
            $consulta->bindValue(':valor_total', $valor_total);
            $consulta->execute();
            $payload = json_encode([
                'code' => 201,
                'message' => 'Reserva modficada con éxito'
            ]);

            $response->getBody()->write($payload);
            return $response->withStatus(201);
        } else {

            if ($disponible == 0) {

                $payload = json_encode([
                    'code' => '200',
                    'error' => 'La propiedad no se encuenta disponible'
                ]);
            } else if ($active == 0) {
                $payload = json_encode([
                    'code' => '200',
                    'error' => 'El inquilino no se encuentra disponible'
                ]);
            }
            $response->getBody()->write($payload);
            return $response;
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
