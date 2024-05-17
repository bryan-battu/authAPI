<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBlockedUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'blocked_until' => [
                'type' => 'TIMESTAMP',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('blocked_users');
    }

    public function down()
    {
        $this->forge->dropTable('blocked_users');
    }
}
