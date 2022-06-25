<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "billgroup".
 *
 * @property int $id
 * @property string $name
 * @property int|null $country_id
 * @property int|null $countrynetwork_id
 * @property int $sender_id
 * @property string|null $currency_id 
 * @property string|null $billcycle_id
 * @property int $service
 * @property float $cost_rate
 * @property float $cld1rate
 * @property float $cld2rate
 * @property string $cld3rate
 * @property string $selfallocation
 * @property string $maxperday
 * @property string|null $notes
 */
class Billgroup extends \yii\db\ActiveRecord
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
		return 'billgroup';
	}

	public function behaviors()
	{
		$behaviors = parent::behaviors();

		// auto fill timestamp columns.
		/* if ($this->hasAttribute('created_at') || $this->hasAttribute('updated_at')) {
		    $behavior = [
			    'class' => TimestampBehavior::class,
			    'value' => new Expression('NOW()'),
		    ];
		    if ($this->hasAttribute('created_at')) {
			    $behavior['createdAtAttribute'] = 'created_at';
		    } else {
			    $behavior['createdAtAttribute'] = null;
		    }
		    if ($this->hasAttribute('updated_at')) {
			    $behavior['updatedAtAttribute'] = 'updated_at';
		    } else {
			    $behavior['updatedAtAttribute'] = null;
		    }
		    $behaviors[] = $behavior;
	    } */
		return $behaviors;
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['id', 'name', 'country_id', 'countrynetwork_id', 'sender_id', 'currency_id', 'billcycle_id', 'service', 'cost_rate', 'cld1rate', 'cld2rate', 'cld3rate', 'selfallocation', 'maxperday', 'notes'], 'safe'],
			[['name', 'notes', 'maxperday', 'selfallocation'], 'trim'],
			[['id', 'name'], 'unique'],
			[['name', 'notes'], 'string'],
			[['id', 'country_id', 'countrynetwork_id', 'sender_id', 'maxperday',], 'integer'],
			[['name'], 'required', 'on' => self::SCENARIO_CREATE],
			[['name'], 'required', 'on' => self::SCENARIO_UPDATE],
		];
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'name' => 'Bill Group Name (required)',
			'country_id' => 'Country',
			'countrynetwork_id' => 'Country Network',
			'sender_id' => 'Supplier',
			'currency_id' => 'Currency',
			'billcycle_id' => 'Billcycle',
			'selfallocation' => 'Max daily self allocate value (0 to disable)',
			'maxperday' => 'Max per day value (0 for no limit)'
		];
	}

	public function attributeHints()
	{
		return [
			'name'        => \Yii::t('app', 'Enter your Bill Group Name'),
		];
	}

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 * @throws \yii\base\Exception
	 */
	public function beforeSave($insert)
	{
		/* if (!parent::beforeSave($insert)) {
			return false;
		}

		if (!empty($this->passwd)) {
			$this->password = static::hashPassword($this->passwd);
		}
		*/
		return true;
	}

	/**
	 * @param int|string $user_id
	 *
	 * @return User|IdentityInterface|null
	 */
	public static function findIdentity($billgroup_id)
	{
		return static::findOne($billgroup_id);
	}

	/**
	 * @return int|string
	 */
	public function getId()
	{
		return $this->id;
	}

    public function getCountry()
    {
        return $this->hasOne(Country::className(),['ID' => 'country_id']);
    }

    public function getSupplier()
    {
        return $this->hasOne(Supplier::className(),['id' => 'sender_id']);
    }

    public function getCurrency()
    {
        return $this->hasOne(Currency::className(),['id' => 'currency_id']);
    }

    public function getBillcycle()
    {
        return $this->hasOne(Billcycle::className(),['ID' => 'billcycle_id']);
    }

	public static function getBillgroupItems()
    {
        $res = static::find()->orderBy(['name' => SORT_ASC])->all();
        return ArrayHelper::map($res, 'id', 'name');
    }

}
