<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user`.
 */
class m181222_134519_create_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user', [
            'id' => $this->bigPrimaryKey(),
            'nickname' => $this->string(50)
                ->notNull()
                ->unique(),
            'balance' => $this->decimal(65, 2)
                ->notNull()
                ->defaultValue(0),
            'authkey' => $this->char(32)
                ->notNull()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('user');
    }
}
