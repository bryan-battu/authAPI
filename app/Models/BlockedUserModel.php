<?php

namespace App\Models;

use CodeIgniter\Model;

class BlockedUserModel extends Model
{
    protected $table = 'blocked_users';

    protected $primaryKey = 'id';

    protected $allowedFields = ['ip_address', 'blocked_until'];

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useTimestamps = false;
}
