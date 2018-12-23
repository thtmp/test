<?php

use app\models\User;

class TransferCest
{
    public function _before(\FunctionalTester $I)
    {
        \Yii::$app->db->createCommand('TRUNCATE ' . User::tableName())->execute();
        $I->haveRecord(User::className(), ['id' => 1, 'nickname' => 'user1', 'authkey' => 'authkey1']);
        $I->haveRecord(User::className(), ['id' => 2, 'nickname' => 'user2', 'authkey' => 'authkey2']);
        
        $I->amOnRoute('site/login');
        $I->submitForm('#login-form', [
            'LoginForm[nickname]' => 'user1',
        ]);
        $I->see('Logout (user1)');
    }

    public function emptyTransferForm(\FunctionalTester $I)
    {
        $I->amOnRoute('user/transfer');
        $I->submitForm('#transfer-form', []);
        $I->expectTo('see validations errors');
        $I->see('Transfer Amount cannot be blank.');
        $I->see('Target User cannot be blank.');
    }
    
    public function zeroAmountTransfer(\FunctionalTester $I)
    {
        $I->amOnRoute('user/transfer');
        $I->submitForm('#transfer-form', [
            'User[transferAmount]' => 0
        ]);
        $I->expectTo('see validations errors');
        $I->see('Transfer Amount must be no less than 0.01.');
    }
    
    public function negativeAmountTransfer(\FunctionalTester $I)
    {
        $I->amOnRoute('user/transfer');
        $I->submitForm('#transfer-form', [
            'User[transferAmount]' => -0.01
        ]);
        $I->expectTo('see validations errors');
        $I->see('Transfer Amount must be no less than 0.01.');
    }
    
    public function greaterAmountTransfer(\FunctionalTester $I)
    {
        $I->amOnRoute('user/transfer');
        $I->submitForm('#transfer-form', [
            'User[transferAmount]' => 1000.01
        ]);
        $I->expectTo('see validations errors');
        $I->see('Transfer Amount must be no greater than 1000.');
    }
    
    public function transferToNonExistanceUser(\FunctionalTester $I)
    {
        $I->amOnRoute('user/transfer');
        $I->submitForm('#transfer-form', [
            'User[transferAmount]' => 500,
            'User[targetUser]' => 'invalid-user',
        ]);
        $I->expectTo('see validations errors');
        $I->see('Target User dose not exist!');
    }
    
    public function validTransfer(\FunctionalTester $I)
    {
        $I->amOnRoute('user/transfer');
        $I->submitForm('#transfer-form', [
            'User[transferAmount]' => 500,
            'User[targetUser]' => 'user2',
        ]);
        $I->expectTo('see successfull message');
        $I->see('Transaction completed successfully.');
        
        Yii::$app->user->logout();
        
        $I->amOnRoute('site/login');
        $I->submitForm('#login-form', [
            'LoginForm[nickname]' => 'user2',
        ]);
        $I->see('Logout (user2)');
        $I->amOnRoute('user/transfer');
        $I->see('Current balance: 500.00');
    }
    
    public function transferAllBalance(\FunctionalTester $I)
    {
        $I->amOnRoute('user/transfer');
        $I->submitForm('#transfer-form', [
            'User[transferAmount]' => 1000,
            'User[targetUser]' => 'user2',
        ]);
        $I->expectTo('see successfull message');
        $I->see('Transaction completed successfully.');
        $I->amOnRoute('user/transfer');
        $I->expectTo('see insufficient balance error');
        $I->see("Sorry, you don't have sufficient balance to transfer.");
    }
}