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
        $reservas = $consulta->fetchAll(PDO::FETCH_ASSOC);
        if($consulta->rowCount() != 0){
            $resultados = [];
            foreach ($reservas as $reserva) {

                $sql = "SELECT * FROM propiedades WHERE id = ?";
                $consulta = $pdo->prepare($sql);
                $consulta->execute([$reserva['propiedad_id']]);
                $propiedad = $consulta->fetch(PDO::FETCH_ASSOC);
                
                $sql = "SELECT * FROM localidades WHERE id = ?";
                $consulta = $pdo->prepare($sql);
                $consulta->execute([$propiedad["localidad_id"]]);
                $propiedad["localidad_id"] = $consulta->fetch(PDO::FETCH_ASSOC); 

                $sql = "SELECT * FROM tipo_propiedades WHERE id = ?";
                $consulta = $pdo->prepare($sql);
                $consulta->execute([$propiedad['tipo_propiedad_id']]);
                $propiedad['tipo_propiedad_id'] = $consulta->fetch(PDO::FETCH_ASSOC);

                $sql = "SELECT * FROM inquilinos WHERE id = ?";
                $consulta = $pdo->prepare($sql);
                $consulta->execute([$reserva['inquilino_id']]);
                $inquilino = $consulta->fetch(PDO::FETCH_ASSOC);

                $reserva['inquilino_id'] = $inquilino;

                $reserva['propiedad_id'] = $propiedad;

                $resultados[] = $reserva;
            }
            return responseWithData($response, $resultados, 200);
        } else {
            return responseWithError($response, 'No se encontraron reservas', 404);
        }
    } catch (\Exception $e) {
        return responseWithError($response, $e, 500);
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
            if ($consulta->rowCount() == 0) {
                return responseWithError($response, 'No se encontró inquilino', 404); 
            } else {
                $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
                $active = $resultado['activo'];

                $propiedad_id = $data['propiedad_id'];
                $sql = "SELECT disponible FROM propiedades WHERE id = '" . $propiedad_id . "'";
                $consulta = $pdo->query($sql);
                if ($consulta->rowCount() == 0) {
                    return responseWithError($response, 'No se encontró propiedad', 404);
                } else {
                $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
                $disponible = $resultado['disponible'];
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
                            responseWithSuccess($response, 'La propiedad no esta disponible en esta fecha', 201);
                            return $response;
                        }

                        $sql = "INSERT INTO reservas (propiedad_id,inquilino_id,fecha_desde,cantidad_noches,valor_total) VALUES (:propiedad_id,:inquilino_id,:fecha_desde,:cantidad_noches,:valor_total)";
                        $consulta = $pdo->prepare($sql);
                        $consulta->bindValue(':propiedad_id', $propiedad_id);
                        $consulta->bindValue(':inquilino_id', $inquilino_id);
                        $consulta->bindValue(':fecha_desde', $fecha_desde);
                        $consulta->bindValue(':cantidad_noches', $cantidad_noches);
                        $consulta->bindValue(':valor_total', $valor_total);
                        $consulta->execute();
                        responseWithSuccess($response, 'Reserva creada con éxito', 201);
                        return $response;
                    } else {

                        if ($disponible == 0) {
                            $mensaje = 'La propiedad no se encuenta disponible';
                        } else if ($active == 0) {
                            $mensaje = 'El inquilino no se encuentra disponible';
                        }
                        responseWithError($response, $mensaje, 400);
                        return $response;
                    }
                }
            }
        } catch (\Exception $e) {
            responseWithError($response, $e, 500);
            return $response;
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
            responseWithError($response, 'La reserva no se puede eliminar una vez iniciada la estadía', 404);
            return $response;
        }
        $sql = "DELETE FROM reservas WHERE id = '" . $id . "'";
        $consulta = $pdo->query($sql);
        $stmt = $pdo->prepare("ALTER TABLE reservas AUTO_INCREMENT = 1");
        $stmt->execute();
        responseWithSuccess($response, 'Reserva borrada con éxito', 201);
        return $response;
    } catch (\Exception $e) {
        responseWithError($response, $e, 500);
        return $response;
    }
}

function putReservas(Request $request, Response $response, $args)
{
    global $longCampoReservas;
    global $reservasCamposRequeridos;
    $data = $request->getParsedBody();
    $id = $args['id'];
    $error['id'] = validarTipo('id', $id);
    $erroresValidacion = validarCampo($data, $reservasCamposRequeridos, $longCampoReservas);
    if (!empty($erroresValidacion)) { 
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
            responseWithError($response, 'La reserva no se puede modificar una vez iniciada la estadía', 404);
            return $response;
        }
        $data = $request->getParsedBody();
        $inquilino_id = $data['inquilino_id'];
        $sql = "SELECT activo FROM inquilinos WHERE id = '" . $inquilino_id . "'";
        $consulta = $pdo->query($sql);
        if ($consulta->rowCount() == 0) {
            return responseWithError($response, 'No se encontró inquilino', 404);   
        } else {
            $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
            $active = $resultado['activo'];
        
            $propiedad_id = $data['propiedad_id'];
            $sql = "SELECT disponible FROM propiedades WHERE id = '" . $propiedad_id . "'";
            $consulta = $pdo->query($sql);
            if ($consulta->rowCount() == 0) {
                return responseWithError($response, 'No se encontró propiedad', 404);  
            } else {
                $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
                $disponible = $resultado['disponible'];
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
                        responseWithSuccess($response, 'La propiedad no esta disponible en esta fecha', 201);
                        return $response;
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
                    responseWithSuccess($response, 'Reserva modficada con éxito', 201);
                    return $response;
                } else {

                    if ($disponible == 0) {
                        $mensaje = 'La propiedad no se encuenta disponible';
                    } else if ($active == 0) {
                        $mensaje = 'El inquilino no se encuentra disponible';
                    }
                    responseWithError($response, $mensaje, 404);
                    return $response;
                }
            }
        }
    } catch (\Exception $e) {
        responseWithError($response, $e, 500);
        return $response;
    }
}
