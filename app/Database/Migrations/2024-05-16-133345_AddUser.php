<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUser extends Migration
{
    public function up()
    {
        // Ajout des types ENUM pour PostgreSQL
        $this->db->query("CREATE TYPE user_status AS ENUM ('open', 'closed');");

        // Définition de la table users
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 255,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'login' => [
                'type' => 'VARCHAR',
                'unique' => true,
                'constraint' => '255',
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'roles' => [
                'type' => 'VARCHAR',
                'constraint' => '500',
            ],
            'status' => [
                'type' => 'user_status',
                'default' => 'open',
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
        $this->db->query("DROP TYPE IF EXISTS user_status;");
    }
}
