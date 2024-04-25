<?php
require_once __DIR__ . "/../utils/utils.php";

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function getReservas (Request $request, Response $response) {
    try{
        $pdo = getConnection();
        $sql = "SELECT * FROM reservas";
        $consulta = $pdo->query($sql);
        $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $payload = json_encode ([
            'status' => 'success',
            'code' => 200,
            'data' => $resultados
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(200); 
    } catch (\Exception $e){
        $payload = json_encode([
            'code' => '500',
            'error' => $e->getMessage()
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(500);
    }
};

function postReservas (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $requiredFields = ['domicilio', 'localidad_id', 'cantidad_huespedes', 'fecha_inicio_disponibilidad', 'cantidad_dias', 'disponible', 'valor_noche', 'tipo_propiedad_id'];
    
    //campo requerido moneda_id ??
    $arr = [];
     $fields = "";
     foreach ($requiredFields as $field) {
         if (!isset($data[$field]) || empty($data[$field])) {
             $arr[] = $field; 
             if (!empty($fields)) {
                 $fields .= ', '; 
             }
             $fields .= $field; 
         }
     }
    if (!empty($arr)){
        $error = (count($arr) > 1)  ? "fatan los campos requeridos: " : "falta el campo requerido " ;
        $payload = json_encode([
            'error' => $error . $fields,
            'code' => '400'
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(400);
    $id = $data['id'];
    $propiedad_id = $data['propiedad_id'];
    $inquilino_id = $data['inquilino_id'];
    $fecha_desde = $data['fecha_desde'];
    $cantidad_noches = $data['cantidad_noches'];
    $valor_total = $data['valor_total'];
    // validar campos obligatorios
    try{
        $pdo = getConnection();

        $sql = "INSERT INTO reservas (id,propiedad_id,inquilino_id,fecha_desde,cantidad_noches,valor_total) VALUES (:id,:propiedad_id,:inquilino_id,:fecha_desde,:cantidad_noches,:valor_total)";
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
            'message' => 'Reserva creada con éxito'
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(201);
    } catch (\Exception $e){
        $payload = json_encode([
            'code' => '500',
            'error' => $e->getMessage()
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(500);
    }
    } 
}

function deleteReservas (Request $request, Response $response, $args){
    $id = $args['id'];
    // validar que se encuentre en rango
    try{
        $pdo = getConnection();
        $sql = "DELETE FROM reservas WHERE id = '". $id ."'";
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

function putReservas (Request $request, Response $response, $args){
    // validar que se encuentre en rango, si comenzo a correr los dias
    $data = $request->getParsedBody();
    $id = $args['id'];
    // $propiedad_id = isset($data['propiedad_id']) ? $data['propiedad_id'] : null;
    // $inquilino_id = isset($data['inquilino_id']) ? $data['inquilino_id'] : null;
    // $fecha_desde = isset($data['fecha_desde']) ? $data['fecha_desde'] : null;
    // $cantidad_noches = isset($data['cantidad_noches']) ? $data['cantidad_noches'] : null;
    $valor_total = isset($data['valor_total']) ? $data['valor_total'] : null;

    // if (!isset($data['inquilino_id']) || !isset($data['fecha_desde']) || !isset($data['cantidad_noches']) || !isset($data['valor_total'])) {
    //     $payload = json_encode(['error' => 'Faltan completar campos obligatorios', 'code' => '400']);
    //     $response->getBody()->write($payload);
    //     return $response->withStatus(400);
    // }
    //else {
        try {
            $pdo = getConnection();
            $sql = "UPDATE reservas SET valor_total = (:valor_total) WHERE id = (:id)";
            $consulta = $pdo->prepare($sql);
            $consulta->bindValue(':valor_total', $valor_total, PDO::PARAM_STR);
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $payload = json_encode([
                'code' => 201,
                'message' => 'Localidad editada con éxito'
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(201);
        } catch(\Exception $e) {
            $payload = json_encode([
                'code' => '500',
                'error' => $e->getMessage()
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(500);
        }
   // }

}

