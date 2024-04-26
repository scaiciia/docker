<?php

function responseWithError($response, $error, $statusCode) {
    $payload = json_encode([
        'status' => 'failed',
        'code' => $statusCode,
        'error' => $error
    ]);
    $response->getBody()->write($payload);
    return $response->withStatus($statusCode)->withHeader('Content-Type', 'application/json');
}

function responseWithSuccess($response, $message, $statusCode) {
    $payload = json_encode([
        'status' => 'success',
        'code' => $statusCode,
        'message' => $message
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