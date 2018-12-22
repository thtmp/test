<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property int $id
 * @property string $nickname
 * @property string $balance
 * @property string $authkey
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    /**
     * Maximum negative balance that is allowed for each user
     * @var integer
     */
    const MAX_NEGATIVE_BALANCE = 1000;

    /**
     * The scenario used to transfer balance between users
     * @var string
     */
    const SCENARIO_TRANSFER = 'transfer';

    /**
     * The amount to be transfered to another user in transfer scenario
     * @var float
     */
    public $transferAmount;

    /**
     * Target user's nickname to receive the balance in transfer scenario
     * @var string
     */
    public $targetUser;

    /**
     * An instance of the target user
     * @var User
     */
    private $targetUserInstance;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     * @see \yii\db\ActiveRecord::transactions()
     */
    public function transactions()
    {
        return [
            self::SCENARIO_TRANSFER => self::OP_ALL
        ];
    }

    /**
     *
     * {@inheritdoc}
     * @see \yii\base\Model::scenarios()
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        
        $scenarios[self::SCENARIO_TRANSFER] = ['nickname', 'balance', 'transferAmount', 'targetUser'];
        
        return $scenarios;
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['nickname', 'required'],
            ['authkey', 'required'],
            [['balance'], 'number'],
            [['nickname'], 'string', 'max' => 50],
            [['authkey'], 'string', 'max' => 32],
            [['nickname'], 'unique'],
            [['transferAmount', 'targetUser'], 'required', 'on' => [self::SCENARIO_TRANSFER]],
            ['transferAmount', 'number', 'min' => 0.01, 'max' => (self::MAX_NEGATIVE_BALANCE + $this->balance), 'on' => self::SCENARIO_TRANSFER],
            ['targetUser', 'validateTargetUser'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'nickname' => Yii::t('app', 'Nickname'),
            'balance' => Yii::t('app', 'Balance'),
            'authkey' => Yii::t('app', 'Authkey'),
            'transferAmount' => Yii::t('app', 'Transfer Amount'),
            'targetUser' => Yii::t('app', 'Target User'),
        ];
    }

    /**
     * Validates the targetUser.
     * This method serves as the inline validation for targetUser.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateTargetUser($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if ($this->targetUser == $this->nickname) {
                $this->addError($attribute, Yii::t('app', "You can't transfer balance to yourself!"));
            }
            
            if (! ($this->targetUserInstance = static::findByNickname($this->targetUser))) {
                $this->addError($attribute, Yii::t('app', '{targetUser} dose not exist!', [
                    'targetUser' => $this->getAttributeLabel($attribute),
                ]));
            }
        }
    }

    /**
     * {@inheritdoc}
     * @return UserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    /**
     * {@inheritDoc}
     * @see \yii\base\Model::beforeValidate()
     */
    public function beforeValidate()
    {
        if ($this->isNewRecord) {
            $this->authkey = \Yii::$app->security->generateRandomString(32);
        }
        
        return parent::beforeValidate();
    }

    /**
     * {@inheritdoc}
     * @see \yii\db\BaseActiveRecord::beforeSave()
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->scenario === self::SCENARIO_TRANSFER) {
                $this->balance -= $this->transferAmount;
                $this->targetUserInstance->balance += $this->transferAmount;
                if ($this->targetUserInstance->save()) {
                    return true;
                } else {
                    $this->balance += $this->transferAmount;
                    $this->targetUserInstance->balance -= $this->transferAmount;
                    return false;
                }
            }
            
            return true;
        }
        
        return false;
    }

    /**
     * Finds an identity by the given ID.
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface the identity object that matches the given ID.
     *         Null should be returned if such an identity cannot be found
     *         or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     *        For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface the identity object that matches the given token.
     *         Null should be returned if such an identity cannot be found
     *         or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|int an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     * The space of such keys should be big enough to defeat potential identity attacks.
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        return $this->authkey;
    }

    /**
     * Validates the given auth key.
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return bool whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Finds user by nickname
     *
     * @param string $nickname
     * @return static|null
     */
    public static function findByNickname($nickname)
    {
        return static::findOne(['nickname' => $nickname]);
    }
    
    /**
     * Username getter wich returns nickname as username
     * @return string
     */
    public function getUsername()
    {
        return $this->nickname;
    }
}
