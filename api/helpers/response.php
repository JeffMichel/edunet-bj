<?php
// Fonctions de réponse JSON formatées

function json_success($message, $data = [], $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

function json_error($message, $code = 400, $data = null) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}
