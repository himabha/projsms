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
 * Class Numbers
 * @package app\models
 */
class Numbers extends Fsmastertb
{
    /**
	 * Constants
	 */
	const SCENARIO_CREATE = 'create';
	const SCENARIO_UPDATE = 'update';
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['fsmid', 'smpp_username', 'status', 'smpp_gateway', 'inboundip', 'cld1', 'cld2', 'outboundip', 'cost_rate', 'cld1rate', 'cld2rate', 'cld3rate', 'cld1description', 'cld2description', 'maxduration', 'admin_id', 'reseller_id', 'agent_id', 'PaymentTerms', 'currency', 'billgroup_id', 'country_id', 'countrynetwork_id', 'service_id', 'sender_id', 'receiver_id'], 'safe'],
            [['fsmid', 'admin_id', 'reseller_id', 'agent_id', 'billgroup_id', 'country_id', 'countrynetwork_id', 'service_id', 'sender_id', 'receiver_id'], 'integer'],
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
}
