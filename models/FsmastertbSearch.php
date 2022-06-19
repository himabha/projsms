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


        switch(\Yii::$app->user->identity->role)
        {
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
    public function search($params, $users, $search=null, $isAdmin = false, $isTestPanel = false)
    {
        $query = Fsmastertb::find();
        if($isTestPanel) $query->andFilterWhere(['admin_id' => \Yii::$app->user->id]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            //return $dataProvider;
        }

        if(!empty($search)){
            $query->orFilterWhere(['cld1' => $search]);
            $query->orFilterWhere(['like', 'cld2description', $search]);
            if(!$isAdmin)
            {
                switch(Yii::$app->user->identity->role) 
                {
                    case 4: // reseller admin
                        $query->orFilterWhere(['cld1rate' => $search]);
                        $query->orFilterWhere(['like', 'user.username', $search]);
                        $query->leftJoin('user', 'fsmastertb.admin_id = user.id');
                        break;
                    case 3: // reseller
                        $query->orFilterWhere(['cld2rate' => $search]);
                        $query->orFilterWhere(['like', 'user.username', $search]);
                        $query->leftJoin('user', 'fsmastertb.reseller_id = user.id');
                        break;
                    case 2: // agent
                        $query->orFilterWhere(['cld3rate' => $search]);
                        break;
                }
            }
        } else {
            $query->andFilterWhere(['cld1' => $this->cld1]);
            $query->andFilterWhere(['billgroup_id' => $this->billgroup_id]);
            $query->andFilterWhere(['country_id' => $this->country_id]);
            $query->andFilterWhere(['like', 'cld2description', $this->cld2description]);
            switch(Yii::$app->user->identity->role) 
            {
                case 4: // reseller admin
                    $query->andFilterWhere([
                        'reseller_id' => $this->reseller_id,
                        'cld1rate' => $this->cld1rate
                    ]);
                    break;
                case 3: // reseller
                    $query->andFilterWhere([
                        'agent_id' => $this->agent_id,
                        'cld2rate' => $this->cld2rate
                    ]);
                    break;
                case 2: // agent
                    $query->andFilterWhere([
                        'cld3rate' => $this->cld3rate
                    ]);
                    break;
                case 1: // admin
                    $query->andFilterWhere([
                        'cld1rate' => $this->cld1rate,
                        'cld2rate' => $this->cld2rate,
                        'cld3rate' => $this->cld3rate,
                        'sender_id' => $this->sender_id,
                        'admin_id' => $this->admin_id,
                        'service_id' => $this->service_id
                    ]);
                    break;
            }
        }

        if(!$isAdmin){
            switch(Yii::$app->user->identity->role) 
            {
                case 4: // reseller admin
                    $query->andFilterWhere(['in', 'fsmastertb.admin_id', Yii::$app->user->identity->id]);
                    break;
                case 3: // reseller
                    $query->andFilterWhere(['in', 'fsmastertb.reseller_id', Yii::$app->user->identity->id]);
                    break;
                case 2: // agent
                    $query->andFilterWhere(['in', 'fsmastertb.agent_id', Yii::$app->user->identity->id]);
                    break;
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $dataProvider;
    }
}