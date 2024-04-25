<?php

require_once __DIR__ . '/../utils/utils.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function getPropiedades (Request $request, Response $response){

    $pdo = getConnection();

    $sql = "SELECT * FROM propiedades
    ORDER BY disponible DESC, localidad_id ASC, fecha_inicio_disponibilidad ASC, cantidad_huespedes ASC;";
    $consulta = $pdo->query($sql);
    $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

    if(isset($resultados) && is_array($resultados) && !empty($resultados)){
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

function postPropiedades (Request $request, Response $response){
    $data = $request->getParsedBody();
    $requiredFields = ['domicilio', 'localidad_id', 'cantidad_huespedes', 'fecha_inicio_disponibilidad', 'cantidad_dias', 'disponible', 'valor_noche', 'tipo_propiedad_id'];
    $responseVal = validationFields($data,$requiredFields,$response);
    //var_dump($responseVal);die;
    if (!$responseVal){
        return $response->withStatus(400);
    } else {
        try {
            $pdo = getConnection();
            $nombre_localidad = $data['localidad_id'];
            $nombre_tipo_propiedad = $data['tipo_propiedad_id'];
            $sql = "SELECT id  FROM localidades WHERE nombre = (:nombre_localidad) UNION SELECT id FROM tipo_propiedades WHERE nombre = (:nombre_tipo_propiedad)";
            $consulta = $pdo->prepare($sql);
            $consulta->bindValue(':nombre_localidad', $nombre_localidad);
            $consulta->bindValue(':nombre_tipo_propiedad', $nombre_tipo_propiedad);
            $consulta->execute();
            $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $id_localidades = $resultados[0]['id'];
            if (!isset($resultados[1]['id']) && isset($resultados[0]['id'])) {
                $payload = json_encode([
                    'error' => "El campo "." $resultados[0]['id'] " . "es incorrecto",
                    'code' => "400"
                ]);
                $response->getBody()->write($payload);
                return $response;
            }
            $tipo_propiedad_id = $resultados[1]['id'];
            $localidad_id=$resultados[0]['id'];
            
            $id = $data['id'];
            $domicilio = $data['domicilio'];
            $cantidad_habitaciones = $data['cantidad_habitaciones'];
            $cantidad_banios = $data['cantidad_banios'];
            $cochera = $data['cochera']; 
            $fecha_inicio_disponibilidad = $data['fecha_inicio_disponibilidad'];
            $cantidad_dias = $data['cantidad_dias'];
            $disponible = $data['disponible'];
            $valor_noche = $data['valor_noche'];
            $imagen = $data['imagen'];
            $tipo_imagen = $data['tipo_imagen'];
            $cantidad_huespedes = $data['cantidad_huespedes'];
            
            $sql = "INSERT INTO propiedades (id, domicilio, localidad_id, cantidad_habitaciones, cantidad_banios, cochera, cantidad_huespedes, fecha_inicio_disponibilidad, cantidad_dias, disponible, valor_noche, tipo_propiedad_id, imagen, tipo_imagen) VALUES (:id, :domicilio, :localidad_id, :cantidad_habitaciones, :cantidad_banios, :cochera, :cantidad_huespedes, :fecha_inicio_disponibilidad, :cantidad_dias, :disponible, :valor_noche, :tipo_propiedad_id, :imagen, :tipo_imagen)";
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
    
    function putPropiedades (Request $request, Response $response, $args){
        try {
            
            $id = $args['id'];
            $pdo = getConnection();
        $sql = "SELECT * FROM propiedades WHERE id = '" . $id . "'"    ;
        $consulta =  $pdo->query($sql);
        if ($consulta->rowCount() == 0) {
            $payload = json_encode([
                'error' => 'Not Found',
                'code' => 404
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(404);
        }else{
            
            $data = $request->getParsedBody();
            $tipo_propiedad_id = isset($data['tipo_propiedad_id']) ? $data['tipo_propiedad_id'] : null;
            $localidad_id = isset($data['localidad_id']) ? $data['localidad_id'] : null;
            $domicilio = isset($data['domicilio']) ? $data['domicilio'] : null;
            $cantidad_habitaciones = isset($data['cantidad_habitaciones']) ? $data['cantidad_habitaciones'] : null;
            $cantidad_banios = isset($data['cantidad_banios']) ? $data['cantidad_banios'] : null;
            $cochera = isset($data['cochera']) ? $data['cochera'] : null;
            $fecha_inicio_disponibilidad = isset($data['fecha_inicio_disponibilidad']) ? $data['fecha_inicio_disponibilidad'] : null;
            $cantidad_dias = isset($data['cantidad_dias']) ? $data['cantidad_dias'] : null;
            $disponible = isset($data['disponible']) ? $data['disponible'] : null;
            $valor_noche = isset($data['valor_noche']) ? $data['valor_noche'] : null;
            $imagen = isset($data['imagen']) ? $data['imagen'] : null;
            $tipo_imagen = isset($data['tipo_imagen']) ? $data['tipo_imagen'] : null;
            $cantidad_huespedes = isset($data['cantidad_huespedes']) ? $data['cantidad_huespedes'] : null;

            $sql = "UPDATE propiedades SET";
            $params = [];
            
            if (!empty($domicilio)) {
                $sql .= " domicilio = :domicilio,";
                $params[':domicilio'] = $domicilio;
            }

            if (!empty($localidad_id)) {
                $sql .= " localidad_id = :localidad_id,";
                $params[':localidad_id'] = $localidad_id;
            }

            if (!empty($cantidad_habitaciones)) {
                $sql .= " cantidad_habitaciones = :cantidad_habitaciones,";
                $params[':cantidad_habitaciones'] = $cantidad_habitaciones;
            }
            if (!empty($cantidad_banios)) {
                $sql .= " cantidad_banios = :cantidad_banios,";
                $params[':cantidad_banios'] = $cantidad_banios;
            }
            
            if (!empty($cochera)) {
                $sql .= " cochera = :cochera,";
                $params[':cochera'] = $cochera;
            }
            
            if (!empty($cantidad_huespedes)) {
                $sql .= " cantidad_huespedes = :cantidad_huespedes,";
                $params[':cantidad_huespedes'] = $cantidad_huespedes;
            }
            
            if (!empty($fecha_inicio_disponibilidad)) {
                $sql .= " fecha_inicio_disponibilidad = :fecha_inicio_disponibilidad,";
                $params[':fecha_inicio_disponibilidad'] = $fecha_inicio_disponibilidad;
            }
            
            if (!empty($cantidad_dias)) {
                $sql .= " cantidad_dias = :cantidad_dias,";
                $params[':cantidad_dias'] = $cantidad_dias;
            }
            
            if (!empty($disponible)) {
                $sql .= " disponible = :disponible,";
                $params[':disponible'] = $disponible;
            }
            
            if (!empty($valor_noche)) {
                $sql .= " valor_noche = :valor_noche,";
                $params[':valor_noche'] = $valor_noche;
            }

            if (!empty($tipo_propiedad_id)){
                $sql .= " tipo_propiedad_id = :tipo_propiedad_id,";
                $params[':tipo_propiedad_id'] = $tipo_propiedad_id;
            }
            
            if (!empty($imagen)) {
                $sql .= " imagen = :imagen,";
                $params[':imagen'] = $imagen;
            }
            
            if (!empty($tipo_imagen)) {
                $sql .= " tipo_imagen = :tipo_imagen,";
                $params[':tipo_imagen'] = $tipo_imagen;
            }
            $sql = rtrim($sql, ',');

            $sql .= " WHERE id = :id";
            $params[':id'] = $id;

            $consulta = $pdo->prepare($sql);

            foreach ($params as $key => $value) {
                $consulta->bindValue($key, $value);
            }
            $consulta->execute();
            $payload = json_encode([
                'code' => 201,
                'message' => 'Localidad editada con éxito'
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

function getPropiedad (Request $request, Response $response , $args) {
    $id = $args['id'];
    try {
        $pdo = getConnection();
        $sql = "SELECT * FROM propiedades WHERE id = '" . $id ."'";
        $consulta = $pdo->query($sql);
        if ($consulta->rowCount() == 0) {
            $payload = json_encode([
                    'error' => 'ID Not Found',
                    'code' => 404
            ]);
            $response-> getBody()->write($payload);
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
    } catch (\Exception $e){
        $payload = json_encode([
            'code' => '500',
            'error' => $e->getMessage()
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(500);
    }

}

function deletePropiedades(Request $request, Response $response , $args){
   $id = $args['id'];
    try {
        $pdo = getConnection();
        $sql = "SELECT * FROM propiedades WHERE id = '" . $id . "'";
        $consulta = $pdo->query($sql);
        if ($consulta->rowCount() == 0) {
            $payload = json_encode([
                    'error' => 'Not Found',
                    'code' => 404
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(404);
        } else {
            $sql = "DELETE FROM propiedades WHERE id = (:id)";
            $consulta = $pdo->prepare($sql);
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
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
