<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Token extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        helper('token');
    }

    public function index() {
        $userModel = new UserModel();

        $login = $this->request->getVar('login');
        $password = $this->request->getVar('password');

        $user = $userModel->where('login', $login)->first();

        if(is_null($user)) {
            return $this->respond(['error' => 'Invalid username or password.'], 404);
        }

        $pwd_verify = password_verify($password, $user['password']);

        if(!$pwd_verify) {
            return $this->respond(['error' => 'Invalid username or password.'], 404);
        }

        session()->remove('failed_login_attempts');

        $key = getenv('JWT_SECRET');
        $iat = time();
        $exp = $iat + 3600;

        $payload = array(
            "iat" => $iat,
            "exp" => $exp,
            "login" => $user['login'],
            "roles" => $user['roles'],
            "status" => $user['status']
        );

        $token = JWT::encode($payload, $key, 'HS256');

        $payload['exp'] = $iat + 7200;

        $refreshToken = JWT::encode($payload, $key, 'HS256');

        $response = [
            'accessToken' => $token,
            'accessTokenExpiresAt' => $iat + 3600,
            'refreshToken' => $refreshToken,
            'refreshTokenExpiresAt' => $iat + 7200
        ];

        return $this->respond($response, 201);
    }

    public function validateToken($token) {
        if (!$token) {
            return $this->respond(['message' => 'Token not found'], 404);
        }

        $key = getenv('JWT_SECRET');

        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
        } catch (\Exception $e) {
            return $this->respond(['message' => 'Invalid token'], 404);
        }

        $response = [
            'accessToken' => $token,
            'accessTokenExpiresAt' => $decoded->exp
        ];

        return $this->respond($response, 200);
    }

    public function refreshToken($refreshToken)
    {
        if (!$refreshToken) {
            return $this->respond(['message' => 'Token not found'], 404);
        }

        $key = getenv('JWT_SECRET');

        try {
            $decoded = JWT::decode($refreshToken, new Key($key, 'HS256'));
        } catch (\Exception $e) {
            return $this->respond(['message' => 'Invalid token'], 404);
        }

        $iat = time();
        $exp = $iat + 3600;

        $payload = array(
            "iat" => $iat,
            "exp" => $exp,
            "login" => $decoded->login,
            "roles" => $decoded->roles,
            "status" => $decoded->status
        );

        $token = JWT::encode($payload, $key, 'HS256');

        $payload['exp'] = $iat + 7200;

        $refreshToken = JWT::encode($payload, $key, 'HS256');

        $response = [
            'accessToken' => $token,
            'accessTokenExpiresAt' => $iat + 3600,
            'refreshToken' => $refreshToken,
            'refreshTokenExpiresAt' => $iat + 7200
        ];

        return $this->respond($response, 201);
    }
}