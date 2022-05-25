<?php

namespace app\controllers;

use app\models\BillgroupSearch;
use Yii;
use app\models\User;
use app\models\Fsusertb;
use app\models\Fscdr;
use app\models\Fstest;
use app\models\FstestSearch;
use app\models\FsusertbSearch;
use app\models\Fscallsnow;
use app\models\Fsmastertb;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use app\models\Fsmycdr;
use app\models\Fscallreport;
use app\models\Brandname;

use app\models\Billgroup;
use app\models\Country;
use app\models\FsmastertbSearch;
use app\models\Numbers;
use app\models\Supplier;

use app\models\TdrSearch;
use app\models\TdrSearchSummary;
use app\models\TdrSearchDetailed;

class UserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['cdr', 'fs-test', 'fs-call-report', 'export-mycdr', 'export-fscall', 'export-mynumber', 'country-summary'],
                'rules' => [
                    [
                        'actions' => ['cdr', 'fs-test', 'fs-call-report', 'export-mycdr', 'export-fscall', 'export-mynumber', 'country-summary'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Displays active calls for reseller.
     *
     * @return string
     */
    public function actionActiveCalls()
    {
        $caller_id = isset($_GET['caller_id']) ? $_GET['caller_id'] : '';
        $called_no = isset($_GET['called_no']) ? $_GET['called_no'] : '';
        $ip = $_SERVER['REMOTE_ADDR'];
        $iduser = Yii::$app->user->identity->id;
        $sql = "UPDATE user SET lastlogin = NOW(), userip = '$ip'  WHERE id = $iduser";
        Yii::$app->db->createCommand($sql)
            ->execute();

        $myclds = Fsmastertb::find()->select('cld1')->where(['agent_id' => Yii::$app->user->identity->id]);
        $query = Fscallsnow::find()->where(['cld1' => $myclds])->orderBy(['call_start' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);
        $query->andFilterWhere(['like', 'ani', $caller_id])
            ->andFilterWhere(['like', 'dialed_number', $called_no]);
        $count = $query->count();
        return $this->render('active_calls', ['dataProvider' => $dataProvider, 'count' => $count, 'caller_id' => $caller_id, 'called_no' => $called_no]);
    }

    /*
    * List users CDR list
    */
    public function actionCdr()
    {
        ini_set('max_execution_time', 360);
        $date_range = isset($_GET['date_range']) ? $_GET['date_range'] : '';
        $caller_id = isset($_GET['caller_id']) ? $_GET['caller_id'] : '';
        $called_no = isset($_GET['called_no']) ? $_GET['called_no'] : '';

        if (strpos($date_range, " - ") == FALSE) {
            $start = date('Y-m-d');
            $end = date('Y-m-d');
        } else {
            $date = explode(" - ", $date_range);
            $start = $date[0];
            $end = $date[1];
        }
        $startT = $start . ' 00:01:00';
        $endT = $end . ' 23:59:00';

        //$clds_count = Fsusertb::find()->where(['user_id' => Yii::$app->user->identity->id,'closing_date' => NULL])->count();

        $query = Fsmycdr::find()
            ->select(['call_startdate', 'ani', 'called_number', 'cld1', 'country', 'call_duration', '(cld1_ratepersec * (call_duration/60)) AS Charges', '(cld3_ratepersec * (call_duration/60)) AS Cost', 'fsmycdr.reseller_id'])
            ->innerJoin('user', '`user`.`id` = `fsmycdr`.`agent_id`')
            ->where(['between', 'call_startdate', $startT, $endT])
            ->with(['user'])
            ->andWhere(['=', 'user.id', Yii::$app->user->identity->id]);

        $date = $start . ' - ' . $end;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);
        $query->andFilterWhere(['like', 'ani', $caller_id])
            ->andFilterWhere(['like', 'called_number', $called_no]);
        $query1 = $query;

        $value['count'] = $query1->count();

        $min_sum = $query1->sum('call_duration');
        $value['min_sum'] = round($min_sum / 60, 2);
        $value['revenue'] = $query1->sum('((call_duration/60)*cld2_ratepersec) + ((call_duration/60)*cld1_ratepersec)');

        $query = $query->orderBy(['call_startdate' => SORT_DESC]);

        return $this->render('cdr', ['dataProvider' => $dataProvider, 'date' => $date, 'value' => $value, 'caller_id' => $caller_id, 'called_no' => $called_no]);
    }

    public function actionFsTest()
    {
        $foldername = array_reverse(explode("/", getcwd()))[0];
        $brandname = explode(".com", $_SERVER['SERVER_NAME'])[0];
        $findBrand = Brandname::findOne(['foldername' => $foldername, 'name' => $brandname]);

        $searchModel = new FstestSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $findBrand ? $findBrand->admin_id : 0);

        return $this->render('fs_test', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionMyNumber()
    {
        $Fsusertb = new Fsusertb();
        $searchModel = new FsusertbSearch();
        $dataProvider = $searchModel->searchNumbers(Yii::$app->request->queryParams);

        $summary['mystock'] = $Fsusertb->getMyStock();

        return $this->render('my_number', ['summary' => $summary, 'dataProvider' => $dataProvider, 'searchModel' => $searchModel,]);
    }

    public function actionFsCallReport()
    {
        $country_name = isset($_GET['country']) ? $_GET['country'] : '';
        $caller_id = isset($_GET['caller_id']) ? $_GET['caller_id'] : '';
        $cld_1 = isset($_GET['cld1']) ? $_GET['cld1'] : '';
        $cld1_rate = isset($_GET['cld1_rate']) ? $_GET['cld1_rate'] : '';
        $cld2_rate = isset($_GET['cld2_rate']) ? $_GET['cld2_rate'] : '';
        $date_range = isset($_GET['date_range']) ? $_GET['date_range'] : '';
        $called_num = isset($_GET['called_num']) ? $_GET['called_num'] : '';

        if (!empty($date_range) && strlen($date_range) > 2) {
            if (strpos($date_range, ' - ') !== false) {
                $date = explode(' - ', $date_range);
            } else {
                $date = explode('-', $date_range);
            }
            $start = $date[0];
            $end = $date[1];
        } else {
            $start = date('Y-m-d');
            $end = date('Y-m-d');
        }

        $Datepickr = $start . ' - ' . $end;


        $query = Fscallreport::find()->where(['agent_id' => Yii::$app->user->id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $query->andFilterWhere([
            'Country' => $country_name,
            'Caller_ID' => $caller_id,
            'Cld1' => $cld_1,
        ]);

        //if ($flag) {
        $query->andFilterWhere(['between', 'Date', $start . ' 00:00:00', $end . ' 23:59:59'])
            ->andFilterWhere(['like', 'called_number', $called_num]);
        //}
        $query1 = $query;

        $totalColls = $query1->sum('Total_Calls');
        $Call_Duration = $query1->sum('Call_Duration');
        $Cost = $query1->sum('cld3_cost');

        $country = Fscallreport::find()->groupBy(['Country'])->where(['between', 'Date', $start, $end])->andWhere(['agent_id' => Yii::$app->user->id])->all();
        $Caller_ID = Fscallreport::find()->groupBy(['Caller_ID'])->where(['between', 'Date', $start, $end])->andWhere(['agent_id' => Yii::$app->user->id])->all();
        $cld1 = Fscallreport::find()->groupBy(['Cld1'])->where(['between', 'Date', $start, $end])->andWhere(['agent_id' => Yii::$app->user->id])->limit(1000)->all();


        return $this->render(
            'fs_call_report',
            [
                'dataProvider' => $dataProvider,
                'country' => $country,
                'caller_id' => $Caller_ID,
                'cld1' => $cld1,
                'country_name' => $country_name,
                'callerId' => $caller_id,
                'cld_1' => $cld_1,
                'cld1_rate' => $cld1_rate,
                'cld2_rate' => $cld2_rate,
                'totalColls' => $totalColls,
                'Call_Duration' => $Call_Duration,
                'Cost' => $Cost,
                'Datepickr' => $Datepickr,
                'called_num' => $called_num
            ]
        );
    }

    /*
    * Download cdr as excel
    */
    public function actionExportMycdr()
    {
        ini_set('max_execution_time', 360);
        ini_set('memory_limit', '-1');

        $date_range = isset($_GET['export_date']) ? $_GET['export_date'] : '';
        $caller_id = isset($_GET['export_caller_id']) ? $_GET['export_caller_id'] : '';
        $called_no = isset($_GET['export_called_no']) ? $_GET['export_called_no'] : '';

        if (strpos($date_range, " - ") == FALSE) {
            $start = date('Y-m-d');
            $end = date('Y-m-d');
        } else {
            $date = explode(" - ", $date_range);
            $start = $date[0];
            $end = $date[1];
        }
        $startT = $start . ' 00:01:00';
        $endT = $end . ' 23:59:00';

        //$clds_count = Fsusertb::find()->where(['user_id' => Yii::$app->user->identity->id,'closing_date' => NULL])->count();

        $clds = Fsusertb::find()->where(['user_id' => Yii::$app->user->identity->id, 'closing_date' => NULL])->all();

        $i = 0;

        //        $query = Fsmycdr::find()->select(['call_startdate','ani' , 'called_number' , 'cld1'  ,'country' , 'call_duration', '(cld2_ratepersec * (call_duration/60)) AS Charges']);
        $query = Fsmycdr::find()->select(['call_startdate', 'ani', 'called_number', 'cld1', 'country', 'call_duration']);


        $condition[] = 'OR';

        if ($clds) {
            foreach ($clds as $cld) {
                $condition[] = ['and', ['cld1' => $cld->cld1], ['>=', 'call_startdate', $cld->assigned_date]];
            }
        } else {
            $condition[] = ['cld1' => '.1'];
        }

        $query = $query->andWhere($condition)
            ->andWhere(['between', 'call_startdate', $startT, $endT]);

        if (!empty($caller_id)) {
            $query = $query->andWhere(['like', 'ani', $caller_id]);
        }

        if (!empty($called_no)) {
            $query = $query->andWhere(['like', 'called_number', $called_no]);
        }

        $query = $query->orderBy(['call_startdate' => SORT_DESC])->all();


        if ($query) {

            $filename = "data_sheet.xls";
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");

            echo 'call_startdate' . "\t" . 'Caller_id' . "\t" . 'called_number' . "\t" . 'country' . "\t" . 'Call Duration' . "\t" . 'Revenue' . "\n";
            if ($query)
                foreach ($query as $value) {

                    echo $value->call_startdate . "\t" . $value->ani . "\t" . $value->called_number . "\t" . $value->country . "\t" . round($value->call_duration / 60, 2) . "\n";
                }
            exit;
        } else {
            return $this->redirect(['cdr']);
        }
    }

    /*
    * Export fscall report
    */
    public function actionExportFscall()
    {
        $country_name = isset($_GET['country']) ? $_GET['country'] : '';
        $caller_id = isset($_GET['caller_id']) ? $_GET['caller_id'] : '';
        $cld_1 = isset($_GET['cld1']) ? $_GET['cld1'] : '';
        $cld1_rate = isset($_GET['cld1_rate']) ? $_GET['cld1_rate'] : '';
        $cld2_rate = isset($_GET['cld2_rate']) ? $_GET['cld2_rate'] : '';
        $date_range = isset($_GET['date_range']) ? $_GET['date_range'] : '';
        $called_num = isset($_GET['called_num']) ? $_GET['called_num'] : '';

        if (!empty($date_range) && strlen($date_range) > 2) {
            if (strpos($date_range, ' - ') !== false) {
                $date = explode(' - ', $date_range);
            } else {
                $date = explode('-', $date_range);
            }
            $start = $date[0];
            $end = $date[1];
        } else {
            $start = date('Y-m-d');
            $end = date('Y-m-d');
        }

        $Datepickr = $start . ' - ' . $end;


        $query = Fscallreport::find()->where(['agent_id' => Yii::$app->user->id]);

        $query->andFilterWhere([
            'Country' => $country_name,
            'Caller_ID' => $caller_id,
            'Cld1' => $cld_1,
        ]);

        //if ($flag) {
        $query->andFilterWhere(['between', 'Date', $start . ' 00:00:00', $end . ' 23:59:59'])
            ->andFilterWhere(['like', 'called_number', $called_num]);
        //}

        $query = $query->all();

        if ($query) {

            $filename = "fscall_report.xls";
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");

            echo 'Date' . "\t" . 'Country' . "\t" . 'Caller ID' . "\t" . 'Called Number' . "\t" . 'Total Calls' . "\t" . 'Call Duration' . "\n";
            if ($query)
                foreach ($query as $value) {

                    echo $value->Date . "\t" . $value->Country . "\t" . $value->Caller_ID . "\t" . $value->called_number . "\t" . $value->Total_Calls . "\t" . $value->Call_Duration . "\n";
                }
            exit;
        } else {
            return $this->redirect(['fs-call-report']);
        }
    }

    /*
    * Load dropdown in seach fields in fscalreport oage
    */
    public function actionLoadSearchFields()
    {
        $country_optn = "<option value=''>Country</option>";
        $callerId_optn = "<option value=''>Caller Id</option>";
        $cld1_optn = "<option value=''>Cld1</option>";

        $start = Yii::$app->request->post('start');
        $end = Yii::$app->request->post('end');

        $country = Fscallreport::find()->groupBy(['Country'])->where(['between', 'Date', $start, $end])->andWhere(['agent_id' => Yii::$app->user->id])->all();
        $Caller_ID = Fscallreport::find()->groupBy(['Caller_ID'])->where(['between', 'Date', $start, $end])->andWhere(['agent_id' => Yii::$app->user->id])->all();
        $cld1 = Fscallreport::find()->groupBy(['Cld1'])->where(['between', 'Date', $start, $end])->limit(1000)->andWhere(['agent_id' => Yii::$app->user->id])->all();



        if ($country) {
            foreach ($country as $value) {
                $country_optn .= '<option value="' . $value->Country . '">' . $value->Country . '</option>';
            }
        }

        if ($Caller_ID) {
            foreach ($Caller_ID as $value) {
                $callerId_optn .= '<option value="' . $value->Caller_ID . '">' . $value->Caller_ID . '</option>';
            }
        }

        if ($cld1) {
            foreach ($cld1 as $value) {
                $cld1_optn .= '<option value="' . $value->Cld1 . '">' . $value->Cld1 . '</option>';
            }
        }
        return [
            'country_optn' => $country_optn,
            'callerId_optn' => $callerId_optn,
            'cld1_optn' => $cld1_optn,

        ];
    }

    public function actionExportMynumber()
    {
        $country_name = isset($_GET['country']) ? $_GET['country'] : '';
        $cld_1 = isset($_GET['cld1']) ? $_GET['cld1'] : '';
        $cld2_rate = isset($_GET['cld2_rate']) ? $_GET['cld2_rate'] : '';


        $query = Fsusertb::find()->joinWith('master')
            ->where(['user_id' => Yii::$app->user->identity->id])->andWhere(['closing_date' => NULL]);
        $query->andFilterWhere(['like', 'fsusertb.cld1', $cld_1])
            ->andFilterWhere(['like', 'fsmastertb.cld1description', $country_name])
            ->andFilterWhere(['like', 'fsmastertb.cld2rate', $cld2_rate]);


        $query = $query->all();

        if ($query) {

            $filename = "mynumber.xls";
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");

            echo 'Country' . "\t" . 'Number' . "\n";
            if ($query)
                foreach ($query as $value) {

                    echo $value->master->cld1description . "\t" . $value->cld1 . "\n";
                }
            exit;
        } else {
            return $this->redirect(['my-number']);
        }
    }

    /*
    * Country wise Summary
    */
    public function actionCountrySummary()
    {
        $country_id = isset($_GET['country']) ? $_GET['country'] : '';
        $date_range = isset($_GET['date_range']) ? $_GET['date_range'] : date('Y-m-d', strtotime("-6 days")) . " to " . date('Y-m-d');

        if (strpos($date_range, " to ") !== FALSE) {
            $date = explode(" to ", $date_range);
            $start = $date[0] . " 00:00:00";
            $end = $date[1] . " 23:59:59";
        } else {
            $start = date('Y-m-d', strtotime("-6 days")) . " 00:00:00";
            $end = date('Y-m-d') . " 23:59:59";
        }

        $query = Fscallreport::find()->select('Country,SUM(Total_Calls) As Total_Calls,SUM(Call_Duration) AS Call_Duration,SUM(Cost) AS Cost')->where(['agent_id' => Yii::$app->user->identity->id])->groupBy(['Country']);

        //echo $query->createCommand()->getRawSql(); exit();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $query->andFilterWhere([
            'Country' => $country_id,
        ]);
        $query->andFilterWhere(['between', 'Date', $start, $end]);

        $country = Fscallreport::find()->groupBy(['Country'])->all();

        return $this->render('country_wise_summary', ['dataProvider' => $dataProvider, 'date_range' => $date_range, 'country' => $country, 'country_id' => $country_id]);
    }

    /**
     * Function to list all billgroups
     */

    public function actionBillgroups()
    {
        $searchModel = new BillgroupSearch();
        $dataProvider = $searchModel->search(\Yii::$app->getRequest()->queryParams);
        $dataProvider->pagination->pageSize = 10;

        \Yii::$app->view->title = \Yii::t('app', 'Billgroups');

        return $this->render('billgroups', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'billgroups' => $this->getBillgroupItems(),
            'countries' => $this->getCountryItems(),
            'country_networks' => $this->getCountryNetworkItems(),
            'currencies' => $this->getCurrencyItems(),
            'billcycles' => $this->getBillcycleItems(),
            'suppliers' => $this->getSupplierItems(),
            'services' => $this->getServicesItems()
        ]);
    }

    protected function getCountryItems()
    {
        $res = Country::find()->groupBy('Country')->all();
        return \yii\helpers\ArrayHelper::map($res, 'ID', 'Country');
    }

    protected function getCountryNetworkItems()
    {
        $res = Country::find()->all();
        return \yii\helpers\ArrayHelper::map($res, 'ID', 'Country_Network');
    }

    protected function getCurrencyItems()
    {
        $items = [];
        $res = \app\models\Currency::find()->all();
        if(is_array($res) && count($res) > 0)
        {
            foreach($res as $v)
            {
                $items[$v->id] = $v->currency;
            }
        }
        return $items;
    }
    protected function getBillcycleItems()
    {
        $items = [];
        $res = \app\models\Billcycle::find()->all();
        if(is_array($res) && count($res) > 0)
        {
            foreach($res as $v)
            {
                $items[$v->ID] = $v->billcycle;
            }
        }
        return $items;
    }

    protected function getServicesItems()
    {
        $items = [];
        $res = \Yii::$app->params['services'];
        if(is_array($res) && count($res) > 0)
        {
            foreach($res as $k=>$v)
            {
                $items[$k] = $v;
            }
        }
        return $items;
    }
    protected function getSupplierItems()
    {
        $items = [];
        $res = Supplier::find()->all();
        if(is_array($res) && count($res) > 0)
        {
            foreach($res as $v)
            {
                $items[$v->id] = $v->name;
            }
        }
        return $items;
    }

    /*
    * Add cld to users
    */
    public function actionSmsNumbers()
    {
        $model = new Fsusertb();
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 20;

        if ($filter == 'all') {
            $filter = '';
        }

        $searchModel = new FsmastertbSearch();
        $mysubusr = User::find()->select('id')->where(['agent_id' => Yii::$app->user->identity->id, 'role' => 2]);
        $summary = $model->getSummary($mysubusr, false, false, true);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search);
        $dataProvider->pagination->pageSize = $filter;

        return $this->render('my_number', [
            'dataProvider' => $dataProvider, 
            'searchModel' => $searchModel,
            'summary' => $summary,
            'countries' => $this->getCountryItems(),
            'billgroups' => $this->getBillgroupItems(),
        ]);
    }

    protected function getBillgroupItems()
    {
        $items = [];
        $res = \app\models\Billgroup::find()->all();
        if(is_array($res) && count($res) > 0)
        {
            foreach($res as $v)
            {
                $items[$v->id] = $v->name;
            }
        }
        return $items;
    }

    public function actionSmsTdr()
    {
        $model = new Fsusertb();
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 20;
        $mysubusr = User::find()->select('id')->where(['role' => 2]);

        if ($filter == 'all') {
            $filter = '';
        }

        $searchModel = new TdrSearch();

        $mysubusr = User::find()->select('id')->where(['agent_id' => Yii::$app->user->identity->id, 'role' => 2]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search, false);
        $dataProvider->setPagination(['pageSize' => $filter]); 

        return $this->render('tdr', [
            'dataProvider' => $dataProvider, 
            'searchModel' => $searchModel,
            'search' => $search, 
            'filter' => $filter,
            'billgroups' => $this->getBillgroupItems(),
        ]);
    }

    public function actionSummaryReport()
    {
        $model = new Fsusertb();
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 20;

        if ($filter == 'all') {
            $filter = '';
        }

        $searchModel = new TdrSearchSummary();

        $mysubusr = User::find()->select('id')->where(['agent_id' => Yii::$app->user->identity->id, 'role' => 2]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search, false, false);
        $dataProvider->setPagination(['pageSize' => $filter]); 

        return $this->render('summary_report', [
            'dataProvider' => $dataProvider, 
            'searchModel' => $searchModel,
            'search' => $search, 
            'filter' => $filter,
            'billgroups' => $this->getBillgroupItems(),
        ]);
    }

    public function actionDetailedReport()
    {
        $model = new Fsusertb();
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 20;

        if ($filter == 'all') {
            $filter = '';
        }

        $mysubusr = User::find()->select('id')->where(['agent_id' => Yii::$app->user->identity->id, 'role' => 2]);
        $searchModel = new TdrSearchSummary();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search, false, true);
        $dataProvider->setPagination(['pageSize' => $filter]); 

        $searchModel_1 = new TdrSearchDetailed();
        $dataProvider_1 = $searchModel_1->search(Yii::$app->request->queryParams, $mysubusr, $search, false);
        $dataProvider_1->setPagination(['pageSize' => $filter]); 

        return $this->render('detail_report', [
            'dataProvider' => $dataProvider, 
            'dataProvider_1' => $dataProvider_1, 
            'search' => $search, 
            'filter' => $filter,
            'billgroups' => $this->getBillgroupItems(),
        ]);
    }



}
