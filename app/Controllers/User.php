<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;

class User extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        // CODE IGNITER 4

        // tu dois récupérer le token passé en authorization
        // décode le token en utilisant la lib firebase jwt php
        // tu récupères l'email de l'utilisateur

        $userModel = new UserModel;

        // tu fais une requête pour récupérer l'utilisateur en utilisant l'email avec UserModel

        // si l'utilisateur n'existe pas tu retournes une erreur 404 avec un message en anglais

        // tu retournes l'user
        return $this->respond(['user' => $userModel->find(array('email' => 'test@test.com'))], 200);
    }
}