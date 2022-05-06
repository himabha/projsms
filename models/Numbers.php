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

    public $start_number;
    public $number_qty;
    public $number_list;
    public $single_number;
    public $upload_type;
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['fsmid', 'smpp_username', 'status', 'smpp_gateway', 'inboundip', 'cld1', 'cld2', 'outboundip', 'cost_rate', 'cld1rate', 'cld2rate', 'cld3rate', 'cld1description', 'cld2description', 'maxduration', 'admin_id', 'reseller_id', 'agent_id', 'PaymentTerms', 'currency', 'billgroup_id', 'country_id', 'countrynetwork_id', 'service_id', 'sender_id', 'receiver_id'], 'safe'],
            //[['fsmid', 'admin_id', 'reseller_id', 'agent_id', 'billgroup_id', 'country_id', 'countrynetwork_id', 'service_id', 'sender_id', 'receiver_id'], 'integer'],
            [['fsmid', 'admin_id', 'reseller_id', 'agent_id', 'country_id', 'countrynetwork_id', 'receiver_id'], 'integer'],
        
            [['start_number', 'number_list', 'single_number', 'upload_type'], 'safe', 'on' => self::SCENARIO_CREATE],
            ['number_qty', 'integer', 'message' => 'required', 'on' => self::SCENARIO_CREATE],

            [['billgroup_id', 'sender_id', 'service_id'], 'required', 'message' => 'required',  'on' => self::SCENARIO_CREATE],
            [['billgroup_id', 'sender_id', 'service_id'], 'required', 'message' => 'required',  'on' => self::SCENARIO_CREATE],
            [['billgroup_id', 'sender_id', 'service_id'], 'integer', 'message' => 'required',  'on' => self::SCENARIO_CREATE],

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
