<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Smscdr;

/**
 * FsmastertbSearch represents the model behind the search form of `app\models\Smscdr`.
 */
class TdrSearchSummary extends Smscdr
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
            [['msgs', 'billgroup_id'], 'integer'],
            [['rev_in', 'rev_out', 'profit', 'profit_percentage'], 'number'],
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
    public function search($params, $users, $search=null, $isAdmin = false, $detail = false)
    {
        if($isAdmin)
        {
            if($detail)
            {
                $query = Smscdr::find()->select("billgroup_id, currency, count(*) as msgs, sum(cost_rate) as rev_in, sum(cld1rate) as rev_out, sum(cost_rate - cld1rate) as profit, ((sum(cost_rate) / sum(cld1rate)) * 100) as profit_percentage");
            } else {
                $query = Smscdr::find()->select("currency, count(*) as msgs, sum(cost_rate) as rev_in, sum(cld1rate) as rev_out, sum(cost_rate - cld1rate) as profit, ((sum(cost_rate) / sum(cld1rate)) * 100) as profit_percentage");
            }
        } else {
            if(\Yii::$app->user->identity->role == 2) { // user
                if($detail)
                {
                    $query = Smscdr::find()->select("billgroup_id, currency, count(*) as msgs, sum(cld3rate) as rev_in, sum(cld3rate) as profit");
                } else {
                    $query = Smscdr::find()->select("currency, count(*) as msgs, sum(cld3rate) as rev_in, sum(cld3rate) as profit");
                }
            } else if(\Yii::$app->user->identity->role == 3) { // reseller
                if($detail)
                {
                    $query = Smscdr::find()->select("billgroup_id, currency, count(*) as msgs, sum(cld2rate) as rev_in, sum(cld3rate) as rev_out, sum(cld2rate - cld3rate) as profit, ((sum(cld2rate) / sum(cld3rate)) * 100) as profit_percentage");
                } else {
                    $query = Smscdr::find()->select("currency, count(*) as msgs, sum(cld2rate) as rev_in, sum(cld3rate) as rev_out, sum(cld2rate - cld3rate) as profit, ((sum(cld2rate) / sum(cld3rate)) * 100) as profit_percentage");
                }
            } else if(\Yii::$app->user->identity->role == 4) { // reseller admin
                if($detail)
                {
                    $query = Smscdr::find()->select("billgroup_id, currency, count(*) as msgs, sum(cld1rate) as rev_in, sum(cld2rate) as rev_out, sum(cld1rate - cld2rate) as profit, ((sum(cld1rate) / sum(cld2rate)) * 100) as profit_percentage");
                } else {
                    $query = Smscdr::find()->select("currency, count(*) as msgs, sum(cld1rate) as rev_in, sum(cld2rate) as rev_out, sum(cld1rate - cld2rate) as profit, ((sum(cld1rate) / sum(cld2rate)) * 100) as profit_percentage");
                }
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

        if(empty($search) && empty($params))
        {
            $query->andFilterWhere(['id' => 0]); // set empty        
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
                        $dr_start_time = date_create_from_format('d-m-Y H:i A', trim($this->dr[0]));
                        $this->dr_from = date_format($dr_start_time, 'Y-m-d H:i');
                        break;
                    case 2: 
                        $dr_start_time = date_create_from_format('d-m-Y H:i A', trim($this->dr[0]));
                        $this->dr_from = date_format($dr_start_time, 'Y-m-d H:m');
                        $dr_end_time = date_create_from_format('d-m-Y H:i A', trim($this->dr[1]));
                        $this->dr_to = date_format($dr_end_time, 'Y-m-d H:i');
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
            // if($isAdmin){
            //     $query->orFilterWhere([
            //         'admin_id' => $search,
            //         'sender_id' => $search,
            //     ]);
            // } else {
            //     if(\Yii::$app->user->identity->role == 2) // user
            //     {
            //         // nothing
            //     } else if(\Yii::$app->user->identity->role == 3) // reseller
            //     {
            //         $query->orFilterWhere([
            //             'agent_id' => $search,
            //         ]);
            //     } else if(\Yii::$app->user->identity->role == 4) { // reseller admin
            //         $query->orFilterWhere([
            //             'reseller_id' => $search,
            //         ]);
            //     }
            // }
            $query->orFilterWhere(['like', 'from_number', $search])
            ->orFilterWhere(['like', 'to_number', $search])
            ->orFilterWhere(['like', 'sms_message', $search])
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
        
        if($detail)
        {
            $query->groupBy('billgroup_id, currency');
        } else {
            $query->groupBy('currency');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $dataProvider;
    }
}