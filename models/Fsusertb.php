<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "fsusertb".
 *
 * @property int $fsuid
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $cld1
 */
class Fsusertb extends \yii\db\ActiveRecord
{
    public $user_name;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'fsusertb';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id','cld1'],'required'],
            [['user_id'], 'integer'],
            [['assigned_date','closing_date'], 'safe'],
            //[['cld1'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User',
            'cld1' => 'Number',
            'assigned_date' => 'Assigned Date',
            'closing_date' => 'Detached Date'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getMaster()
    {
        return $this->hasOne(Fsmastertb::className(), ['cld1' => 'cld1']);
    }


    /*
    * Return unused cld list
    */
    public function getCldList()
    {
        $fsuser = Fsusertb::find()->select('cld1')->where(['closing_date' => NULL]);
        //Fsmastertb::find()->all();
        $cld1 = ArrayHelper::map(Fsmastertb::find()->where(['not in','cld1',$fsuser])->limit(1000)->all(), 'cld1', 'cld1');
        //$cld2 = ArrayHelper::map(Fsusertb::find()->all(), 'id', 'cld1');
        return $cld1;
    }

    /*
    return user id
    */
    public function getIdValue()
    {
        $count = Self::find()->count();
        $id = $count.rand (10000 , 99999);
        if (Self::find()->where(['fsuid' => $id])->count() == 0) {
            return $id;
        } else {
            return $this->getIdValue();
        }
    }

    /*
    *Return all users list
    */
    public function getUserList($users)
    {
       return ArrayHelper::map(User::find()->where(['in', 'id', ArrayHelper::map($users->all(), 'id', 'id')])->orderBy('username', 'asc')->all(), 'id', 'username');
    }

    /*
    * Get Summary
    */
    public function getSummary($users = [], $isAdmin = false, $isReseller = false, $isUser = false, $isResellerAdmin = false)
    {
        $assigned = Fsmastertb::find();
        //->joinWith(['user']);

        if($isAdmin && !$isReseller && !$isUser && !$isResellerAdmin){
            $assigned->where(['>', 'fsmastertb.admin_id', 0])
            ->orFilterWhere(['>', 'fsmastertb.reseller_id', 0])
            ->orFilterWhere(['>', 'fsmastertb.agent_id', 0]);
        }
        else if($isAdmin && !$isReseller && !$isResellerAdmin && $isUser){
            $assigned->where(['>', 'fsmastertb.reseller_id', 0])
            ->andFilterWhere(['>', 'fsmastertb.agent_id', 0]);
        }
        else if($isAdmin && $isReseller && !$isResellerAdmin && !$isUser){
            $assigned->where(['>', 'fsmastertb.reseller_id', 0])
            ->andFilterWhere(['=', 'fsmastertb.agent_id', 0]);
        }
        else if($isAdmin && !$isReseller && $isResellerAdmin && !$isUser){
            $assigned->where(['>', 'fsmastertb.admin_id', 0])
            ->andFilterWhere(['=', 'fsmastertb.reseller_id', 0])
            ->andFilterWhere(['=', 'fsmastertb.agent_id', 0]);
        }
        else if(!$isAdmin && $isUser && !empty($users)){
            $assigned->where(['in', 'agent_id', $users]);
        }
        else if(!$isAdmin && $isReseller && !empty($users)){
            $assigned->where(['in', 'reseller_id', $users]);
        }
        else if(!$isAdmin && $isResellerAdmin && !empty($users)){
            $assigned->where(['in', 'admin_id', $users]);
        }
        //$assigned->andWhere(['closing_date' => NULL]);
        $assigned = $assigned->count();
        if (!$assigned) {
            $assigned = 0;
        }
        $stock = Fsmastertb::find();
        if($isAdmin && !$isUser && ($isReseller || $isResellerAdmin)){
            $stock->where(['=', 'fsmastertb.admin_id', 0])
            ->andFilterWhere(['=', 'fsmastertb.reseller_id', 0])
            ->andFilterWhere(['=', 'fsmastertb.agent_id', 0]);
        }
        else if($isAdmin && !$isReseller && !$isResellerAdmin  && $isUser){
            $stock->where(['>', 'fsmastertb.reseller_id', 0])
            ->andFilterWhere(['=', 'fsmastertb.admin_id', 0])
            ->andFilterWhere(['=', 'fsmastertb.agent_id', 0]);
        }
        else if(!$isAdmin){
            $stock->where(['in', 'fsmastertb.reseller_id', Yii::$app->user->identity->id])
            ->orFilterWhere(['=', 'fsmastertb.admin_id', Yii::$app->user->identity->id]);
        }
        $stock = $stock->count();
        if (!$stock) {
            $stock = 0;
        }
        return ['assigned' => $assigned,'stock' => $stock];
    }

    public function getMyStock()
    {
        $mystock = Fsusertb::find()->where(['user_id' => Yii::$app->user->identity->id,'closing_date' => NULL])->count();
        if (!$mystock) {
            $mystock = 0;
        }
        return $mystock;
    }

    /*
    * Return logined users clds
    */
    public static function getMyclds()
    {
        return ArrayHelper::map(
            Fsusertb::find()->where(['user_id' => Yii::$app->user->identity->id])->all(),
             'id', 'cld1');

    }
}
