<?php

namespace App\Controllers;
use App\Models\UserModel;
use CodeIgniter\RESTful\BaseController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use ResponseTrait;


function decodeJWT($token)
{
    $key = getenv('JWT_SECRET_KEY');

    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        return (array) $decoded;
    } catch (Exception $e) {

        return null;
    }
}

class User extends BaseController
{

    protected $modelName = 'App\Models\UserModel';
    protected $format    = 'json';

    public function index()
    {

        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader) {
            return $this->respond(['message' => 'Missing Token'], 401);
        }

        list($type, $token) = explode(' ', $authHeader);

        if (strtolower($type) !== 'bearer') {
            return $this->respond(['message' => 'Token type invalid'], 401);
        }

        $decoded = decodeJWT($token);

        if (!$decoded) {
            return $this->respond(['message' => 'Token invalid'], 401);
        }

        $email = $decoded['email'] ?? null;

        if (!$email) {
            return $this->respond(['message' => 'Email not found in the token'], 401);
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (!$user) {
            return $this->respond(['message' => 'User not found'], 404);
        }

        return $this->respond($user);
    }

}