<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "fsadmintb".
 *
 * @property int $fsuid
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $cld1
 */
class Fsadmintb extends \yii\db\ActiveRecord
{
    public $user_name;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'fsadmintb';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['admin_id','cld1'],'required'],
            [['admin_id'], 'integer'],
            [['assigned_date','closing_date'], 'safe'],
            //[['cld1'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'admin_id' => 'Reseller Admin',
            'cld1' => 'Number',
            'assigned_date' => 'Assigned Date',
            'closing_date' => 'Detached Date'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResellerAdmin()
    {
        return $this->hasOne(User::className(), ['id' => 'admin_id']);
    }

    /*
    *Return all resellers list
    */
    public function getResellerAdminList($resellerAdmins)
    {
       return ArrayHelper::map(User::find()->where(['in', 'id', ArrayHelper::map($resellerAdmins->all(), 'id', 'id')])->orderBy('username', 'asc')->all(), 'id', 'username');
    }
}
