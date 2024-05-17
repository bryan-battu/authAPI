<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFailedLoginAttemptsTable extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE failed_login_attempts (
                id SERIAL PRIMARY KEY,
                ip_address VARCHAR(255),
                attempt_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function down()
    {
        $this->forge->dropTable('failed_login_attempts');
    }
}
