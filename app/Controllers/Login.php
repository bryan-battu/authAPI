<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;

class Login extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        helper('token');
    }

    public function index()
    {

        $userModel = new UserModel();

        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        $failedLoginAttempts = session()->get('failed_login_attempts') ?? 0;

        if ($failedLoginAttempts >= 5) {
            return $this->respond(['error' => 'Too many failed login attempts. Please try again later.'], 401);
        }

        $user = $userModel->where('email', $email)->first();

        if(is_null($user)) {
            session()->set('failed_login_attempts', $failedLoginAttempts + 1);

            return $this->respond(['error' => 'Invalid username or password.'], 401);
        }

        $pwd_verify = password_verify($password, $user['password']);

        if(!$pwd_verify) {
            session()->set('failed_login_attempts', $failedLoginAttempts + 1);

            return $this->respond(['error' => 'Invalid username or password.'], 401);
        }

        session()->remove('failed_login_attempts');

        $token = createToken($email);

        $response = [
            'message' => 'Login Succesful',
            'token' => $token
        ];

        return $this->respond($response, 200);
    }

}