<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Fstest;

/**
 * FsmastertbSearch represents the model behind the search form of `app\models\Fstest`.
 */
class FstestSearch extends Fstest
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Country', 'Number_Range', 'Test_Number', 'Rate'], 'safe'],
            //[['cld1rate', 'cld2rate'], 'number'],
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
        $query = Fstest::find();
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [ 
            'defaultOrder' => ['Country' => SORT_ASC] 
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

        $query->andFilterWhere(['like', 'Country', $this->Country])
        ->andFilterWhere(['like', 'Number_Range', $this->Number_Range])
        ->andFilterWhere(['like', 'Rate', $this->Rate]);
		if($admin_id){
			$query->andFilterWhere(['reseller_id' => $admin_id]);
		}
        $query->andFilterWhere(['like', 'Test_Number', $this->Test_Number]);

        return $dataProvider;
    }
}
