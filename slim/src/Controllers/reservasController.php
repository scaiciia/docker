<?php
require_once __DIR__ . "/../../utils/utils.php";
$longCampoReservas = array('' => 225);
$reservasCamposRequeridos = ['propiedad_id','inquilino_id','fecha_desde','cantidad_noches'];
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
    //$requiredFields = ['propiedad_id', 'inquilino_id', 'fecha_desde', 'cantidad_noches'];
    //$responseVal = validationFields($data, $requiredFields, $response);
    global $longCampoReservas ;
    global $reservasCamposRequeridos ;
    $erroresValidacion = validarCampo($data, $reservasCamposRequeridos, $longCampoReservas);
    if (!empty($erroresValidacion)){ // Validacion de campos
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
                if ( $fecha2 < $fecha1 ) {
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

                if ($disponible == 0 ){

                    $payload = json_encode([
                        'code' => '200',
                        'error' => 'La propiedad no se encuenta disponible'
                    ]);
                } else if($active == 0){
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
                'message' => 'La reserva no se puede eliminar una vez iniciada la estadía'
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(201);
        }
        $sql = "DELETE FROM reservas WHERE id = '" . $id . "'";
        $consulta = $pdo->query($sql);
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
    $data = $request->getParsedBody();
    $id = $args['id'];
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
        $propiedad_id = isset($data['propiedad_id']) ? $data['propiedad_id'] : null;
        $inquilino_id = isset($data['inquilino_id']) ? $data['inquilino_id'] : null;
        $fecha_inicio_disponibilidad = isset($data['fecha_inicio_disponibilidad']) ? $data['fecha_inicio_disponibilidad'] : null;
        $cantidad_noches = isset($data['cantidad_noches']) ? $data['cantidad_noches'] : null;
        $sql = "UPDATE reservas SET";
        $params = [];
        if (!empty($propiedad_id)) {
            $sql .= " propiedad_id = :propiedad_id,";
            $params[':propiedad_id'] = $propiedad_id;
            $sql_2 = "SELECT valor_noche FROM propiedades WHERE id = '" . $propiedad_id . "'";
            $consulta = $pdo->query($sql_2);
            $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $valor_noche = $resultado[0]['valor_noche'];
            $sql_3 = "SELECT cantidad_noches FROM reservas WHERE id = '" . $id . "'";
            $consulta = $pdo->query($sql_3);
            $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $cantidad_noches_aux = $resultado[0]['cantidad_noches'];
            $valor_total = $valor_noche * $cantidad_noches_aux;
            //var_dump($valor_total);die;
            $sql .= " valor_total = :valor_total,";
            $params[':valor_total'] = $valor_total;
        }

        if (!empty($inquilino_id)) {
            $sql .= " inquilino_id = :inquilino_id,";
            $params[':inquilino_id'] = $inquilino_id;
        }

        if (!empty($fecha_inicio_disponibilidad)) {
            $sql .= " fecha_inicio_disponibilidad = :fecha_inicio_disponibilidad,";
            $params[':fecha_inicio_disponibilidad'] = $fecha_inicio_disponibilidad;
        }
        if (!empty($cantidad_noches)) {
            $sql .= " cantidad_noches = :cantidad_noches,";
            $params[':cantidad_noches'] = $cantidad_noches;
            if (empty($propiedad_id)) {
                $sql_2 = "SELECT propiedad_id FROM reservas WHERE id = '" . $id . "'";
                $consulta = $pdo->query($sql_2);
                $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
                $propiedad_id = $resultado[0]['propiedad_id'];
            }
            $sql_2 = "SELECT valor_noche FROM propiedades WHERE id = '" . $propiedad_id . "'";
            $consulta = $pdo->query($sql_2);
            $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $valor_noche = $resultado[0]['valor_noche'];
            $sql_3 = "SELECT cantidad_noches FROM reservas WHERE id = '" . $id . "'";
            $consulta = $pdo->query($sql_3);
            $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $cantidad_noches = $resultado[0]['cantidad_noches'];
            $valor_total = $valor_noche  * $cantidad_noches;
            if (strpos($sql, "valor_total = :valor_total") === false) {
                $sql .= " valor_total = :valor_total,";
            }
            $params[':valor_total'] = $valor_total;
            $sql = rtrim($sql, ',');
        }
        $sql .= " WHERE id = :id";
        $params[':id'] = $id;
        $consulta = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $consulta->bindValue($key, $value);
        }
        $consulta->execute();
        $payload = json_encode([
            'code' => 201,
            'message' => 'Reserva editada con éxito'
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
