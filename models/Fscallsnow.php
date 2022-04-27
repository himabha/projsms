<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "fscallsnow".
 *
 * @property string $fscallsnow_id
 * @property string $dialed_number
 * @property string $call_start
 * @property string $ani
 * @property double $call_rate
 * @property string $session_id
 */
class Fscallsnow extends \yii\db\ActiveRecord
{
    //public $call_duration;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fscallsnow';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['dialed_number', 'call_start', 'ani', 'cld1', 'cld2', 'cld1_ratepersec', 'cld2_ratepersec', 'session_id','call_state'], 'required'],
            [['call_start','call_state','call_answered'], 'safe'],
            [['cld1_ratepersec', 'cld2_ratepersec'], 'number'],
            [['dialed_number', 'ani', 'cld1', 'cld2', 'session_id'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fscallsnow_id' => 'Fscallsnow ID',
            'dialed_number' => 'Dialed Number',
            'call_start' => 'Call Start',
            'ani' => 'Caller Number',
  //          'cld1' => 'Number Range',
            'cld2' => 'Mapped Number',
            'cld1_ratepersec' => 'Cld1 Rate per min',
            'cld2_ratepersec' => 'Rate per Min',
            'session_id' => 'Session ID',
            'call_state' => 'Call State',
            //'call_duration' => 'Call Duration',
        ];
    }

    public function getCld()
    {
        return $this->hasOne(Fsmastertb::className(), ['cld1' => 'cld1']);
    }

    public function getUserTb()
    {
        return $this->hasOne(Fsusertb::className(), ['cld1' => 'cld1']);
    }

    public function getResellerTb()
    {
        return $this->hasOne(Fsresellertb::className(), ['cld1' => 'cld1']);
    }


    public function getCallDuration()
    {
        date_default_timezone_set('Europe/London');
        $dteStart = new \DateTime($this->call_answered);
        $dteEnd = new \DateTime("now");
        $dteDiff  = $dteStart->diff($dteEnd);
        return $dteDiff->format("%H:%I:%S");
        //return $dteStart->format("Y-m-d H:i:s")."   ".$dteEnd->format("Y-m-d H:i:s");
    }
}
