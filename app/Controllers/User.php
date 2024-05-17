<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;
use Firebase\JWT\JWT;

class User extends BaseController
{
    use ResponseTrait;
    protected $format    = 'json';

    public function index()
    {
        $email = $this->request->getHeaderLine('email');

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

    public function editUser() {
        $email = $this->request->getHeaderLine('email');

        if (!$email) {
            return $this->respond(['message' => 'Email not found in the token'], 401);
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (!$user) {
            return $this->respond(['message' => 'User not found'], 404);
        }

        $rules = [
            'email' => ['rules' => 'required|min_length[4]|max_length[255]|valid_email|is_unique[users.email]']
        ];

        if($this->validate($rules)){
            $data = [
                'email'    => $this->request->getVar('email'),
            ];
            $userModel->update($user['id'], $data);

            // create new token
            $key = getenv('JWT_SECRET');
            $iat = time();
            $exp = $iat + 3600;

            $payload = array(
                "iat" => $iat,
                "exp" => $exp,
                "email" => $this->request->getVar('email'),
            );

            $token = JWT::encode($payload, $key, 'HS256');

            $response = [
                'message' => 'User updated successfully',
                'token' => $token
            ];

            return $this->respond($response, 200);
        }else{
            $response = [
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ];
            return $this->fail($response , 409);
        }
    }
}