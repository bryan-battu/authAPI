<?php

namespace App\Models;

use CodeIgniter\Model;

class FailedLoginAttemptModel extends Model
{
    protected $table = 'failed_login_attempts';
    protected $primaryKey = 'id';
    protected $allowedFields = ['ip_address', 'attempt_timestamp'];
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
}
