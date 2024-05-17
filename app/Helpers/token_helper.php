<?php

use Firebase\JWT\JWT;

function createToken($email) {
    $key = getenv('JWT_SECRET');
    $iat = time();
    $exp = $iat + 3600;

    $payload = array(
        "iat" => $iat,
        "exp" => $exp,
        "email" => $email
    );

    return JWT::encode($payload, $key, 'HS256');
}