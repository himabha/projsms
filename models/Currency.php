<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "billgroup".
 *
 * @property int $ID
 * @property string $Country
 * @property string|null $Destination
 * @property string|null $Type
 * @property string $Dialcode
 * @property string|null $Start_date 
 * @property string|null $End_date
 */
class Currency extends \yii\db\ActiveRecord
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
		return 'currency';
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
			[['id', 'country_id'], 'integer'],
			[['currency'], 'safe'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
	}

	public function attributeHints()
	{
	}
}
