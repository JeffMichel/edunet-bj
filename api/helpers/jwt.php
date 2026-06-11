<?php
// Gestion et vérification JWT manuel

function jwt_generate($payload) {
    $header = base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload['iat'] = time();
    $payload['exp'] = time() + JWT_EXPIRES_IN;
    $payload_encoded = base64url_encode(json_encode($payload));
    $signature = base64url_encode(hash_hmac('sha256', "$header.$payload_encoded", JWT_SECRET, true));
    return "$header.$payload_encoded.$signature";
}

function jwt_verify($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;
    [$header, $payload, $signature] = $parts;
    $expected = base64url_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    if (!hash_equals($expected, $signature)) return false;
    $data = json_decode(base64url_decode($payload), true);
    if (!isset($data['exp']) || $data['exp'] < time()) return false;
    return $data;
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}
