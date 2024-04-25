<?php

function validationFields(array $data,array $requiredFields, $response){
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
        }
        return empty($arr);
}