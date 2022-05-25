<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Smscdr;

/**
 * FsmastertbSearch represents the model behind the search form of `app\models\Smscdr`.
 */
class TdrSearchDetailed extends Smscdr
{
    public $dr;
    public $dr_from;
    public $dr_to;
    //public $msgs;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $return = [
            [['currency'], 'safe'],
            [['msgs', 'countrynetwork_id',  'billgroup_id'], 'integer'],
            [['admin_id', 'reseller_id', 'agent_id', 'sender_id'], 'integer'],
            [['delivered_time'], 'safe'],
        ];

        return $return;

    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $users, $search=null, $isAdmin = false)
    {
        if($isAdmin)
        {
            $query = Smscdr::find()->select("countrynetwork_id, billgroup_id, sender_id, admin_id, count(*) as msgs");
        } else {
            if(Yii::$app->user->identity->role == 3) { // reseller
                $query = Smscdr::find()->select("countrynetwork_id, billgroup_id, agent_id, count(*) as msgs");
            } else if(Yii::$app->user->identity->role == 4){ // reseller admin
                $query = Smscdr::find()->select("countrynetwork_id, billgroup_id, reseller_id, count(*) as msgs");
            } else if(Yii::$app->user->identity->role == 2) { //user
                $query = Smscdr::find()->select("countrynetwork_id, billgroup_id, count(*) as msgs");
            }
        }

        // if(!$isAdmin)  => NOT SURE WHAT THIS BLOCK FOR
        // {
        //     if(Yii::$app->user->identity->role == 4){
        //         //$query->joinWith(['resellers']);
        //     } else {
        //         $query->joinWith(['users']);
        //     }
        // }

        $this->load($params);
        if(isset($params['TdrSearchSummary']['billgroup_id']))
        {
            $this->billgroup_id = $params['TdrSearchSummary']['billgroup_id'];
        }
        if(isset($params['TdrSearchSummary']['admin_id']))
        {
            $this->admin_id = $params['TdrSearchSummary']['admin_id'];
        }
        if(isset($params['TdrSearchSummary']['agent_id']))
        {
            $this->agent_id = $params['TdrSearchSummary']['agent_id'];
        }
        if(isset($params['TdrSearchSummary']['reseller_id']))
        {
            $this->reseller_id = $params['TdrSearchSummary']['reseller_id'];
        }
        if(isset($params['TdrSearchSummary']['sender_id']))
        {
            $this->sender_id = $params['TdrSearchSummary']['sender_id'];
        }
        if(isset($params['TdrSearchSummary']['delivered_time']))
        {
            $this->delivered_time = $params['TdrSearchSummary']['delivered_time'];
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            //return $dataProvider;
        }

        if(!empty($this->delivered_time))
        {
            try{
                $this->dr = explode("to", $this->delivered_time);   
                if(empty($this->dr)) $this->dr = [];
                switch (count($this->dr))
                {
                    case 1: 
                        $dr_start_time = date_create_from_format('d-m-Y', trim($this->dr[0]));
                        $this->dr_from = date_format($dr_start_time, 'Y-m-d 00:00');
                        break;
                    case 2: 
                        $dr_start_time = date_create_from_format('d-m-Y', trim($this->dr[0]));
                        $this->dr_from = date_format($dr_start_time, 'Y-m-d 00:00');
                        $dr_end_time = date_create_from_format('d-m-Y', trim($this->dr[1]));
                        $this->dr_to = date_format($dr_end_time, 'Y-m-d 23:59');
                        break;
                }             
            } catch (\Exception $e) {

            }
        }

        if(!empty($search)){
            $query->orFilterWhere([
                'id' => $search
            ]);
            $query->orFilterWhere([
                'billgroup_id' => $this->billgroup_id,
            ]);
            if($isAdmin){
                $query->orFilterWhere([
                    'admin_id' => $search,
                    'sender_id' => $search,
                ]);
            } else {
                if(\Yii::$app->user->identity->role == 2) // user
                {
                    // nothing
                } else if(\Yii::$app->user->identity->role == 3) // reseller
                {
                    $query->orFilterWhere([
                        'agent_id' => $search,
                    ]);
                } else if(\Yii::$app->user->identity->role == 4) { // reseller admin
                    $query->orFilterWhere([
                        'reseller_id' => $search,
                    ]);
                }
            }
            $query->orFilterWhere(['like', 'from_number', $search])
            ->orFilterWhere(['like', 'to_number', $search])
            ->orFilterWhere(['like', 'sms_message', $search])
            //->orFilterWhere(['like', 'delivered_time', $search])
            ;

            if(!empty($this->dr_from) && !empty($this->dr_to))
            {
                $query->orFilterWhere(
                    ["BETWEEN", "delivered_time", $this->dr_from, $this->dr_to]
                );
            } elseif(!empty($this->dr_from)) {
                $query->orFilterWhere(['like', 'delivered_time', $this->dr_from]);
            }
        } else {            
            // for all roles
            $query->andFilterWhere([
                'id' => $this->id,
            ]);
            $query->andFilterWhere([
                'billgroup_id' => $this->billgroup_id,
            ]);

            if(!$isAdmin){
                if(\Yii::$app->user->identity->role == 2) // user 
                {
                    // do nothing
                } else if(\Yii::$app->user->identity->role == 3) {// reseller 
                    $query->andFilterWhere([
                        'agent_id' => $this->agent_id,
                    ]);
                } else if(\Yii::$app->user->identity->role == 4) { // reseller admin
                    $query->andFilterWhere([
                        'reseller_id' => $this->reseller_id,
                    ]);
                }
            } else { // admin
                $query->andFilterWhere([
                    'admin_id' => $this->admin_id,
                    'sender_id' => $this->sender_id,
                ]);
            }

            // for all roles
            $query->andFilterWhere(['like', 'from_number', $this->from_number])
            ->andFilterWhere(['like', 'to_number', $this->to_number])
            ->andFilterWhere(['like', 'sms_message', $this->sms_message])
            //->andFilterWhere(['like', 'delivered_time', $this->delivered_time])
            ;

            if(!empty($this->dr_from) && !empty($this->dr_to))
            {
                $query->andFilterWhere(
                    ["BETWEEN", "delivered_time", $this->dr_from, $this->dr_to]
                );
            } elseif(!empty($this->dr_from)) {
                $query->andFilterWhere(['like', 'delivered_time', $this->dr_from]);
            }

        }

        if(!$isAdmin){
            if(Yii::$app->user->identity->role == 4){ // reseller admin
                //$query->andFilterWhere(['in', 'sms_cdr.admin_id', Yii::$app->user->identity->id]);
            } else if(Yii::$app->user->identity->role == 2) { //user
                $query->andFilterWhere(['in', 'sms_cdr.agent_id', Yii::$app->user->identity->id]);
            } else if(Yii::$app->user->identity->role == 3) { // reseller
                $query->andFilterWhere(['in', 'sms_cdr.reseller_id', Yii::$app->user->identity->id]);
            }
        }
        
        if($isAdmin){
            $query->groupBy('countrynetwork_id, billgroup_id, sender_id, admin_id');
        } else {
            if(Yii::$app->user->identity->role == 3) { // reseller
                $query->groupBy('countrynetwork_id, billgroup_id, agent_id');
            } else if(Yii::$app->user->identity->role == 4){ // reseller admin
                $query->groupBy('countrynetwork_id, billgroup_id, reseller_id');
            } else if(Yii::$app->user->identity->role == 2) { //user
                $query->groupBy('countrynetwork_id, billgroup_id');
            }
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $dataProvider;
    }
}