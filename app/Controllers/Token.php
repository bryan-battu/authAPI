<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;

class Token extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        helper('token');
    }

    public function refreshToken(): \CodeIgniter\HTTP\ResponseInterface
    {
        $email = $this->request->getHeaderLine('email');

        if (!$email) {
            return $this->respond(['message' => 'Email not found in the token'], 401);
        }

        $token = createToken($email);

        $response = [
            'message' => 'Token sucessfully refreshed',
            'token' => $token
        ];

        return $this->respond($response, 200);
    }
}