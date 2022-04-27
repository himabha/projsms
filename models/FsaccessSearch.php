<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Fsaccess;

/**
 * FsaccessSearch represents the model behind the search form of `app\models\Fsaccess`.
 */
class FsaccessSearch extends Fsaccess
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['caller_number', 'called_number', 'caller_origination', 'called_Destination'], 'safe'],
        ];
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
    public function search($params, $admin_id = null)
    {
        $query = Fsaccess::find();
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [ 
            'defaultOrder' => ['called_destination' => SORT_ASC] 
         ],
       ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // $query->andFilterWhere([
        //     'id' => $this->id,
        //     'user_id' => $this->user_id,
        // ]);

        $query->andFilterWhere(['like', 'called_destination', $this->called_destination])
        ->andFilterWhere(['like', 'called_number', $this->called_number])
        ->andFilterWhere(['like', 'caller_number', $this->caller_number]);
		if($admin_id){
			$query->andFilterWhere(['reseller_id' => $admin_id]);
		}
        $query->andFilterWhere(['like', 'caller_origination', $this->caller_origination]);

        return $dataProvider;
    }
}
