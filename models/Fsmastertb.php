<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "fsmastertb".
 *
 * @property int $fsmid
 * @property string $inboundip
 * @property string $CLD1
 * @property string $CLD2
 * @property string $outbound_ip
 * @property string $cdl1_rate
 * @property string $cld2_rate
 */
class Fsmastertb extends \yii\db\ActiveRecord
{
    public $file;
    public $user;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'fsmastertb';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['file','required','on' => 'upload'],
             [['fsmid', 'inboundip', 'cld1', 'cld2', 'cld1description', 'cld2description', 'outboundip', 'cld1rate', 'cld2rate'], 'required','on' => 'save'],
            [['fsmid'], 'integer'],
            [['cld1rate', 'cld2rate', 'cld3rate'], 'number'],
            [['inboundip', 'outboundip'], 'string', 'max' => 30],
            [['cld1', 'cld2'], 'string', 'max' => 20],
            [['cld1description', 'cld2description'], 'string', 'max' => 100],
            [['fsmid','cld1'], 'unique'],
            [['file'],'file','extensions'=>'csv','maxSize'=>1024 * 1024 * 5],
            ['user','safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'fsmid' => 'Fsmid',
            'inboundip' => 'Inboundip',
            'cld1' => 'Called Number',
            'cld2' => 'Cld2',
            'cld1description' => 'Cld1 Name',
            'cld2description' => 'Cld2 Name',
            'outboundip' => 'Outboundip',
            'cld1rate' => 'Cld1 Rate',
            'cld2rate' => 'Cld2 Rate',
            'cld3rate' => 'Cld3 Rate',
            'user' => 'User',
        ];
    }

    public function getCld()
    {
        return $this->hasOne(Fsusertb::className(), ['cld1' => 'cld1']);
    }

    public function getResellerCld()
    {
        return $this->hasOne(Fsresellertb::className(), ['cld1' => 'cld1']);
    }

    public function getResellerAdminCld()
    {
        return $this->hasOne(Fsadmintb::className(), ['cld1' => 'cld1']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(),['id' => 'user_id'])
            ->via('cld');
    }

    public function getReseller()
    {
        return $this->hasOne(User::className(),['id' => 'reseller_id'])
            ->via('resellerCld');
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

    public function getIdValue()
    {
        $count = Self::find()->count();
        $id = $count.rand (10000 , 99999);
        if (Self::find()->where(['fsmid' => $id])->count() == 0) {
            return $id;
        } else {
            return $this->getIdValue();
        }
    }


}
