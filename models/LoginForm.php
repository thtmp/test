<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $nickname;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // nickname is required
            ['nickname', 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // nickname is validated by validateNickname()
            ['nickname', 'validateNickname'],
        ];
    }

    /**
     * Validates the nickname.
     * This method serves as the inline validation for nickname.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateNickname($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (! $this->getUser()) {
                $this->_user = false;
                $user = new User();
                $user->nickname = $this->nickname;
                if (! $user->save()) {
                    $this->addErrors($user->getErrors());
                }
            }
        }
    }

    /**
     * Logs in a user using the provided nickname.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

    /**
     * Finds user by [[nickname]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByNickname($this->nickname);
        }

        return $this->_user;
    }
}
