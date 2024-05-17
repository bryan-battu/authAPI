<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use CodeIgniter\Controller;

class Register extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $rules = [
            'login' => ['rules' => 'required|min_length[4]|max_length[255]|is_unique[users.login]'],
            'password' => ['rules' => 'required|min_length[8]|max_length[255]'],
            'roles' => ['rules' => 'required'],
            'status' => ['rules' => 'required|in_list[open,closed]'],
        ];

        if ($this->validate($rules)) {

            $model = new UserModel();
            $data = [
                'login'      => $this->request->getVar('login'),
                'password'   => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
                'roles'      => $this->request->getVar('roles'),
                'status'     => $this->request->getVar('status'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            try {
                $model->save($data);
            } catch (\ReflectionException $e) {
                var_dump($e->getMessage() . ' ' . $e->getLine());die;
            }

            $response = [
                'uid' => $model->getInsertID(),
                'login' => $data['login'],
                'roles' => $data['roles'],
                'status' => $data['status'],
                'created_at' => $data['created_at'],
                'updated_at' => $data['updated_at'],
            ];

            return $this->respondCreated($response);
        } else {
            $response = [
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ];
            return $this->fail($response, 409);
        }
    }
}
