<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BlockedUserModel;
use App\Models\FailedLoginAttemptModel;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use DateTime;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Token extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        helper('token');
    }

    public function index()
    {
        $userModel = new UserModel();

        $login = $this->request->getVar('login');
        $password = $this->request->getVar('password');

        $ipAddress = $this->request->getIPAddress();
        $blockedUntil = $this->getBlockedUntilTime($ipAddress);

        if ($blockedUntil > time()) {
            $remainingTime = $blockedUntil - time();
            return $this->respond(['error' => 'Your account is blocked. Please try again later.', 'Seconds before new try' => $remainingTime], 403);
        }

        $failedAttemptsCount = $this->getFailedLoginAttemptsCount($ipAddress);

        if ($failedAttemptsCount >= 3) {
            $this->blockUser($ipAddress);
            return $this->respond(['error' => 'Your account is blocked. Please try again later.', 'Seconds before new try' => 1800], 403);
        }

        $user = $userModel->where('login', $login)->first();

        if (is_null($user)) {
            $this->recordFailedLoginAttempt($this->request->getIPAddress());
            return $this->respond(['error' => 'Invalid login or password.'], 404);
        }

        if ($user['status'] == 'closed') {
            return $this->respond(['error' => 'Your account has to be opened'], 403);
        }

        $pwd_verify = password_verify($password, $user['password']);

        if (!$pwd_verify) {
            $this->recordFailedLoginAttempt($this->request->getIPAddress());
            return $this->respond(['error' => 'Invalid login or password.'], 404);
        }

        $this->clearFailedLoginAttempts($this->request->getIPAddress());

        // Générez et retournez le token JWT
        $key = getenv('JWT_SECRET');
        $iat = time();
        $exp = $iat + 3600;

        $payload = array(
            "iat" => $iat,
            "exp" => $exp,
            "uid" => $user['id'],
            "login" => $user['login'],
            "roles" => $user['roles'],
            "status" => $user['status'],
            "created_at" => $user['created_at']
        );

        $token = JWT::encode($payload, $key, 'HS256');

        $payload['exp'] = $iat + 7200;

        $refreshToken = JWT::encode($payload, $key, 'HS256');

        $accessTokenExpiresAtTimestamp = $iat + 3600;
        $accessTokenExpiresAtDateTime = new DateTime();
        $accessTokenExpiresAtDateTime->setTimestamp($accessTokenExpiresAtTimestamp);
        $accessTokenExpiresAtFormatted = $accessTokenExpiresAtDateTime->format('Y-m-d\TH:i:s\Z');

        $refreshTokenExpiresAtTimestamp = $iat + 7200;
        $refreshTokenExpiresAtDateTime = new DateTime();
        $refreshTokenExpiresAtDateTime->setTimestamp($refreshTokenExpiresAtTimestamp);
        $refreshTokenExpiresAtFormatted = $refreshTokenExpiresAtDateTime->format('Y-m-d\TH:i:s\Z');

        $response = [
            'accessToken' => $token,
            'accessTokenExpiresAt' => $accessTokenExpiresAtFormatted,
            'refreshToken' => $refreshToken,
            'refreshTokenExpiresAt' => $refreshTokenExpiresAtFormatted
        ];

        return $this->respond($response, 201);
    }

    private function blockUser($ipAddress): void
    {
        $blockedUntil = time() + (30 * 60);

        $blockedUserModel = new BlockedUserModel();
        $blockedUserModel->insert(['ip_address' => $ipAddress, 'blocked_until' => date('Y-m-d H:i:s', $blockedUntil)]);
    }

    private function getBlockedUntilTime($ipAddress): bool|int
    {
        $blockedUserModel = new BlockedUserModel();
        $result = $blockedUserModel->where('ip_address', $ipAddress)
            ->orderBy('id', 'DESC')
            ->first();
        if ($result) {
            return strtotime($result['blocked_until']);
        }
        return 0;
    }

    private function getFailedLoginAttemptsCount($ipAddress): int|string
    {
        $failedLoginModel = new FailedLoginAttemptModel();
        $currentTimestamp = time();
        $fiveMinutesAgoTimestamp = $currentTimestamp - (5 * 60);
        return $failedLoginModel->where('ip_address', $ipAddress)
            ->where('attempt_timestamp >', date('Y-m-d H:i:s', $fiveMinutesAgoTimestamp))
            ->countAllResults();
    }

    private function recordFailedLoginAttempt($ipAddress): void
    {
        $failedLoginModel = new FailedLoginAttemptModel();
        $failedLoginModel->insert(['ip_address' => $ipAddress]);
    }

    private function clearFailedLoginAttempts($ipAddress)
    {
        $failedLoginModel = new FailedLoginAttemptModel();
        $failedLoginModel->where('ip_address', $ipAddress)->delete();
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

        $accessTokenExpiresAtDateTime = new DateTime();
        $accessTokenExpiresAtDateTime->setTimestamp($decoded->exp);
        $accessTokenExpiresAtFormatted = $accessTokenExpiresAtDateTime->format('Y-m-d\TH:i:s\Z');

        $response = [
            'accessToken' => $token,
            'accessTokenExpiresAt' => $accessTokenExpiresAtFormatted
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

        $accessTokenExpiresAtTimestamp = $iat + 3600;
        $accessTokenExpiresAtDateTime = new DateTime();
        $accessTokenExpiresAtDateTime->setTimestamp($accessTokenExpiresAtTimestamp);
        $accessTokenExpiresAtFormatted = $accessTokenExpiresAtDateTime->format('Y-m-d\TH:i:s\Z');

        $refreshTokenExpiresAtTimestamp = $iat + 7200;
        $refreshTokenExpiresAtDateTime = new DateTime();
        $refreshTokenExpiresAtDateTime->setTimestamp($refreshTokenExpiresAtTimestamp);
        $refreshTokenExpiresAtFormatted = $refreshTokenExpiresAtDateTime->format('Y-m-d\TH:i:s\Z');

        $response = [
            'accessToken' => $token,
            'accessTokenExpiresAt' => $accessTokenExpiresAtFormatted,
            'refreshToken' => $refreshToken,
            'refreshTokenExpiresAt' => $refreshTokenExpiresAtFormatted
        ];

        return $this->respond($response, 201);
    }
}