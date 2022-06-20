<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Fsmastertb;

/**
 * FsmastertbSearch represents the model behind the search form of `app\models\Fsmastertb`.
 */
class FsmastertbSearch extends Fsmastertb
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $return = [
            [['fsmid', 'admin_id'], 'integer'],
            [['inboundip', 'cld1', 'cld2', 'outboundip', 'cld1description', 'cld2description', 'country_id'], 'safe'],
            [['cld1rate', 'cld2rate', 'cld3rate', 'billgroup_id'], 'number']
        ];


        switch (\Yii::$app->user->identity->role) {
            case 1: // admin
                $return[] = [['admin_id', 'sender_id', 'service_id'], 'number'];
                break;
            case 2: // agent
                break;
            case 3: // reseller
                $return[] = [['agent_id'], 'number'];
                break;
            case 4: // reseller admin
                $return[] = [['reseller_id'], 'number'];
                break;
        }

        return $return;

        // return [
        //     [['fsmid'], 'integer'],
        //     [['inboundip', 'cld1', 'cld2', 'outboundip', 'cld1description', 'cld2description'], 'safe'],
        //     [['cld1rate', 'cld2rate', 'billgroup_id', 'sender_id','admin_id', 'service_id'], 'number'],
        // ];
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
        $query = Fsmastertb::find();
        if (!$isAdmin) {
            $query->joinWith(['users']);
        }
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            //return $dataProvider;
        }
        $searches = [];
        if (!$isAdmin) {
            if (\Yii::$app->user->identity->role == 2) { // user               
                $query->andFilterWhere([
                    'cld1rate' => $this->cld1rate,
                    'cld2rate' => $this->cld2rate,
                    'cld3rate' => $this->cld3rate,
                    'billgroup_id' => $this->billgroup_id,
                    'country_id' => $this->country_id
                ]);
                $query->andFilterWhere(['like', 'cld1', $this->cld1]);
                if (!empty($search)) {
                    $searches = [
                        ['like', 'cld1rate', $search],
                        ['like', 'cld2rate', $search],
                        ['like', 'cld3rate', $search]
                    ];
                }
            } else if (\Yii::$app->user->identity->role == 3) { // reseller
                $query->andFilterWhere([
                    'cld2rate' => $this->cld2rate,
                    'cld3rate' => $this->cld3rate,
                    'billgroup_id' => $this->billgroup_id,
                    'agent_id' => $this->agent_id,
                    'country_id' => $this->country_id
                ]);
                $query->andFilterWhere(['like', 'cld1', $this->cld1]);
                if (!empty($search)) {
                    $searches = [
                        ['like', 'cld2rate', $search],
                        ['like', 'cld3rate', $search]
                    ];
                }
            } else if (\Yii::$app->user->identity->role == 4) { // reseller admin
                $query->andFilterWhere([
                    'cld1rate' => $this->cld1rate,
                    'cld2rate' => $this->cld2rate,
                    'billgroup_id' => $this->billgroup_id,
                    'reseller_id' => $this->reseller_id,
                    'country_id' => $this->country_id
                ]);
                $query->andFilterWhere(['like', 'cld1', $this->cld1]);
                if (!empty($search)) {
                    $searches = [
                        ['like', 'cld1rate', $search],
                        ['like', 'cld2rate', $search]
                    ];
                }
            }
        } else { // admin
            $query->andFilterWhere([
                'cld1rate' => $this->cld1rate,
                'cld2rate' => $this->cld2rate,
                'billgroup_id' => $this->billgroup_id,
                'sender_id' => $this->sender_id,
                'admin_id' => $this->admin_id,
                'service_id' => $this->service_id,
                'country_id' => $this->country_id,
            ]);
            $query->andFilterWhere(['like', 'cld1', $this->cld1]);
            if (!empty($search)) {
                $searches = [
                    ['like', 'cld1rate', $search],
                    ['like', 'cld2rate', $search]
                ];
            }
        }

        if (!empty($search)) {
            if (!$isAdmin) {
                $searches = ['like', 'user.username', $search];
            }
            $query->andFilterWhere(
                [
                    'or',
                    ['like', 'cld1', $search],
                    ['like', 'cld1description', $search],
                    $searches
                ]
            );
        }

        if (!$isAdmin && !$isTestPanel) {
            if (Yii::$app->user->identity->role == 4) {
                $query->andFilterWhere(['in', 'fsmastertb.admin_id', Yii::$app->user->identity->id]);
            } else if (Yii::$app->user->identity->role == 2) {
                $query->andFilterWhere(['in', 'fsmastertb.agent_id', Yii::$app->user->identity->id]);
            } else if (Yii::$app->user->identity->role == 3) {
                $query->andFilterWhere(['in', 'fsmastertb.reseller_id', Yii::$app->user->identity->id]);
            }
        } else if ($isTestPanel) {
            $query->andFilterWhere(['fsmastertb.admin_id' => \Yii::$app->params['test_panel_id']]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $dataProvider;
    }
}
