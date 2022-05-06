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
        return [
            [['fsmid'], 'integer'],
            [['inboundip', 'cld1', 'cld2', 'outboundip', 'cld1description', 'cld2description'], 'safe'],
            [['cld1rate', 'cld2rate'], 'number'],
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
    //public function search($params, $users, $search=null, $isAdmin = false)
    public function search($params, $search=null, $isAdmin = false)
    {
        $query = Fsmastertb::find();
        /* if(!$isAdmin)
        {
          if(Yii::$app->user->identity->role == 4){
              $query->joinWith(['resellers']);
          }
          else{
            $query->joinWith(['users']);
          }
        }*/
        $this->load($params);

        /*if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        // $query->andFilterWhere([
        //     'fsmid' => $this->fsmid,
        //     'cld1rate' => $search,
        //     'cld2rate' => $search,
        // ]);
        */

        if(!empty($search)){
            $query->andFilterWhere(['like', 'fsmastertb.cld1', $search]);
            if(!$isAdmin){
                $query->orFilterWhere(['like', 'user.username', $search]);
            }
            //->orFilterWhere(['like', 'inboundip', $search])
            //->orFilterWhere(['like', 'cld2', $search])
            //->orFilterWhere(['like', 'outboundip', $search])
            $query->orFilterWhere(['like', 'cld1rate', $search])
            ->orFilterWhere(['like', 'cld2rate', $search])
            //->orFilterWhere(['like', 'cld1description', $search])
            ->orFilterWhere(['like', 'cld2description', $search]);
        }
        /*
        if(!$isAdmin){
            if(empty($search)){
                //$query->where(['closing_date' => NULL]);
            }
            else{
                //$query->andWhere(['closing_date' => NULL]);
            }
        }*/

        if(!$isAdmin){
          if(Yii::$app->user->identity->role == 4){
              $query->andFilterWhere(['in', 'fsmastertb.admin_id', Yii::$app->user->identity->id]);
          }
          else{
            $query->andFilterWhere(['in', 'fsmastertb.reseller_id', Yii::$app->user->identity->id]);
          }
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }
}
