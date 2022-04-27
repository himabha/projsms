<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "billgroup".
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string|null $email
 * @property string|null $contact_no
 * @property string|null $smpp_host 
 * @property string|null $smpp_port
 * @property string|null $smpp_prefix
 * @property string|null $smpp_username
 * @property string|null $smpp_password
 * @property string|null $smpp_TxRx
 * @property int $status
 * @property string $submission_date
 */
class Supplier extends \yii\db\ActiveRecord
{
	/**
	 * Constants
	 */
	const SCENARIO_CREATE = 'create';
	const SCENARIO_UPDATE = 'update';

	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'smppclients';
	}

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		return $behaviors;
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['id', 'name', 'company_id', 'email', 'contact_no', 'smpp_host', 'smpp_port', 'smpp_prefix', 'smpp_username', 'smpp_password', 'smpp_TxRx', 'status', 'submission_date'], 'safe'],
			[['name', 'company_id', 'email', 'contact_no', 'smpp_host', 'smpp_port', 'smpp_prefix', 'smpp_username', 'smpp_password', 'smpp_TxRx'], 'trim'],
			[['id', 'company_id'], 'unique'],
			[['name', 'company_id', 'email', 'contact_no', 'smpp_host', 'smpp_port', 'smpp_prefix', 'smpp_username', 'smpp_password', 'smpp_TxRx'], 'string'],
			[['id', 'company_id'], 'integer'],
			[['name'], 'required', 'on' => self::SCENARIO_CREATE],
			[['name'], 'required', 'on' => self::SCENARIO_UPDATE],
		];
		return [];
	}
}
