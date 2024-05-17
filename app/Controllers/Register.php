<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;


class Register extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $rules = [
            'email' => ['rules' => 'required|min_length[4]|max_length[255]|valid_email|is_unique[users.email]'],
            'password' => ['rules' => 'required|min_length[8]|max_length[255]'],
            'confirmed_password'  => [ 'label' => 'confirmed password', 'rules' => 'matches[password]']
        ];

        if($this->validate($rules)){
            $model = new UserModel();
            $data = [
                'email'    => $this->request->getVar('email'),
                'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s')
            ];
            $model->save($data);

            $key = getenv('JWT_SECRET');
            $iat = time();
            $exp = $iat + 3600;

            $payload = array(
                "iat" => $iat,
                "exp" => $exp,
                "email" => $data['email'],
            );

            $token = \Firebase\JWT\JWT::encode($payload, $key, 'HS256');

            $response = [
                'message' => 'User registered successfully',
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