<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "fsmycdr".
 *
 * @property string $fscdrid
 * @property string $ani
 * @property string $called_number
 * @property string $cld1
 * @property string $cld2
 * @property string $call_getdate
 * @property string $call_startdate
 * @property string $call_enddate
 * @property string $call_duration
 * @property string $inbound_ip
 * @property double $cld1_ratepersec
 * @property double $cld2_ratepersec
 * @property string $outbound_ip
 * @property string $hangup_cause
 * @property string $country
 */
class Fsmycdr extends \yii\db\ActiveRecord
{
    public $Charges;
    public $Cost;
    public $Sale;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fsmycdr';
    }

    /**
    * @inheritdoc$primaryKey
    */
    public static function primaryKey()
    {
      return ["fscdrid"];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fscdrid', 'call_duration'], 'integer'],
            [['ani', 'called_number', 'cld1', 'cld2', 'call_getdate', 'call_enddate', 'call_duration', 'inbound_ip', 'cld1_ratepersec', 'cld2_ratepersec', 'outbound_ip', 'hangup_cause', 'country'], 'required'],
            [['call_getdate', 'call_startdate', 'call_enddate','Charges','Cost', 'Sale'], 'safe'],
            [['cld1_ratepersec', 'cld2_ratepersec'], 'number'],
            [['ani', 'inbound_ip'], 'string', 'max' => 50],
            [['called_number', 'cld1', 'cld2', 'outbound_ip', 'hangup_cause'], 'string', 'max' => 100],
            [['country'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fscdrid' => 'Fscdrid',
            'ani' => 'Caller Number',
            'called_number' => 'Dialed Number',
            'cld1' => 'Cld1',
            'cld2' => 'Cld2',
            'call_getdate' => 'Call Getdate',
            'call_startdate' => 'Call Startdate',
            'call_enddate' => 'Call Enddate',
            'call_duration' => 'Duration [Minutes]',
            'inbound_ip' => 'Inbound Ip',
            'cld1_ratepersec' => 'Cld1 Ratepersec',
            'cld2_ratepersec' => 'Cld2 Ratepersec',
            'cld3_ratepersec' => 'Cld3 Ratepersec',
            'outbound_ip' => 'Outbound Ip',
            'hangup_cause' => 'Hangup Cause',
            'country' => 'Country',
            'agent_id' => "Agent",
            'reseller_id' => "Reseller",
            'admin_id' => "Reseller Admin",
        ];
    }

    public function getTotalcharge($provider)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item['Charges'];
        }

        return round($total,4);
    }

    public function getTotalCost($provider)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item['Cost'];
        }

        return round($total,4);
    }

    public function getTotalSale($provider)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item['Sale'];
        }

        return round($total,4);
    }

    public function getTotalMargin($provider)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item['Charges'] - $item['Cost'];
        }

        return round($total,4);
    }

    public function getUser()
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
}
