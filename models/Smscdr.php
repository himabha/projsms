<?php

namespace app\models;

use Yii;
//use yii\behaviors\TimestampBehavior;
//use yii\db\Expression;
//use yii\web\IdentityInterface;

class Smscdr extends \yii\db\ActiveRecord
{
	/**
	 * Constants
	 */
	const SCENARIO_CREATE = 'create';
	const SCENARIO_UPDATE = 'update';

	public static function tableName()
	{
		return 'sms_cdr';
	}

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		return $behaviors;
	}

	public function rules()
	{
		return [
            [['id'], 'number'],
            [['admin_id', 'delivered_time_time', 'reseller_id', 'agent_id', 'sender_id'], 'integer'],
            [['sms_message'], 'safe'],
            [['from_number', 'to_number'], 'string' , 'max' => 15],
        ];
	}

	public function attributeLabels()
    {
        return [
			'id' => 'ID',
			'admin_id' => 'Client',
			'sender_id' => 'Supplier',
			'delivered_time' => 'Delivered Time',
			'sms_message' => 'SMS Message',
			'from_number' => 'From Number',
			'to_number' => 'To Number'
		];
	}

    public function getUsers()
    {
        return $this->hasOne(User::className(),['id' => 'agent_id']);
    }

    public function getResellers()
    {
        return $this->hasOne(User::className(),['id' => 'reseller_id']);
    }

	public function getResellerAdmin()
    {
        return $this->hasOne(User::className(),['id' => 'admin_id']);
    }

	public function getSupplier()
    {
        return $this->hasOne(Supplier::className(),['id' => 'sender_id']);
    }


}
