<?php

namespace tests\models;

use app\models\User;

class UserTest extends \Codeception\Test\Unit
{
    private $user;

    protected function _before()
    {
        \Yii::$app->db->createCommand('TRUNCATE ' . User::tableName())->execute();
        $this->tester->haveRecord(User::className(), ['id' => 1, 'nickname' => 'user1', 'authkey' => 'authkey1']);
    }

    public function testFindUserById()
    {        
        expect_that($user = User::findIdentity(1));
        expect($user->username)->equals('user1');

        expect_not(User::findIdentity(10));
    }

    public function testFindUserByNickname()
    {
        expect_that(User::findByNickname('user1'));
        expect_not(User::findByNickname('invalid-user'));
    }

    public function testValidateInitialBalance()
    {
        expect_that(User::findByNickname('user1')->balance == 0);
    }

    /**
     * @depends testFindUserByNickname
     */
    public function testValidateUser($user)
    {
        $user = User::findByNickname('user1');
        expect_that($user->validateAuthKey('authkey1'));
        expect_not($user->validateAuthKey('invalid-authkey'));
    }

    /**
     * @depends testFindUserByNickname
     */
    public function testZeroAmountTransfer($user)
    {
        $this->tester->haveRecord(User::className(), ['id' => 2, 'nickname' => 'user2', 'authkey' => 'authkey2']);
        
        $user = User::findByNickname('user1');
        $user->scenario = User::SCENARIO_TRANSFER;
        $user->transferAmount = 0;
        $user->targetUser = 'user2';
        expect_not($user->save());
    }

    /**
     * @depends testFindUserByNickname
     */
    public function testNegativeAmountTransfer($user)
    {
        $this->tester->haveRecord(User::className(), ['id' => 2, 'nickname' => 'user2', 'authkey' => 'authkey2']);
        
        $user = User::findByNickname('user1');
        $user->scenario = User::SCENARIO_TRANSFER;
        $user->transferAmount = -0.01;
        $user->targetUser = 'user2';
        expect_not($user->save());
    }

    /**
     * @depends testFindUserByNickname
     */
    public function testGreaterAmountTransfer($user)
    {
        $this->tester->haveRecord(User::className(), ['id' => 2, 'nickname' => 'user2', 'authkey' => 'authkey2']);
        
        $user = User::findByNickname('user1');
        $user->scenario = User::SCENARIO_TRANSFER;
        $user->transferAmount = 1000.01;
        $user->targetUser = 'user2';
        expect_not($user->save());
    }

    /**
     * @depends testFindUserByNickname
     */
    public function testTransferToNonExistanceUser($user)
    {
        $this->tester->haveRecord(User::className(), ['id' => 2, 'nickname' => 'user2', 'authkey' => 'authkey2']);
        
        $user = User::findByNickname('user1');
        $user->scenario = User::SCENARIO_TRANSFER;
        $user->transferAmount = 500;
        $user->targetUser = 'invalid-user';
        expect_not($user->save());
    }
    
    /**
     * @depends testFindUserByNickname
     */
    public function testValidTransfer($user)
    {
        $this->tester->haveRecord(User::className(), ['id' => 2, 'nickname' => 'user2', 'authkey' => 'authkey2']);
        
        $user = User::findByNickname('user1');
        $user->scenario = User::SCENARIO_TRANSFER;
        $user->transferAmount = 500;
        $user->targetUser = 'user2';
        expect_that($user->save());
        
        expect(User::findByNickname('user2')->balance)->equals(500);
    }
    
    /**
     * @depends testFindUserByNickname
     */
    public function testTransferTransaction($user)
    {
        $this->tester->haveRecord(User::className(), ['id' => 2, 'nickname' => 'user2', 'authkey' => 'authkey2']);
        
        $user = User::findByNickname('user1');
        $user->scenario = User::SCENARIO_TRANSFER;
        $user->transferAmount = 500;
        $user->targetUser = 'user2';
        
        User::findByNickname('user1')->updateAttributes(['balance' => -500.01]);
        
        expect_not($user->save());
        expect(User::findByNickname('user2')->balance)->equals(0);
    }
}
