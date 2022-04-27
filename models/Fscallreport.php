<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "fscallreport".
 *
 * @property string $Date
 * @property int $agent_id
 * @property string $Country
 * @property string $Caller_ID
 * @property string $Cld1
 * @property double $Cld1_Rate
 * @property double $Cld2_Rate
 * @property int $Total_Calls
 * @property double $Call_Duration
 * @property double $Charges
 * @property double $Cost
 */
class Fscallreport extends \yii\db\ActiveRecord
{
    public $margin;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fscallreport';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Date', 'Country', 'Caller_ID', 'Cld1'], 'required'],
            [['Date','margin'], 'safe'],
            [['agent_id', 'reseller_id', 'admin_id', 'Total_Calls'], 'integer'],
            [['Cld1_Rate', 'Cld2_Rate', 'Cld3_Rate', 'Call_Duration', 'Charges', 'Cost', 'cld1_cost', 'cld2_cost', 'cld3_cost'], 'number'],
            [['Country', 'Cld1'], 'string', 'max' => 100],
            [['Caller_ID','called_number'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Date' => 'Date',
            'agent_id' => 'Agent',
            'reseller_id' => 'Reseller',
            'admin_id' => 'Reseller Admin',
            'Country' => 'Country',
            'Caller_ID' => 'Caller  Number',
            'Cld1' => 'Cld1',
            'Cld1_Rate' => 'Cld1  Rate',
            'Cld2_Rate' => 'Rate Per Min',
            'Total_Calls' => 'Total  Calls',
            'Call_Duration' => 'Call  Duration',
            'Charges' => 'Charges',
            'Cost' => 'Cost',
            'called_number' => 'Dialed Number',
            'cld1_cost' => 'Cld1 Cost',
            'cld2_cost' => 'Cld2 Cost',
            'cld3_cost' => 'Cld3 Cost',
        ];
    }

    public function getAgent()
    {
        return $this->hasOne(User::className(), ['id' => 'agent_id']);
    }

    public function getReseller()
    {
        return $this->hasOne(User::className(), ['id' => 'reseller_id']);
    }

    public function getResellerAdmin()
    {
        return $this->hasOne(User::className(), ['id' => 'admin_id']);
    }

    public static function getTotalCalls($provider)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item['Total_Calls'];
        }

        return $total;
    }

    public static function getTotalMinutes($provider)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item['Call_Duration'];
        }

        return $total;
    }

    public static function getTotalCharges($provider)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item['Charges'];
        }

        return $total;
    }

    public static function getTotalCost($provider)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item['Cost'];
        }

        return $total;
    }

    public static function getTotalResellerCost($provider)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item['cld1_cost'];
        }

        return $total;
    }

    public static function getTotalResellerSale($provider)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item['cld2_cost'];
        }

        return $total;
    }

    public static function getTotalAgentCost($provider)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item['cld2_cost'];
        }

        return $total;
    }

    public static function getTotalAgentSale($provider)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item['cld3_cost'];
        }

        return $total;
    }

    public static function getTotalMargin($provider)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item['margin'];
        }

        return $total;
    }
}
