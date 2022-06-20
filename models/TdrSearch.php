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
    public $dr;
    public $dr_from;
    public $dr_to;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $return = [
            [['id'], 'number'],
            [['billgroup_id', 'admin_id', 'reseller_id', 'agent_id', 'sender_id'], 'integer'],
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
    public function search($params, $users, $search = null, $isAdmin = false, $isTestPanel = false)
    {
        $query = Smscdr::find();

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            //return $dataProvider;
        }

        if (!empty($this->delivered_time)) {
            try {
                $this->dr = explode("to", $this->delivered_time);
                if (empty($this->dr)) $this->dr = [];
                switch (count($this->dr)) {
                    case 1:
                        $dr_start_time = date_create_from_format('d-m-Y H:i A', trim($this->dr[0]));
                        $this->dr_from = date_format($dr_start_time, 'Y-m-d H:i');
                        break;
                    case 2:
                        $dr_start_time = date_create_from_format('d-m-Y H:i A', trim($this->dr[0]));
                        $this->dr_from = date_format($dr_start_time, 'Y-m-d H:i');
                        $dr_end_time = date_create_from_format('d-m-Y H:i A', trim($this->dr[1]));
                        $this->dr_to = date_format($dr_end_time, 'Y-m-d H:i');
                        break;
                }
                if (!empty($this->dr_from) && !empty($this->dr_to)) {
                    $query->andFilterWhere(
                        ["BETWEEN", "delivered_time", $this->dr_from, $this->dr_to]
                    );
                } elseif (!empty($this->dr_from)) {
                    $query->andFilterWhere(['like', 'delivered_time', $this->dr_from]);
                }
            } catch (\Exception $e) {
            }
        }

        if (!empty($search)) {
            $query->andFilterWhere(
                [
                    'or',
                    ['like', 'id', $search],
                    ['like', 'from_number', $search],
                    ['like', 'to_number', $search],
                    ['like', 'sms_message', $search]
                ]
            );
        } else {
            // for all roles
            $query->andFilterWhere([
                'id' => $this->id,
                'billgroup_id' => $this->billgroup_id
            ]);

            if (!$isAdmin) {
                if (\Yii::$app->user->identity->role == 3) { // reseller 
                    $query->andFilterWhere([
                        'agent_id' => $this->agent_id,
                    ]);
                } else if (\Yii::$app->user->identity->role == 4) { // reseller admin
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
            $query->andFilterWhere(['from_number' => $this->from_number])
                ->andFilterWhere(['to_number' => $this->to_number])
                ->andFilterWhere(['like', 'sms_message', $this->sms_message]);
        }

        if (!$isAdmin && !$isTestPanel) {
            if (Yii::$app->user->identity->role == 4) { // reseller admin
                $query->andFilterWhere(['in', 'sms_cdr.admin_id', Yii::$app->user->identity->id]);
            } else if (Yii::$app->user->identity->role == 2) { //user
                $query->andFilterWhere(['in', 'sms_cdr.agent_id', Yii::$app->user->identity->id]);
            } else if (Yii::$app->user->identity->role == 3) { // reseller
                $query->andFilterWhere(['in', 'sms_cdr.reseller_id', Yii::$app->user->identity->id]);
            }
        } else if ($isTestPanel) {
            $query->andFilterWhere(['sms_cdr.admin_id' => \Yii::$app->params['test_panel_id']]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $dataProvider;
    }
}
