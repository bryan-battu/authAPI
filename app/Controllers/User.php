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

    public function __construct()
    {
        helper('token');
    }

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

            $token = createToken($data['email']);

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

    public function modifyPassword() {
        $email = $this->request->getHeaderLine('email');
        $oldPassword = $this->request->getVar('old_password');

        if (!$email) {
            return $this->respond(['message' => 'Email not found in the token'], 401);
        }

        if (!$oldPassword) {
            return $this->respond(['message' => 'Email or old password not found'], 401);
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (!$user) {
            return $this->respond(['message' => 'User not found'], 404);
        }

        if (!password_verify($oldPassword, $user['password'])) {
            return $this->respond(['message' => 'Incorrect old password'], 401);
        }

        $rules = [
            'password' => ['rules' => 'required|min_length[8]|max_length[255]'],
            'confirmed_password'  => [ 'label' => 'confirmed password', 'rules' => 'matches[password]']
        ];

        if($this->validate($rules)){
            $data = [
                'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            ];
            $userModel->update($user['id'], $data);

            $response = [
                'message' => 'Password updated successfully'
            ];

            return $this->respond($response, 200);
        } else {
            $response = [
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ];
            return $this->fail($response , 409);
        }
    }

}