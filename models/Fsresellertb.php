<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "fsresellertb".
 *
 * @property int $fsuid
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $cld1
 */
class Fsresellertb extends \yii\db\ActiveRecord
{
    public $user_name;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'fsresellertb';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['reseller_id','cld1'],'required'],
            [['reseller_id'], 'integer'],
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
            'reseller_id' => 'Reseller',
            'cld1' => 'Number',
            'assigned_date' => 'Assigned Date',
            'closing_date' => 'Detached Date'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReseller()
    {
        return $this->hasOne(User::className(), ['id' => 'reseller_id']);
    }

    /*
    *Return all users list
    */
    public function getResellerList($resellers)
    {
       return ArrayHelper::map(User::find()->where(['in', 'id', ArrayHelper::map($resellers->all(), 'id', 'id')])->orderBy('username', 'asc')->all(), 'id', 'username');
    }
}
