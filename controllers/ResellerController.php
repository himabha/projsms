<?php

namespace app\controllers;

use app\models\BillgroupSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use app\models\Fsmastertb;
use app\models\Fsusertb;
use app\models\Fsresellertb;
use app\models\FstestSearch;
use app\models\Fscallsnow;
use yii\web\UploadedFile;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use app\models\User;
use app\models\Fscdr;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use app\models\FsaccessSearch;
use app\models\FsmastertbSearch;
use app\models\Fsmycdr;
use yii\helpers\ArrayHelper;
use yii\db\Query;
use app\models\Fscallreport;
use app\models\Brandname;

use app\models\Country;
use app\models\Supplier;
use app\models\Currency;

use app\models\TdrSearch;
use app\models\TdrSearchSummary;
use app\models\TdrSearchDetailed;

class ResellerController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['check-callsend', 'load-search-fields'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'application/xml' => Response::FORMAT_XML,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['add-user', 'delete-user', 'cdr', 'list-user', 'edit-user', 'date-report', 'detach-number', 'show-number-routes', 'fs-call-report', 'export-fscall', 'agent-summary', 'export-agent-summary', 'load-search-fields'],
                'rules' => [
                    [
                        'actions' => ['add-user', 'delete-user', 'cdr', 'list-user', 'edit-user', 'date-report', 'detach-number', 'show-number-routes', 'fs-call-report', 'export-fscall', 'agent-summary', 'export-agent-summary', 'load-search-fields'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return User::isReseller(Yii::$app->user->identity->id);
                        },

                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete-cld' => ['post'],
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

        $myclds = Fsmastertb::find()->select('cld1')->where(['reseller_id' => Yii::$app->user->identity->id]);
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

    /****************************/

    public function actionAccess()
    {
        $get = Yii::$app->request->queryParams;

        if (isset($get['FsaccessSearch']['called_destination'])) {
            $called_destination = $get['FsaccessSearch']['called_destination'];
        } else {
            $called_destination = '';
        }

        if (isset($get['FsaccessSearch']['called_number'])) {
            $called_number = $get['FsaccessSearch']['called_number'];
        } else {
            $called_number = '';
        }

        $searchModel = new FsaccessSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('test_access', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'called_destination' => $called_destination,
            'called_number' => $called_number
        ]);
    }



    /**************************/

    /*
    * Add user
    */
    public function actionAddUser()
    {
        $flag = 0;
        $user = new User();
        if ($user->load(Yii::$app->request->post())) {
            $id = $user->getIdValue();
            $user->id = $id;
            $user->setResellerId(Yii::$app->user->identity->id);
            $user->setPassword($user->password);
            $user->role = 2;
            if ($user->save()) {
                $user = new User();
                $model = new Fsusertb();
                Yii::$app->session->setFlash('user_add_success', "User added successfully.");
            } else {
                $user->password = "";
                Yii::$app->session->setFlash('user_add_failed', "Failed to save detail try again.");
            }
        }
        return $this->render('add_user', ['user' => $user]);
    }

    /*
    * List all Users
    */
    public function actionListUser()
    {
        $name = isset($_GET['name']) ? $_GET['name'] : '';
        $query = User::find()->where(['role' => 2, 'reseller_id' => Yii::$app->user->identity->id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        $query->andFilterWhere(['like', 'username', $name]);

        return $this->render('list_user', ['dataProvider' => $dataProvider, 'name' => $name]);
    }

    /*
    * Assign cld number to user
    */
    public function actionAssignNumber()
    {
        $user = Yii::$app->request->post('user');
        $numbers = explode(",", Yii::$app->request->post('numbers'));

        foreach ($numbers as $key => $value) {

            $resellertbmodel = new Fsresellertb();
            $resellertbmodel->cld1 = $value;
            $resellertbmodel->user_id = $user;
            $resellertbmodel->save();

            $usertbmodel = new Fsusertb();
            $usertbmodel->cld1 = $value;
            $usertbmodel->user_id = $user;
            $usertbmodel->assigned_date = date("Y-m-d H:i:s");
            $usertbmodel->save();

            $mastertbmodel = Fsmastertb::findOne(['cld1' => $value]);
            $mastertbmodel->agent_id = $user;
            $mastertbmodel->save();
        }
        Yii::$app->session->setFlash('cld_added', Yii::$app->request->post('numbers') . (count($numbers) > 1 ? ' are' : ' is') . " assigned successfully");
    }

    /*
    * Edit User
    */
    public function actionEditUser($id)
    {
        $model = User::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('The requested user does not exist.');
        }
        if ($model->load(Yii::$app->request->post())) {
            if (!empty($model->edit_pas)) {
                $model->setPassword($model->edit_pas);
            }
            if ($model->save()) {
                return $this->redirect(['list-user']);
            } else {
                throw new ForbiddenHttpException('Failed to save details. Try again.');
            }
        }
        return $this->render('edit_user', ['user' => $model]);
    }

    /*
    * Delete user details
    */
    public function actionDeleteUser($id)
    {
        $flag = true;
        $model = User::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('The requested user does not exist.');
        }
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        $fsusers = Fsusertb::find()->where(['user_id' => $id])->all();
        if ($fsusers) {
            foreach ($fsusers as $fsuser) {
                if (!$fsuser->delete()) {
                    $flag = false;
                    break;
                }
            }
        }
        if ($flag) {
            if (!$model->delete()) {
                $flag = false;
            }
        }

        if ($flag) {
            $transaction->commit();
            return $this->redirect(['list-user']);
        } else {
            $transaction->rollBack();
            throw new ForbiddenHttpException('Failed to delete user Try again.');
        }
    }

    /*
    * Show CDR details
    */

    public function actionCdr()
    {
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

        $query = Fsmycdr::find()
            ->select(['call_startdate', 'ani', 'called_number', 'cld1', 'country', 'call_duration', '(cld2_ratepersec * (call_duration/60)) AS Cost', '(cld3_ratepersec * (call_duration/60)) AS Sale', 'agent_id'])
            ->innerJoin('user', '`user`.`id` = `fsmycdr`.`reseller_id`')
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

    /*
    * Datewise connected call report
    */
    public function actionDateReport()
    {
        $date_range = isset($_GET['date_range']) ? $_GET['date_range'] : date('Y-m-d', strtotime("-6 days")) . " to " . date('Y-m-d');

        if (strpos($date_range, " to ") !== FALSE) {
            $date = explode(" to ", $date_range);
            $start = $date[0] . " 00:00:00";
            $end = $date[1] . " 23:59:59";
        } else {
            $start = date('Y-m-d', strtotime("-6 days")) . " 00:00:00";
            $end = date('Y-m-d') . " 23:59:59";
        }
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => "SELECT DATE_FORMAT(call_getdate, '%d-%m-%Y') AS `date`,COUNT(*) as call_count, SUM(`call_duration`) as minute,sum((call_duration/60)*cld3_ratepersec), sum((cld2_ratepersec/60)*call_duration) as sum FROM `fscdr` join user on user.id = fscdr.agent_id WHERE `fsmid` !='' AND `call_getdate` BETWEEN '$start' AND '$end' and user.reseller_id = " . Yii::$app->user->identity->id . " GROUP BY DATE_FORMAT(call_getdate, '%d-%m-%Y') ORDER BY `call_getdate` DESC",
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        $sql = "SELECT COUNT(*) as call_count, SUM(`call_duration`) as minute,sum((call_duration/60)*cld3_ratepersec) as sale , sum((cld2_ratepersec/60)*call_duration)) as sum FROM `fscdr` join user on user.id = fscdr.agent_id WHERE `fsmid` !='' AND `call_getdate` BETWEEN '" . $start . "' AND '" . $end . "' and user.reseller_id = " . Yii::$app->user->identity->id;

        $result = Fscdr::findBySql($sql)->one();

        if ($result) {
            $connected_calls = $result->call_count;
            $minutes = round($result->minute / 60, 2);
            $total_cost = round($result->sum, 4);
        } else {
            $connected_calls = 0;
            $minutes = 0;
            $total_cost = 0;
        }

        return $this->render('date_wise_report', ['dataProvider' => $dataProvider, 'date_range' => $date_range, 'connected_calls' => $connected_calls, 'minutes' => $minutes, 'total_cost' => $total_cost]);
    }

    public function actionExportData()
    {
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

        $query = Fsmycdr::find()
            ->select(['call_startdate', 'ani', 'called_number', 'cld1', 'country', 'call_duration', '(cld3_ratepersec * (call_duration/60)) AS Charges', '(cld2_ratepersec * (call_duration/60)) AS Cost', 'agent_id'])
            ->innerJoin('user', '`user`.`id` = `fsmycdr`.`agent_id`')
            ->where(['between', 'call_startdate', $startT, $endT])
            ->with(['user'])
            ->andWhere(['=', 'user.reseller_id', Yii::$app->user->identity->id]);

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

            echo 'call_startdate' . "\t" . 'ani' . "\t" . 'called_number' . "\t" . 'country' . "\t" . 'Call Duration' . "\t" . 'Charges' . "\t" . 'Cost' . "\t" . 'Margin' . "\n";
            if ($query)
                foreach ($query as $value) {

                    echo $value->call_startdate . "\t" . $value->ani . "\t" . $value->called_number . "\t" . $value->country . "\t" . round($value->call_duration / 60, 2) . "\t" . $value->Charges . "\t" . $value->Cost . "\t" . round($value->Charges - $value->Cost, 4) . "\n";
                }
            exit;
        } else {
            return $this->redirect(['cdr']);
        }
    }

    /*
    * List of all users who holds a specific number
    */
    public function actionShowNumberRoutes($id)
    {
        $model = Fsmastertb::find()->where(['fsmid' => $id])->one();
        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => Fsusertb::find()->where(['cld1' => $model->cld1])->orderBy(['closing_date' => 'SORT_DESC']),
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        return $this->render('number_routes', ['dataProvider' => $dataProvider]);
    }

    public function actionFsCallReport()
    {
        $agent_id = isset($_GET['agent']) ? $_GET['agent'] : '';
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
        $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id]);
        $query = Fscallreport::find()
            ->where(['agent_id' => $mysubusr]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        $query->andFilterWhere([
            'agent_id' => $agent_id,
            'Country' => $country_name,
            'Caller_ID' => $caller_id,
            'Cld1' => $cld_1,
        ]);
        $query->andFilterWhere(['like', 'Cld1_Rate', $cld1_rate])
            ->andFilterWhere(['like', 'Cld2_Rate', $cld2_rate])
            ->andFilterWhere(['like', 'called_number', $called_num]);

        //echo $query->createCommand()->getRawSql(); exit();

        //if ($flag) {
        $query->andFilterWhere(['between', 'Date', $start . ' 00:00:00', $end . ' 23:59:59']);
        //}


        $totalColls = $query->sum('Total_Calls');
        $Call_Duration = $query->sum('Call_Duration');
        $Charges = $query->sum('Charges');
        $Cost = $query->sum('cld2_cost');
        $Sale = $query->sum('cld3_cost');

        $agent = Fscallreport::find()->groupBy(['agent_id'])
            ->where(['agent_id' => $mysubusr])
            ->andFilterWhere(['between', 'Date', $start, $end])->all();
        $country = Fscallreport::find()->groupBy(['Country'])
            ->where(['agent_id' => $mysubusr])
            ->andFilterWhere(['between', 'Date', $start, $end])->all();
        $Caller_ID = Fscallreport::find()->groupBy(['Caller_ID'])
            ->where(['agent_id' => $mysubusr])
            ->andFilterWhere(['between', 'Date', $start, $end])->all();
        $cld1 = Fscallreport::find()->groupBy(['Cld1'])
            ->where(['agent_id' => $mysubusr])
            ->andFilterWhere(['between', 'Date', $start, $end])->limit(1000)->all();


        return $this->render(
            'fs_call_report',
            [
                'dataProvider' => $dataProvider,
                'agent' => $agent,
                'country' => $country,
                'caller_id' => $Caller_ID,
                'cld1' => $cld1,
                'agent_id' => $agent_id,
                'country_name' => $country_name,
                'callerId' => $caller_id,
                'cld_1' => $cld_1,
                'cld1_rate' => $cld1_rate,
                'cld2_rate' => $cld2_rate,
                'totalColls' => $totalColls,
                'Call_Duration' => $Call_Duration,
                'Charges' => $Charges,
                'Cost' => $Cost,
                'Sale' => $Sale,
                'Datepickr' => $Datepickr,
                'called_num' => $called_num
            ]
        );
    }

    /*
    * Load dropdown in seach fields in fscalreport oage
    */
    public function actionLoadSearchFields()
    {
        $agent_optn = "<option value=''>Agent</option>";
        $country_optn = "<option value=''>Country</option>";
        $callerId_optn = "<option value=''>Caller Id</option>";
        $cld1_optn = "<option value=''>Cld1</option>";

        $start = Yii::$app->request->post('start');
        $end = Yii::$app->request->post('end');
        $agents = Fscallreport::find()->groupBy(['agent_id'])->where(['between', 'Date', $start, $end])->all();
        $country = Fscallreport::find()->groupBy(['Country'])->where(['between', 'Date', $start, $end])->all();
        $Caller_ID = Fscallreport::find()->groupBy(['Caller_ID'])->where(['between', 'Date', $start, $end])->all();
        $cld1 = Fscallreport::find()->groupBy(['Cld1'])->where(['between', 'Date', $start, $end])->limit(1000)->all();

        if ($agents) {
            foreach ($agents as $value) {
                $agent_optn .= '<option value="' . $value->agent_id . '">' . $value->agent->username . '</option>';
            }
        }

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
            'agent_optn' => $agent_optn,
            'country_optn' => $country_optn,
            'callerId_optn' => $callerId_optn,
            'cld1_optn' => $cld1_optn,

        ];
    }

    /*
    * Export fscall report
    */
    public function actionExportFscall()
    {
        $agent_id = isset($_GET['agent']) ? $_GET['agent'] : '';
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
            //$flag = true;
            //$Datepickr = $start.' - '.$end;
        } else {
            $start = date('Y-m-d');
            $end = date('Y-m-d');
            //$flag = false;
            //$Datepickr = '';
        }
        $Datepickr = $start . ' - ' . $end;

        $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id]);
        $query = Fscallreport::find()
            ->where(['agent_id' => $mysubusr]);

        $query->andFilterWhere([
            'agent_id' => $agent_id,
            'Country' => $country_name,
            'Caller_ID' => $caller_id,
            'Cld1' => $cld_1,
        ]);
        $query->andFilterWhere(['like', 'Cld1_Rate', $cld1_rate])
            ->andFilterWhere(['like', 'Cld2_Rate', $cld2_rate])
            ->andFilterWhere(['like', 'called_number', $called_num]);

        //echo $query->createCommand()->getRawSql(); exit();

        //if ($flag) {
        $query->andFilterWhere(['between', 'Date', $start . ' 00:00:00', $end . ' 23:59:59']);
        //}

        $query = $query->all();

        if ($query) {

            $filename = "fscall_report.xls";
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");

            echo 'Date' . "\t" . 'Agent' . "\t" . 'Country' . "\t" . 'Caller ID' . "\t" . 'called_number' . "\t" . 'Cld1' . "\t" . 'Cld1 Rate' . "\t" . 'Cld2 Rate' . "\t" . 'Total Calls' . "\t" . 'Call Duration' . "\t" . 'Charges' . "\t" . ' Cost' . "\n";

            foreach ($query as $value) {
                echo $value->Date . "\t" . $value->agent->username . "\t" . $value->Country . "\t" . $value->Caller_ID . "\t" . $value->called_number . "\t" . $value->Cld1 . "\t" . $value->Cld1_Rate . "\t" . $value->Cld2_Rate . "\t" . $value->Total_Calls . "\t" . $value->Call_Duration . "\t" . $value->Charges . "\t" . $value->Cost . "\n";
            }
            exit;
        } else {
            return $this->redirect(['fs-call-report']);
        }
    }

    /*
    * Agent wise Summary
    */
    public function actionAgentSummary()
    {
        $agent_id = isset($_GET['agent']) ? $_GET['agent'] : '';
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
        $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id]);
        $query = Fscallreport::find()->select('agent_id,Country,SUM(Total_Calls) As Total_Calls,SUM(Call_Duration) AS Call_Duration,SUM(Charges) AS Charges,SUM(Cost) AS Cost, (SUM(Charges) -SUM(Cost)) AS margin, SUM(cld1_cost) as cld1_cost, SUM(cld2_cost) as cld2_cost')
            ->where(['agent_id' => $mysubusr])
            ->groupBy(['agent_id', 'Country']);
        //echo $query->createCommand()->getRawSql(); exit();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        $query->andFilterWhere([
            'agent_id' => $agent_id,
            'Country' => $country_id,
        ]);
        $query->andFilterWhere(['between', 'Date', $start, $end]);

        $totalColls = $query->sum('Total_Calls');
        $Call_Duration = $query->sum('Call_Duration');
        $Charges = $query->sum('Charges');
        $Cost = $query->sum('Cost');


        $agent = Fscallreport::find()->groupBy(['agent_id'])
            ->where(['agent_id' => $mysubusr])->all();
        $country = Fscallreport::find()->groupBy(['Country'])
            ->where(['agent_id' => $mysubusr])->all();

        return $this->render('agent_summary', ['dataProvider' => $dataProvider, 'date_range' => $date_range, 'agent' => $agent, 'country' => $country, 'agent_id' => $agent_id, 'country_id' => $country_id, 'totalColls' => $totalColls, 'Call_Duration' => $Call_Duration, 'Charges' => $Charges, 'Cost' => $Cost]);
    }

    /*
    * Export agent summary report
    */
    public function actionExportAgentSummary()
    {
        $agent_id = isset($_GET['agent']) ? $_GET['agent'] : '';
        $country_name = isset($_GET['country']) ? $_GET['country'] : '';
        $date_range = isset($_GET['date_range']) ? $_GET['date_range'] : date('Y-m-d', strtotime("-6 days")) . " to " . date('Y-m-d');

        if (strpos($date_range, " to ") !== FALSE) {
            $date = explode(" to ", $date_range);
            $start = $date[0] . " 00:00:00";
            $end = $date[1] . " 23:59:59";
        } else {
            $start = date('Y-m-d', strtotime("-6 days")) . " 00:00:00";
            $end = date('Y-m-d') . " 23:59:59";
        }

        $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id]);
        $query = Fscallreport::find()->select('agent_id,Country,SUM(Total_Calls) As Total_Calls,SUM(Call_Duration) AS Call_Duration,SUM(Charges) AS Charges,SUM(Cost) AS Cost, (SUM(Charges) -SUM(Cost)) AS margin, SUM(cld1_cost) as cld1_cost, SUM(cld2_cost) as cld2_cost')->groupBy(['Country'])
            ->where(['agent_id' => $mysubusr]);
        $query->andFilterWhere([
            'agent_id' => $agent_id,
            'Country' => $country_name,
        ]);
        $query->andFilterWhere(['between', 'Date', $start, $end]);

        $query = $query->all();

        if ($query) {

            $filename = "agent_summary_report.xls";
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");

            echo 'Agent' . "\t" . 'Country' . "\t" . 'Total Calls' . "\t" . 'Total Minutes' . "\t" . 'Total Charges' . "\t" . 'Total Agent Cost' . "\t" . 'Margin' . "\t" . 'Cld1 Cost' . "\t" . 'Cld2_Cost' . "\n";

            foreach ($query as $value) {
                echo $value->agent->username . "\t" . $value->Country . "\t" . $value->Total_Calls . "\t" . $value->Call_Duration . "\t" . $value->Charges . "\t" . $value->Cost . "\t" . $value->margin . "\t" . $value->cld1_cost . "\t" . $value->cld2_cost . "\n";
            }
            exit;
        } else {
            return $this->redirect(['agent-summary']);
        }
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
        $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id, 'role' => 2]);
        $summary = $model->getSummary($mysubusr, false, false, true);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search);
        $dataProvider->pagination->pageSize = $filter;

        return $this->render('sms_numbers', [
            'dataProvider' => $dataProvider, 
            'searchModel' => $searchModel,
            'summary' => $summary,
            'countries' => $this->getCountryItems(),
            'billgroups' => $this->getBillgroupItems(),
            'agents' => $this->getAgentItems(),
            'services' => $this->getServicesItems()
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
    protected function getAgentItems()
    {
        $items = [0 => "Un-allocated"];
        $res = User::find()->where(['role' => 2, 'reseller_id' => \Yii::$app->user->id])->all();
        if(is_array($res) && count($res) > 0)
        {
            foreach($res as $v)
            {
                $items[$v->id] = $v->username;
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


    /*
    * Asign a cld to user
    */
    public function actionAssignCld()
    {
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $limit = isset($_GET['limit']) ? !empty($_GET['limit']) ? $_GET['limit'] : 20 : 20;
        $model = new Fsusertb();

        $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id, 'role' => 2]);

        /*$fsuser = Fsusertb::find()
        ->select('cld1')->where(['closing_date' => NULL])
        ->andFilterWhere(['in', 'user_id', $mysubusr]);
        $query = Fsmastertb::find()->where(['like','cld1' , $search])->andFilterWhere(['not in','cld1',$fsuser])->limit($limit);*/

        $query = Fsmastertb::find()->where(['agent_id' => 0])->andFilterWhere(['reseller_id' => Yii::$app->user->identity->id]);
        if ($search) {
            $query = $query->andFilterWhere(['like', 'cld1', $search]);
        }
        $query = $query->limit($limit);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $users = $model->getUserList($mysubusr);

        $summary = $model->getSummary($mysubusr, false, false, true);
        // $mstrtb_cnt = Fsmastertb::find()->count();
        // $usrtb_cnt = Fsusertb::find()->innerJoin('user', '`user`.`id` = `fsusertb`.`user_id`')
        // ->with(['user'])
        // ->andWhere(['=', 'user.reseller_id', Yii::$app->user->identity->id])->count();
        // $summary['available'] = $mstrtb_cnt-$usrtb_cnt;
        $summary['available'] = $summary['stock'] - $summary['assigned'];

        return $this->render('assign_cld', ['users' => $users, 'summary' => $summary, 'dataProvider' => $dataProvider, 'search' => $search, 'limit' => $limit]);
    }

    /*
    * Show all assigned number to a user
    */
    public function actionShowAssigned()
    {
        $model = new Fsusertb();
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : '';
        $cld1 = isset($_GET['cld1']) ?  $_GET['cld1']  : '';

        $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id]);
        $query = Fsusertb::find()->where(['closing_date' => NULL])
            ->andFilterWhere(['in', 'user_id', $mysubusr]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $query->andFilterWhere(['like', 'cld1', $cld1]);
        $query->andFilterWhere(['user_id' =>  $userId]);

        $users = $model->getUserList($mysubusr);

        return $this->render('detach_number', ['dataProvider' => $dataProvider, 'users' => $users, 'userId' => $userId, 'cld1' => $cld1]);
    }

    /*
		* Detach an assigned number from a user
		*/
    public function actionDetachNumber()
    {
        //$id = Yii::$app->request->post('btn_id');
        $numbers = Yii::$app->request->post('btn_number');
        $query = Yii::$app->db->createCommand('
			UPDATE fsusertb SET closing_date = "' . date('Y-m-d H:i:s') . '" WHERE cld1 IN(' . $numbers . ');
			UPDATE fsmastertb SET agent_id = 0 WHERE cld1 IN(' . $numbers . ');')
            //->bindValue(':numbers', $numbers)
            ->execute();
        if ($query) {
            return $this->redirect(['show-assigned']);
        } else {
            throw new ForbiddenHttpException('Failed to detach number, Try again.');
        }
    }

    /*
		* Check calls send to this number
		* Input fsuser table id
		*/
    public function actionCheckCallsend()
    {
        $id = Yii::$app->request->post('id');
        $model = Fsusertb::findOne($id);
        if (!$model) {
            return json_encode([
                'error' => true,
                'message' => 'Data not available.'
            ]);
        }
        //['>=','call_startdate',$cld->assigned_date]
        $fscdr = Fscdr::find()->where(['>=', 'call_startdate', $model->assigned_date])->sum('call_duration');
        return json_encode([
            'error' => false,
            'message' => 'Total of ' . round($fscdr / 60, 2) . ' Minutes has been made through this number. Are you sure want to detach?.',
        ]);
    }

    /*
    * Edit cld
    */
    public function actionUpdateCld($id)
    {
        $model = Fsmastertb::find()->where(['fsmid' => $id])->one();
        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(['sms-numbers']);
            }
        }
        return $this->render('update_cld', ['model' => $model]);
    }

    public function actionFsTest()
    {
        $foldername = array_reverse(explode("/", getcwd()))[0];
        $brandname = explode(".", $_SERVER['SERVER_NAME'])[0];
        $findBrand = Brandname::findOne(['foldername' => $foldername, 'name' => $brandname]);

        $searchModel = new FstestSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $findBrand ? $findBrand->admin_id : 0);

        return $this->render('fs_test', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionEditNumber()
    {
        $numbers = Yii::$app->request->post('btn_number');
        $cld3rate = Yii::$app->request->post('cld3rate');
        if (!empty($numbers) && $cld3rate) {
            //$query = Yii::$app->db->createCommand('UPDATE fsmastertb SET cld3rate = '.$cld3rate.' WHERE fsmid IN(:numbers);')->bindValue(':numbers', $numbers)->execute();
            $query = Yii::$app->db->createCommand('UPDATE fsmastertb SET cld3rate = ' . $cld3rate . ' WHERE fsmid IN(' . $numbers . ');')
                //->bindValue(':numbers', $numbers)
                ->execute();
            if ($query) {
                return $this->redirect(['sms-numbers']);
            } else {
                throw new ForbiddenHttpException('Failed to edit number, Try again.');
            }
        } else {
            throw new ForbiddenHttpException('cld3rate field should not be empty, Try again.');
        }
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
            'services' => $this->getServicesItems(),
            'suppliers' => $this->getSupplierItems(),
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
        $res = \app\models\Currency::find()->all();
        return \yii\helpers\ArrayHelper::map($res, 'id', 'currency');
    }
    protected function getBillcycleItems()
    {
        $res = \app\models\Billcycle::find()->all();
        return \yii\helpers\ArrayHelper::map($res, 'ID', 'billcycle');
    }

    protected function getSupplierItems()
    {
        $res = Supplier::find()->all();
        return \yii\helpers\ArrayHelper::map($res, 'id', 'name');
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

        $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id, 'role' => 2]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search, false);
        $dataProvider->setPagination(['pageSize' => $filter]); 

        return $this->render('tdr', [
            'dataProvider' => $dataProvider, 
            'searchModel' => $searchModel,
            'search' => $search, 
            'filter' => $filter,
            'billgroups' => $this->getBillgroupItems(),
            'agents' => $this->getAgentItems(),
            'suppliers' => $this->getSupplierItems(),
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

        $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id, 'role' => 2]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search, false, false);
        $dataProvider->setPagination(['pageSize' => $filter]); 

        return $this->render('summary_report', [
            'dataProvider' => $dataProvider, 
            'searchModel' => $searchModel,
            'search' => $search, 
            'filter' => $filter,
            'agents' => $this->getAgentItems(),
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

        $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id, 'role' => 2]);
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
            //'clients' => $this->getResellerAdminItems(),
            'agents' => $this->getAgentItems(),
            //'suppliers' => $this->getSupplierItems(),
            'billgroups' => $this->getBillgroupItems(),
        ]);
    }



}