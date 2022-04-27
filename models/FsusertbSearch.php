<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Fsusertb;

/**
 * FsusertbSearch represents the model behind the search form of `app\models\Fsusertb`.
 */
class FsusertbSearch extends Fsusertb
{

    public function attributes()
    {
        // add related fields to searchable attributes
      return array_merge(parent::attributes(), ['master.cld2rate','master.cld1description','cld2_rate','country','user.username']);

    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['cld1','user.username','user_name','master.cld2rate','master.cld1description','cld2_rate','country'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
    public function search($params)
    {
        $query = Fsusertb::find();
        $query->joinWith('user');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
        ]);

        $query->andFilterWhere(['like', 'cld1', $this->cld1])
        ->andFilterWhere(['like', 'user.username', $this->user_name]);

        return $dataProvider;
    }

    public function searchNumbers($params)
    {
        $query = Fsusertb::find();
        $query->joinWith('master')
        ->where(['user_id' => Yii::$app->user->identity->id])->andWhere(['closing_date' => NULL]);

        // add conditions that should always apply here

        if (isset($params['pg_nmbr'])) {
            if (!empty($params['pg_nmbr']) || $params['pg_nmbr'] >0) {
                $page = $params['pg_nmbr'];
            } else {
                $page = 20;
            }
        } else {
            $page = 20;
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $page,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        // $query->andFilterWhere([
        //     'id' => $this->id,
        //     'user_id' => $this->user_id,
        // ]);

        $query->andFilterWhere(['like', 'fsusertb.cld1', $this->cld1])
        ->andFilterWhere(['like', 'fsmastertb.cld1description', $this->country])
        ->andFilterWhere(['like', 'fsmastertb.cld2rate', $this->cld2_rate]);

        return $dataProvider;
    }
}
