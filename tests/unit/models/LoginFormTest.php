<?php

namespace tests\models;

use app\models\LoginForm;

class LoginFormTest extends \Codeception\Test\Unit
{
    private $model;

    protected function _after()
    {
        \Yii::$app->user->logout();
    }

    public function testLoginCorrect()
    {
        $this->model = new LoginForm([
            'nickname' => \Yii::$app->security->generateRandomString(50),
        ]);

        expect_that($this->model->login());
        expect_not(\Yii::$app->user->isGuest);
        expect($this->model->errors)->hasntKey('nickname');
    }

    public function testLoginInvalid()
    {
        $this->model = new LoginForm([
            'nickname' => \Yii::$app->security->generateRandomString(51),
        ]);
        
        expect_not($this->model->login());
        expect_that(\Yii::$app->user->isGuest);
        expect($this->model->errors)->hasKey('nickname');
    }
}
