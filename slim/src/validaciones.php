<?php

function validarTipo($field, $dato) {
    $string_fields = [
        'nombre',
        'apellido',
        'tipo_imagen'
    ];

    $numeric_fields = [
        'cantidad_huespedes',
        'localidad_id',
        'valor_noche',
        'tipo_propiedad_id',
        'propiedad_id',
        'inquilino_id',
        'cantidad_noches'
    ];

    $date_fields = [
        'fecha_inicio_disponibilidad',
        'fecha_desde'
    ];

    if (in_array($field, $string_fields, true)) {
        if (!preg_match('/^[a-zA-Z\s]+$/', $dato)) {
            return "El campo $field solo debe contener letras y espacios";
        }
    } elseif ($field === 'documento') {
        if (!ctype_digit($dato)) {
            return "El campo $field solo debe contener solo números";
        }
    } elseif (in_array($field, $numeric_fields, true)) {
        if (!is_numeric($dato)) {
            return "El campo $field solo debe contener números";
        }
    } elseif ($field === 'email') {
        if (!filter_var($dato, FILTER_VALIDATE_EMAIL)) {
            return "El campo $field no es un correo eletrónico válido";
        }
    } elseif (in_array($field, $date_fields, true)) {

    } elseif ($field = 'activo') {
        if (!is_bool($dato)) {
            return "El campo $field debe ser booleano";
        }
    }
}

function validarLong($field, $dato, $long) {
    if (strlen($dato) > $long) {
        return "El campo $field debe ser menor a $long caracteres";
    }
}

function validarCampo($data, $requiredFields, $longCampo) {
    $camposFaltantes = [];
    $campoInvalido = [];
    foreach ($requiredFields as $field) {
        if ((!isset($data[$field])) || empty($data[$field])) {
            $camposFaltantes[$field] = "El campo $field es requerido";
        } else {
            $error = validarTipo($field, $data[$field]);
            if (!empty($error)) {
                $campoInvalido[$field] = $error;
            }
            if (isset($longCampo[$field])) {
                $error = validarLong($field, $data[$field], $longCampo[$field]);
                if (!empty($error)) {
                    $camposLongInvalida[$field] = $error;
                }
            }
        }
    }
    return $errores;
}

function validarExistenteDB($pdo, $tabla, $validarExistentes, $opcional = "") {
    $errores = [];
    foreach ($validarExistentes as $field => $value) {
        $sql = "SELECT * FROM $tabla WHERE $field = ?" . $opcional;
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$value]);
        if ($stmt->rowCount() > 0) {
            $errores[$field] = "El campo $field no debe repetirse";
        }
    }
    return $errores;
}