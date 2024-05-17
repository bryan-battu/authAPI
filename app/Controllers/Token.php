<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;

class Token extends BaseController
{
    use ResponseTrait;
    public function refreshToken(): \CodeIgniter\HTTP\ResponseInterface
    {
        $email = $this->request->getHeaderLine('email');

        if (!$email) {
            return $this->respond(['message' => 'Email not found in the token'], 401);
        }

        $key = getenv('JWT_SECRET');
        $iat = time();
        $exp = $iat + 3600;

        $payload = array(
            "iat" => $iat,
            "exp" => $exp,
            "email" => $email
        );

        $token = JWT::encode($payload, $key, 'HS256');

        $response = [
            'message' => 'Token sucessfully refreshed',
            'token' => $token
        ];

        return $this->respond($response, 200);
    }
}