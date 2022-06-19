<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property integer $member_id
 * @property string $family_id
 * @property string $password_hash
 * @property string $password_reset_token
 * @property integer $last_logged
 * @property string $ip_address
 * @property Family $family
 * @property Member $member
 */

class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    const ROLE_ADMIN = 1;
    const ROLE_USER = 2;
    const ROLE_RESELLER = 3;
    const ROLE_RESELLER_ADMIN = 4;

    public $authKey;
    public $accessToken;
    public $edit_pas;
    public $new_pass;
    public $confirm_pas;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password', 'role','email','account'], 'required'],
            [[ 'role'], 'integer'],
            [['username', 'password', 'password_reset_token'], 'string', 'max' => 255],
            [['password'], 'string', 'min' => 6],
            [['email'], 'string', 'max' => 100],
            [['account'], 'string', 'min' => 6,'max' => 50],
            ['email','email'],
            ['username','unique'],
            ['edit_pas','safe'],
            [['new_pass'], 'string', 'min' => 6],
            [['new_pass','confirm_pas'], 'required', 'on' => ['resetpass']],
            ['confirm_pas', 'compare', 'compareAttribute'=>'new_pass', 'message'=>"Passwords don't match" ],
        ];
    }

    public function getIdValue()
    {
        $count = Self::find()->count();
        $id = $count.rand (10000 , 99999);
        if (Self::find()->where(['id' => $id])->count() == 0) {
            return $id;
        } else {
            return $this->getIdValue();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password' => 'Password',
            'account' => 'Full Name',
            'password_reset_token' => 'Password Reset Token',
            'role' => 'Role',
            'new_pass' => 'New Password',
            'confirm_pas' => 'Confirm Password',
            'lastlogin' => 'Last Login',
            'userip' => 'User Last IP',
        ];
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */

    public static function findIdentity($id) {
        return static::findOne(['id' => $id, 'status']);
        // => self::STATUS_ACTIVE
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        return static::findOne(['auth_key' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByEmail($email) {
        return static::findOne(['email' => $email]);
    }
    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token) {

        return static::findOne([
            'password_reset_token' => $token
        ]);
    }

        /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
        public static function isPasswordResetTokenValid($token) {
          if (empty($token)) {
            return false;
        }
        $expire = Yii::$app->params['passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
    }
    /**
     * @inheritdoc
     */
    public function getId() {
      return $this->getPrimaryKey();
  }

    /**
     * @inheritdoc
     */
    public function getAuthKey() {
        //    return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey) {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password) {

        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password) {

        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates reseller id and sets it to the model
     *
     * @param string $id
     */
    public function setResellerId($id) {

        $this->reseller_id = $id;
    }

    public function changePassword($password) {
        return Yii::$app->security->generatePasswordHash($password);
    }

    public function removePasswordResetToken() {
        $this->password_reset_token = null;
    }

    public static function isUserAdmin($id) {
        if (static::findOne(['id' => $id, 'role' => (string)self::ROLE_ADMIN])) {
            return true;
        } else {
            return false;
        }
    }

    public static function isReseller($id) {
        if (static::findOne(['id' => $id, 'role' => (string)self::ROLE_RESELLER])) {
            return true;
        } else {
            return false;
        }
    }

    public static function isTestPanel($id) {
        if (static::findOne(['id' => $id, 'username' => 'TestPanel'])) {
            return true;
        } else {
            return false;
        }
    }

    public function generatePasswordResetToken() {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public static function isResellerAdmin($id) {
        if (static::findOne(['id' => $id, 'role' => (string)self::ROLE_RESELLER_ADMIN])) {
            return true;
        } else {
            return false;
        }
    }

}
