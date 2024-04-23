<?php

function validarCampoVacio($data, $requiredFields) {
    $errores = [];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            $errores[$field] = "El campo $field es requerido";
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