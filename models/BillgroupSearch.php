<?php

/**
 *
 * @package    Material Dashboard Yii2
 * @author     CodersEden <hello@coderseden.com>
 * @link       https://www.coderseden.com
 * @copyright  2020 Material Dashboard Yii2 (https://www.coderseden.com)
 * @license    MIT - https://www.coderseden.com
 * @since      1.0
 */

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class BillgroupSearch
 * @package app\models
 */
class BillgroupSearch extends Billgroup
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'name', 'service', 'cost_rate', 'cld1rate', 'cld2rate', 'cld3rate', 'selfallocation', 'maxperday', 'notes'], 'safe'],
            [['id', 'country_id', 'countrynetwork_id', 'sender_id', 'maxperday', 'currency_id', 'billcycle_id'], 'integer'],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Billgroup::find();

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
            'currency_id' => $this->currency_id,
            'billcycle_id' => $this->billcycle_id,
            'country_id' => $this->country_id,
            'countrynetwork_id' => $this->countrynetwork_id,
            'sender_id' => $this->sender_id,
            'service' => $this->service,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            //->andFilterWhere(['like', 'currency_id', $this->currency_id])
            //->andFilterWhere(['like', 'billcycle_id', $this->billcycle_id])
            ->andFilterWhere(['like', 'selfallocation', $this->selfallocation])
            ->andFilterWhere(['like', 'maxperday', $this->maxperday])
            ->andFilterWhere(['like', 'notes', $this->notes]);
        return $dataProvider;
    }
}
