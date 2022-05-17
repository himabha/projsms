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
        
        if(!$isAdmin)
        {
            if(Yii::$app->user->identity->role == 4){
                //$query->joinWith(['resellers']);
            } else {
                $query->joinWith(['users']);
            }
        }

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            //return $dataProvider;
        }

        if(!empty($search)){
            $query->orFilterWhere([
                'id' => $search,
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
        }

        if(!$isAdmin){
            if(\Yii::$app->user->identity->role == 2) // user 
            {
                if(empty($search))
                {
                    $query->andFilterWhere([
                        'id' => $this->id,
                    ]);
    
                    $query->andFilterWhere(['like', 'from_number', $this->from_number])
                    ->andFilterWhere(['like', 'to_number', $this->to_number])
                    ->andFilterWhere(['like', 'sms_message', $this->sms_message])
                    ->andFilterWhere(['like', 'delivered_time', $this->delivered_time])
                    ;
                }
            }
            if(\Yii::$app->user->identity->role == 3) // reseller 
            {
                if(empty($search))
                {
                    $query->andFilterWhere([
                        'id' => $this->id,
                        'sender_id' => $this->sender_id,
                        'agent_id' => $this->agent_id,
                    ]);
    
                    $query->andFilterWhere(['like', 'from_number', $this->from_number])
                    ->andFilterWhere(['like', 'to_number', $this->to_number])
                    ->andFilterWhere(['like', 'sms_message', $this->sms_message])
                    ->andFilterWhere(['like', 'delivered_time', $this->delivered_time])
                    ;
                }
            } else if(\Yii::$app->user->identity->role == 4) { // reseller admin
                if(empty($search)){
                    $query->andFilterWhere([
                        'id' => $this->id,
                        'sender_id' => $this->sender_id,
                        'reseller_id' => $this->reseller_id,
                    ]);
    
                    $query->andFilterWhere(['like', 'from_number', $this->from_number])
                    ->andFilterWhere(['like', 'to_number', $this->to_number])
                    ->andFilterWhere(['like', 'sms_message', $this->sms_message])
                    ->andFilterWhere(['like', 'delivered_time', $this->delivered_time])
                    ;
                }
            }
        } else { // admin
            if(empty($search)){
                $query->andFilterWhere([
                    'id' => $this->id,
                    'sender_id' => $this->sender_id,
                    'admin_id' => $this->admin_id,
                ]);

                $query->andFilterWhere(['like', 'from_number', $this->from_number])
                ->andFilterWhere(['like', 'to_number', $this->to_number])
                ->andFilterWhere(['like', 'sms_message', $this->sms_message])
                ->andFilterWhere(['like', 'delivered_time', $this->delivered_time])
                ;
            }
        }

        if(!$isAdmin){
            if(Yii::$app->user->identity->role == 4){
                $query->andFilterWhere(['in', 'sms_cdr.admin_id', Yii::$app->user->identity->id]);
            } else if(Yii::$app->user->identity->role == 2) {
                $query->andFilterWhere(['in', 'sms_cdr.agent_id', Yii::$app->user->identity->id]);
            } else {
                $query->andFilterWhere(['in', 'sms_cdr.reseller_id', Yii::$app->user->identity->id]);
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $dataProvider;
    }
}