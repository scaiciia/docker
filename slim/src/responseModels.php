<?php

function responseWithError($response, $error, $statusCode) {
    $payload = json_encode([
        'error' => $error,
        'code' => $statusCode
    ]);
    $response->getBody()->write($payload);
    return $response->withStatus($statusCode)->withHeader('Content-Type', 'application/json');
}

function responseWithSuccess($response, $message, $statusCode) {
    $payload = json_encode([
        'message' => $message,
        'code' => $statusCode
    ]);
    $response->getBody()->write($payload);
    return $response->withStatus($statusCode)->withHeader('Content-Type', 'application/json');
}

function responseWithData($response, $data, $statusCode) {
    $payload = json_encode([
        'status' => 'success',
        'code' => $statusCode,
        'data' => $data
    ]);
    $response->getBody()->write($payload);
    return $response->withStatus($statusCode)->withHeader('Content-Type', 'application/json');
}