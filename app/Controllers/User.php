<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;
use Firebase\JWT\JWT;

class User extends BaseController
{
    use ResponseTrait;

    protected $format = 'json';

    public function __construct()
    {
        helper('token');
    }

    public function index($id)
    {
        $uid = $this->request->getHeaderLine('uid');

        $userModel = new UserModel();
        $tokenUser = $userModel->where('id', $uid)->first();

        if (!$tokenUser) {
            return $this->respond(['message' => 'User not found'], 404);
        }

        if (!in_array('ROLE_ADMIN', explode(',', $tokenUser['roles']))) {
            if ($uid != $id && $id != 'me') {
                return $this->fail('You have to be admin or account\'s owner', 403);
            }
        }

        if ($id == 'me') {
            $response = [
                'uid' => $tokenUser['id'],
                'login' => $tokenUser['login'],
                'roles' => $tokenUser['roles'],
                'status' => $tokenUser['status'],
                'created_at' => $tokenUser['created_at'],
                'updated_at' => $tokenUser['updated_at'],
            ];

            return $this->respond($response);
        } else {
            $user = $userModel->where('id', $id)->first();

            if (!$user) {
                return $this->respond(['message' => 'User not found'], 404);
            }

            $response = [
                'uid' => $user['id'],
                'login' => $user['login'],
                'roles' => $user['roles'],
                'status' => $user['status'],
                'created_at' => $user['created_at'],
                'updated_at' => $user['updated_at'],
            ];

            return $this->respond($response);
        }
    }

    public function edit($id)
    {
        $rules = [];
        $uid = $this->request->getHeaderLine('uid');

        $userModel = new UserModel();

        $data = [
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if($this->request->getVar('login') !== null) {
            $rules['login'] = ['rules' => 'min_length[4]|max_length[255]'];
            $data['login'] = $this->request->getVar('login');
        }

        if($this->request->getVar('roles') !== null) {
            $rules['roles'] = ['rules' => 'required'];
            $data['roles'] = $this->request->getVar('roles');
        }

        if($this->request->getVar('password') !== null) {
            $rules['password'] = ['rules' => 'min_length[8]|max_length[255]'];
            $data['password'] = password_hash($this->request->getVar('password'), PASSWORD_DEFAULT);
        }

        if($this->request->getVar('status') !== null) {
            $rules['status'] = ['rules' => 'in_list[open,closed]'];
            $data['status'] = $this->request->getVar('status');
        }

        $tokenUser = $userModel->where('id', $uid)->first();

        if (!$tokenUser) {
            return $this->fail('User not found', 404);
        }

        if (!in_array('ROLE_ADMIN', explode(',', $tokenUser['roles']))) {
            if(isset($data['roles'])) {
                unset($data['roles']);
            }

            if ($uid != $id && $id != 'me') {
                return $this->fail('You have to be admin or account\'s owner', 403);
            }
        }

        if ($id == 'me') $id = $uid;

        $user = $userModel->where('id', $id)->first();

        if (!$user) {
            return $this->fail('User not found', 409);
        }

        if ($this->validate($rules)) {
            $userModel->update($user['id'], $data);

            $user = $userModel->where('id', $id)->first();

            $response = [
                'uid' => $user['id'],
                'login' => $user['login'],
                'roles' => $user['roles'],
                'status' => $user['status'],
                'created_at' => $user['created_at'],
                'updated_at' => $user['updated_at'],
            ];

            return $this->respond($response);
        } else {
            $response = [
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ];
            return $this->fail($response, 409);
        }
    }
}