<?php

namespace app\models;

use Yii;
//use yii\behaviors\TimestampBehavior;
//use yii\db\Expression;
//use yii\web\IdentityInterface;

class Smscdr extends \yii\db\ActiveRecord
{
	public $msgs;
	public $rev_in;
	public $rev_out;
	public $profit;
	public $profit_percentage;

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
            [['msgs', 'billgroup_id'], 'integer'],
            [['rev_in', 'rev_out', 'profit', 'profit_percentage'], 'number'],
        ];
	}

	public function attributeLabels()
    {
        return [
			'id' => 'ID',
			'billgroup_id_id' => 'Billgroup',
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

	public function getBillgroup()
    {
        return $this->hasOne(Billgroup::className(),['id' => 'billgroup_id']);
    }

	public function getCountry()
    {
        return $this->hasOne(Country::className(),['ID' => 'country_id']);
    }


}
