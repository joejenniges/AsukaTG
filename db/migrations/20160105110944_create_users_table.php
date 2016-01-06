<?php

use Phinx\Db\Adapter\AdapterInterface;
use Phinx\Migration\AbstractMigration;

class CreateUsersTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('users', ['id' => false, 'primary_key' => ['user_id']]);
        $table
            ->addColumn('user_id', AdapterInterface::PHINX_TYPE_INTEGER, [
                'null' => false,
                'limit' => 64
            ])
            ->addColumn('first_name', AdapterInterface::PHINX_TYPE_STRING, [
                'null' => false
            ])
            ->addColumn('last_name', AdapterInterface::PHINX_TYPE_STRING, [
                'default' => null
            ])
            ->addColumn('username', AdapterInterface::PHINX_TYPE_STRING, [
                'default' => null
            ])
            ->addIndex(['user_id'], ['unique' => true])
            ->create();
    }
}