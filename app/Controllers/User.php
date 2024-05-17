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

    public function index($id)
    {
        $uid = $this->request->getHeaderLine('uid');

        $userModel = new UserModel();
        $user = $userModel->where('id', $uid)->first();

        if (!$user) {
            return $this->respond(['message' => 'User not found'], 404);
        }

        if (!in_array('ROLE_ADMIN', explode(',', $user['roles']))) {
            $response = [
                'uid' => $user['uid'],
                'login' => $user['login'],
                'roles' => $user['roles'],
                'status' => $user['status'],
                'created_at' => $user['created_at'],
                'updated_at' => $user['updated_at'],
            ];

            return $this->respond($response);
        } else {
            if ($id == 'me') {
                $response = [
                    'uid' => $user['uid'],
                    'login' => $user['login'],
                    'roles' => $user['roles'],
                    'status' => $user['status'],
                    'created_at' => $user['created_at'],
                    'updated_at' => $user['updated_at'],
                ];

                return $this->respond($response);
            } else {
                $user = $userModel->where('id', $id)->first();

                if (!$user) {
                    return $this->respond(['message' => 'User not found'], 404);
                }

                $response = [
                    'uid' => $user['uid'],
                    'login' => $user['login'],
                    'roles' => $user['roles'],
                    'status' => $user['status'],
                    'created_at' => $user['created_at'],
                    'updated_at' => $user['updated_at'],
                ];

                return $this->respond($response);
            }
        }
    }

    public function edit($id) {
        $rules = [
            'login' => ['rules' => 'required|min_length[4]|max_length[255]|is_unique[users.login]'],
            'password' => ['rules' => 'required|min_length[8]|max_length[255]'],
            'roles' => ['rules' => 'required'],
            'status' => ['rules' => 'required|in_list[open,closed]'],
        ];

        $uid = $this->request->getHeaderLine('uid');

        $userModel = new UserModel();

        $data = [
            'login'      => $this->request->getVar('login'),
            'password'   => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'roles'      => $this->request->getVar('roles'),
            'status'     => $this->request->getVar('status'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $tokenUser = $userModel->where('id', $uid)->first();

        if (!$tokenUser) {
            return $this->fail('User not found', 404);
        }

        if (!in_array('ROLE_ADMIN', explode(',', $tokenUser['roles']))) {
            unset($data['roles']);

            if($uid != $id && $id != 'me') {
                return $this->fail('Access denied', 401);
            }
        }

        $user = $userModel->where('id', $id)->first();

        if (!$user) {
            return $this->fail('User not found', 409);
        }

        if ($this->validate($rules)) {
            $userModel->update($user['id'], $data);

            $user = $userModel->where('id', $id)->first();

            $response = [
                'uid' => $user['uid'],
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