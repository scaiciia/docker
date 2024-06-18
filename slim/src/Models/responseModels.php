<?php

function responseWithError($response, $error, $statusCode) {
    $payload = json_encode([
        'status' => 'failed',
        'code' => $statusCode,
        'error' => $error
    ]);
    $response->getBody()->write($payload);
    return $response->withStatus($statusCode);
}

function responseWithSuccess($response, $message, $statusCode) {
    $json = [
        'status' => 'success',
        'code' => $statusCode,
        'message' => $message
    ];
    $payload = json_encode($json);
    $response->getBody()->write($payload);
    return $response->withStatus($statusCode);
}

function responseWithData($response, $data, $statusCode, $warning = null) {
    $json = [
        'status' => 'success',
        'code' => $statusCode,
    ];
    if ((isset($warning)) && !(empty($warning))){
        $json['warning'] = $warning;
    }
    $json['data'] = $data;
    $payload = json_encode($json);
    $response->getBody()->write($payload);
    return $response->withStatus($statusCode);
}