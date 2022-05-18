<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Smscdr;

/**
 * FsmastertbSearch represents the model behind the search form of `app\models\Smscdr`.
 */
class TdrSearch extends Smscdr
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $return = [
            [['id'], 'number'],
            [['admin_id', 'reseller_id', 'agent_id', 'sender_id'], 'integer'],
            [['sms_message', 'delivered_time'], 'safe'],
            [['from_number', 'to_number'], 'string'],
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
        $query = Smscdr::find();

        // if(!$isAdmin)  => NOT SURE WHAT THIS BLOCK FOR
        // {
        //     if(Yii::$app->user->identity->role == 4){
        //         //$query->joinWith(['resellers']);
        //     } else {
        //         $query->joinWith(['users']);
        //     }
        // }

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            //return $dataProvider;
        }

        if(!empty($search)){
            $query->orFilterWhere([
                'id' => $search
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
            ->orFilterWhere(['like', 'delivered_time', $search])
            ;
        } else {
            // for all roles
            $query->andFilterWhere([
                'id' => $this->id,
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
            ->andFilterWhere(['like', 'delivered_time', $this->delivered_time])
            ;
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

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $dataProvider;
    }
}