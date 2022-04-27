<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "fscdr".
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
 */
class Fscdr extends \yii\db\ActiveRecord
{
    public $date;
    public $sum;
    public $call_count;
    public $minute;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'fscdr';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // [['ani', 'called_number', 'cld1', 'cld2', 'call_getdate', 'call_enddate', 'call_duration', 'inbound_ip', 'cld1_ratepersec', 'cld2_ratepersec', 'outbound_ip', 'hangup_cause'], 'required'],
            [['call_startdate', 'call_enddate', 'agent_id', 'reseller_id', 'admin_id', 'ani', 'called_number', 'cld1', 'cld2', 'call_getdate', 'call_enddate', 'call_duration', 'inbound_ip', 'cld1_ratepersec', 'cld2_ratepersec', 'outbound_ip', 'hangup_cause','fscdr'], 'safe'],
            [['call_duration'], 'integer'],
            [['cld1_ratepersec', 'cld2_ratepersec'], 'number'],
            [['ani', 'inbound_ip'], 'string', 'max' => 50],
            [['called_number', 'cld1', 'cld2', 'outbound_ip', 'hangup_cause'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'fscdrid' => 'Fscdrid',
            'ani' => 'Caller Number',
            'called_number' => 'Dialed Number',
            'cld1' => 'Called Number',
            'cld2' => 'Cld2',
            'call_getdate' => 'Call Getdate',
            'call_startdate' => 'Call Start',
            'call_enddate' => 'Call Enddate',
            'call_duration' => 'Call Duration',
            'inbound_ip' => 'Inbound Ip',
            'cld1_ratepersec' => 'Cld1 Rate per min',
            'cld2_ratepersec' => 'Cld2 Rate per min',
            'outbound_ip' => 'Outbound Ip',
            'hangup_cause' => 'Hangup Cause',
            'agent_id' => 'Agent Id',
            'reseller_id' => 'Reseller Id',
            'admin_id' => 'Reseller Admin Id',
        ];
    }

    public static function getTotal($provider)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += (float)(($item['call_duration']/60)*$item['cld1_ratepersec'])+(float)(($item['call_duration']/60)*$item['cld2_ratepersec']);
        }

        return round($total,4);
    }

    public static function getTotalMin($provider)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item['call_duration'];
        }

        return round($total/60,2);
    }

    public function getRate()
    {
        $sum = (float)(($this->call_duration/60)*$this->cld1_ratepersec)+(float)(($this->call_duration/60)*$this->cld2_ratepersec);
        return '$'.round($sum,4);
    }

    public static function getTotalCalledMin($provider)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item['minute'];
        }

        return round($total/60,2);
    }

    public static function getTotalCalls($provider)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item['call_count'];
        }

        return $total;
    }

    public static function getTotalCost($provider)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item['sum'];
        }

        return round($total,4);
    }
}
