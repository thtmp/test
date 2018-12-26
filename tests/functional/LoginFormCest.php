<?php

use app\models\User;

class LoginFormCest
{
    public function _before(\FunctionalTester $I)
    {
        \Yii::$app->db->createCommand('TRUNCATE ' . User::tableName())->execute();
        $I->haveRecord(User::className(), ['id' => 1, 'nickname' => 'user1', 'authkey' => 'authkey1']);
        
        $I->amOnRoute('site/login');
    }

    public function openLoginPage(\FunctionalTester $I)
    {
        $I->see('Login', 'h1');
    }

    // demonstrates `amLoggedInAs` method
    public function internalLoginById(\FunctionalTester $I)
    {
        $I->amLoggedInAs(1);
        $I->amOnPage('/');
        $I->see('Logout (user1)');
    }

    // demonstrates `amLoggedInAs` method
    public function internalLoginByInstance(\FunctionalTester $I)
    {
        $I->amLoggedInAs(\app\models\User::findByNickname('user1'));
        $I->amOnPage('/');
        $I->see('Logout (user1)');
    }

    public function loginWithEmptyCredentials(\FunctionalTester $I)
    {
        $I->submitForm('#login-form', []);
        $I->expectTo('see validations errors');
        $I->see('Nickname cannot be blank.');
    }

    public function loginWithInvalidNickname(\FunctionalTester $I)
    {
        $I->submitForm('#login-form', [
            'LoginForm[nickname]' => Yii::$app->security->generateRandomString(51),
        ]);
        $I->expectTo('see validations errors');
        $I->see('Nickname should contain at most 50 characters.');
    }

    public function loginWithExsitingNickname(\FunctionalTester $I)
    {
        $I->submitForm('#login-form', [
            'LoginForm[nickname]' => 'user1',
        ]);
        $I->see('Logout (user1)');
        $I->dontSeeElement('form#login-form');              
    }

    public function loginWithNonExistanceNickname(\FunctionalTester $I)
    {
        $I->submitForm('#login-form', [
            'LoginForm[nickname]' => 'user2',
        ]);
        $I->see('Logout (user2)');
        $I->dontSeeElement('form#login-form');
    }
}