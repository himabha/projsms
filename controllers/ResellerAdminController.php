<?php

namespace app\controllers;

use app\models\BillgroupSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use app\models\Fsmastertb;
use app\models\Fsusertb;
use app\models\Fsresellertb;
use app\models\Fsadmintb;
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
use app\models\FsusertbSearch;
use app\models\FsmastertbSearch;
use app\models\Fsmycdr;
use yii\helpers\ArrayHelper;
use yii\db\Query;
use app\models\Fscallreport;
use app\models\Brandname;
use app\models\Country;
use app\models\FsaccessSearch;
use app\models\Supplier;

use app\models\TdrSearch;
use app\models\TdrSearchSummary;
use app\models\TdrSearchDetailed;

use app\models\Billgroup;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use app\models\Smscdr;


class ResellerAdminController extends \yii\web\Controller
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
                'only' => ['add-user', 'delete-user', 'cdr', 'list-user', 'edit-user', 'date-report', 'detach-number', 'show-number-routes', 'fs-call-report', 'export-fscall', 'reseller-summary', 'export-reseller-summary', 'load-search-fields'],
                'rules' => [
                    [
                        'actions' => ['add-user', 'delete-user', 'cdr', 'list-user', 'edit-user', 'date-report', 'detach-number', 'show-number-routes', 'fs-call-report', 'export-fscall', 'reseller-summary', 'export-reseller-summary', 'load-search-fields'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return User::isResellerAdmin(Yii::$app->user->identity->id);
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
     * Displays active calls for reseller admin.
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

        $myclds = Fsmastertb::find()->select('cld1')->where(['admin_id' => Yii::$app->user->identity->id]);
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
    * Add reseller
    */
    public function actionAddReseller()
    {
        $flag = 0;
        $user = new User();
        if ($user->load(Yii::$app->request->post())) {
            $id = $user->getIdValue();
            $user->id = $id;
            $user->setResellerId(Yii::$app->user->identity->id);
            $user->setPassword($user->password);
            $user->role = 3;
            if ($user->save()) {
                $user = new User();
                Yii::$app->session->setFlash('reseller_add_success', "Reseller added successfully.");
            } else {
                $user->password = "";
                Yii::$app->session->setFlash('reseller_add_failed', "Failed to save detail try again.");
            }
        }
        return $this->render('add_reseller', ['reseller' => $user]);
    }

    /*
    * List all Users
    */
    public function actionListReseller()
    {
        $name = isset($_GET['name']) ? $_GET['name'] : '';
        $query = User::find()->where(['role' => 3, 'reseller_id' => Yii::$app->user->identity->id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        $query->andFilterWhere(['like', 'username', $name]);

        return $this->render('list_reseller', ['dataProvider' => $dataProvider, 'name' => $name]);
    }

    /*
    * Assign cld number to user
    */
    public function actionAssignNumber()
    {
        $user = Yii::$app->request->post('user');
        $numbers = explode(",", Yii::$app->request->post('numbers'));

        foreach ($numbers as $key => $value) {


            $admintbmodel = new Fsadmintb();
            $admintbmodel->cld1 = $value;
            $admintbmodel->reseller_id = $user;
            $admintbmodel->save();

            $resellertbmodel = new Fsresellertb();
            $resellertbmodel->cld1 = $value;
            $resellertbmodel->reseller_id = $user;
            $resellertbmodel->assigned_date = date("Y-m-d H:i:s");
            $resellertbmodel->save();

            $mastertbmodel = Fsmastertb::findOne(['cld1' => $value]);
            $mastertbmodel->reseller_id = $user;
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
            // ->select(['call_startdate','ani','called_number' , 'cld1' ,'country' , 'call_duration', '(cld1_ratepersec * (call_duration/60)) AS Charges', '(cld2_ratepersec * (call_duration/60)) AS Cost', 'fsmycdr.reseller_id'])
            ->select(['call_startdate', 'ani', 'called_number', 'cld1', 'country', 'call_duration', '(cld1_ratepersec * (call_duration/60)) AS Cost', '(cld2_ratepersec * (call_duration/60)) AS Sale', 'fsmycdr.reseller_id'])
            ->innerJoin('user', '`user`.`id` = `fsmycdr`.`admin_id`')
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
            'sql' => "SELECT DATE_FORMAT(call_getdate, '%d-%m-%Y') AS `date`,COUNT(*) as call_count, SUM(`call_duration`) as minute,sum((call_duration/60)*cld2_ratepersec) as sale, sum((cld1_ratepersec/60)*call_duration) as sum FROM `fscdr` join user on user.id = fscdr.reseller_id WHERE `fsmid` !='' AND `call_getdate` BETWEEN '$start' AND '$end' and user.reseller_id = " . Yii::$app->user->identity->id . " GROUP BY DATE_FORMAT(call_getdate, '%d-%m-%Y') ORDER BY `call_getdate` DESC",
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        $sql = "SELECT COUNT(*) as call_count, SUM(`call_duration`) as minute,sum((call_duration/60)*cld2_ratepersec) as sale , sum((cld1_ratepersec/60)*call_duration) as sum FROM `fscdr` join user on user.id = fscdr.reseller_id WHERE `fsmid` !='' AND `call_getdate` BETWEEN '" . $start . "' AND '" . $end . "' and user.reseller_id = " . Yii::$app->user->identity->id;

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
            ->select(['call_startdate', 'ani', 'called_number', 'cld1', 'country', 'call_duration', '(cld1_ratepersec * (call_duration/60)) AS Charges', '(cld2_ratepersec * (call_duration/60)) AS Cost', '`fsmycdr`.reseller_id'])
            ->innerJoin('user', '`user`.`id` = `fsmycdr`.`reseller_id`')
            ->where(['between', 'call_startdate', $startT, $endT])
            ->with(['user'])
            ->andWhere(['=', 'user.id', Yii::$app->user->identity->id]);

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
        $reseller_id = isset($_GET['reseller']) ? $_GET['reseller'] : '';
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
            ->where(['reseller_id' => $mysubusr]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        $query->andFilterWhere([
            'reseller_id' => $reseller_id,
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
        $Cost = $query->sum('cld1_cost');
        $Sale = $query->sum('cld2_cost');


        $reseller = Fscallreport::find()->groupBy(['reseller_id'])
            ->where(['reseller_id' => $mysubusr])
            ->andFilterWhere(['between', 'Date', $start, $end])->all();
        $country = Fscallreport::find()->groupBy(['Country'])
            ->where(['reseller_id' => $mysubusr])
            ->andFilterWhere(['between', 'Date', $start, $end])->all();
        $Caller_ID = Fscallreport::find()->groupBy(['Caller_ID'])
            ->where(['reseller_id' => $mysubusr])
            ->andFilterWhere(['between', 'Date', $start, $end])->all();
        $cld1 = Fscallreport::find()->groupBy(['Cld1'])
            ->where(['reseller_id' => $mysubusr])
            ->andFilterWhere(['between', 'Date', $start, $end])->limit(1000)->all();

        return $this->render(
            'fs_call_report',
            [
                'dataProvider' => $dataProvider,
                'reseller' => $reseller,
                'country' => $country,
                'caller_id' => $Caller_ID,
                'cld1' => $cld1,
                'reseller_id' => $reseller_id,
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
        $reseller_optn = "<option value=''>Reseller</option>";
        $country_optn = "<option value=''>Country</option>";
        $callerId_optn = "<option value=''>Caller Id</option>";
        $cld1_optn = "<option value=''>Cld1</option>";

        $start = Yii::$app->request->post('start');
        $end = Yii::$app->request->post('end');
        $resellers = Fscallreport::find()->groupBy(['reseller_id'])->where(['between', 'Date', $start, $end])->all();
        $country = Fscallreport::find()->groupBy(['Country'])->where(['between', 'Date', $start, $end])->all();
        $Caller_ID = Fscallreport::find()->groupBy(['Caller_ID'])->where(['between', 'Date', $start, $end])->all();
        $cld1 = Fscallreport::find()->groupBy(['Cld1'])->where(['between', 'Date', $start, $end])->limit(1000)->all();

        if ($resellers) {
            foreach ($resellers as $value) {
                $reseller_optn .= '<option value="' . $value->reseller_id . '">' . $value->reseller->username . '</option>';
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
            'reseller_optn' => $reseller_optn,
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
        $reseller_id = isset($_GET['reseller']) ? $_GET['reseller'] : '';
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
            ->where(['reseller_id' => $mysubusr]);

        $query->andFilterWhere([
            'reseller_id' => $reseller_id,
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

            echo 'Date' . "\t" . 'Reseller' . "\t" . 'Country' . "\t" . 'Caller ID' . "\t" . 'called_number' . "\t" . 'Cld1' . "\t" . 'Cld1 Rate' . "\t" . 'Cld2 Rate' . "\t" . 'Total Calls' . "\t" . 'Call Duration' . "\t" . 'Charges' . "\t" . ' Cost' . "\n";

            foreach ($query as $value) {
                echo $value->Date . "\t" . $value->reseller->username . "\t" . $value->Country . "\t" . $value->Caller_ID . "\t" . $value->called_number . "\t" . $value->Cld1 . "\t" . $value->Cld1_Rate . "\t" . $value->Cld2_Rate . "\t" . $value->Total_Calls . "\t" . $value->Call_Duration . "\t" . $value->Charges . "\t" . $value->Cost . "\n";
            }
            exit;
        } else {
            return $this->redirect(['fs-call-report']);
        }
    }

    /*
    * Agent wise Summary
    */
    public function actionResellerSummary()
    {
        $reseller_id = isset($_GET['reseller']) ? $_GET['reseller'] : '';
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
        $query = Fscallreport::find()->select('reseller_id,Country,SUM(Total_Calls) As Total_Calls,SUM(Call_Duration) AS Call_Duration,SUM(Charges) AS Charges,SUM(Cost) AS Cost, (SUM(Charges) -SUM(Cost)) AS margin, SUM(cld1_cost) as cld1_cost, SUM(cld2_cost) as cld2_cost')
            ->where(['reseller_id' => $mysubusr])
            ->groupBy(['reseller_id', 'Country']);
        //echo $query->createCommand()->getRawSql(); exit();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        $query->andFilterWhere([
            'reseller_id' => $reseller_id,
            'Country' => $country_id,
        ]);
        $query->andFilterWhere(['between', 'Date', $start, $end]);

        $totalColls = $query->sum('Total_Calls');
        $Call_Duration = $query->sum('Call_Duration');
        $Charges = $query->sum('Charges');
        $Cost = $query->sum('Cost');


        $reseller = Fscallreport::find()->groupBy(['reseller_id'])
            ->where(['reseller_id' => $mysubusr])->all();
        $country = Fscallreport::find()->groupBy(['Country'])
            ->where(['reseller_id' => $mysubusr])->all();

        return $this->render('reseller_summary', ['dataProvider' => $dataProvider, 'date_range' => $date_range, 'reseller' => $reseller, 'country' => $country, 'reseller_id' => $reseller_id, 'country_id' => $country_id, 'totalColls' => $totalColls, 'Call_Duration' => $Call_Duration, 'Charges' => $Charges, 'Cost' => $Cost]);
    }

    /*
    * Export agent summary report
    */
    public function actionExportAgentSummary()
    {
        $reseller_id = isset($_GET['reseller']) ? $_GET['reseller'] : '';
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
        $query = Fscallreport::find()->select('reseller_id,Country,SUM(Total_Calls) As Total_Calls,SUM(Call_Duration) AS Call_Duration,SUM(Charges) AS Charges,SUM(Cost) AS Cost, (SUM(Charges) -SUM(Cost)) AS margin, SUM(cld1_cost) as cld1_cost, SUM(cld2_cost) as cld2_cost')->groupBy(['Country'])
            ->where(['reseller_id' => $mysubusr]);
        $query->andFilterWhere([
            'reseller_id' => $reseller_id,
            'Country' => $country_name,
        ]);
        $query->andFilterWhere(['between', 'Date', $start, $end]);

        $query = $query->all();

        if ($query) {

            $filename = "reseller_summary_report.xls";
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");

            echo 'Reseller' . "\t" . 'Country' . "\t" . 'Total Calls' . "\t" . 'Total Minutes' . "\t" . 'Total Charges' . "\t" . 'Total Reseller Cost' . "\t" . 'Margin' . "\t" . 'Cld1 Cost' . "\t" . 'Cld2_Cost' . "\n";

            foreach ($query as $value) {
                echo $value->reseller->username . "\t" . $value->Country . "\t" . $value->Total_Calls . "\t" . $value->Call_Duration . "\t" . $value->Charges . "\t" . $value->Cost . "\t" . $value->margin . "\t" . $value->cld1_cost . "\t" . $value->cld2_cost . "\n";
            }
            exit;
        } else {
            return $this->redirect(['reseller-summary']);
        }
    }

    /*
    * Add cld to users
    */
    public function actionSmsNumbers()
    {
        $model = new Fsusertb();
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 10;

        if ($filter == 'all') {
            $filter = '';
        }

        $searchModel = new FsmastertbSearch();
        $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id, 'role' => 3]);
        $summary = $model->getSummary($mysubusr, false, true);

        $billgroups = $this->getBillgroupItems();    
        if(is_array($billgroups) && count($billgroups) > 0)
        {
            $bg_id = array_key_first($billgroups);
            $bg = Billgroup::findOne($bg_id);
            if(empty(Yii::$app->request->queryParams))
            {
                $billgroups = $this->getBillgroupItems();    
                if(is_array($billgroups) && count($billgroups) > 0)
                {
                    $selected_billgroup_id = array_key_first($billgroups);
                    Yii::$app->request->queryParams = [
                        'FsmastertbSearch' => [
                            'billgroup_id' => $bg_id
                        ]
                    ];
                }
            }
            if(!isset(Yii::$app->request->queryParams['FsmastertbSearch']['billgroup_id']) || empty(Yii::$app->request->queryParams['FsmastertbSearch']['billgroup_id']))
            {
                $temp_arr = Yii::$app->request->queryParams;
                $temp_arr['FsmastertbSearch']['billgroup_id'] = $bg_id;
                Yii::$app->request->queryParams = $temp_arr;
            }
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search, User::isUserAdmin(\Yii::$app->user->id), User::isTestPanel(\Yii::$app->user->id));
        $dataProvider->pagination->pageSize = $filter;

        return $this->render('sms_numbers', [
            'dataProvider' => $dataProvider, 
            'searchModel' => $searchModel,
            'summary' => $summary,
            'countries' => $this->getCountryItems(),
            'billgroups' => $this->getBillgroupItems(),
            'resellers' => $this->getResellerItems(),
            'clients_only' => $this->getResellerItems(false),
            'services' => $this->getServicesItems(),
            'bg' => $bg
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

    protected function getResellerItems($include_unallocated = true)
    {
        $items = [];
        if($include_unallocated) $items = [0 => "Un-allocated"];
        $res = User::find()->where(['role' => 3, 'reseller_id' => \Yii::$app->user->id])->all();
        if(is_array($res) && count($res) > 0)
        {
            foreach($res as $v)
            {
                $items[$v->id] = $v->username;
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

        $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id, 'role' => 3]);
        $query = Fsmastertb::find()->where(['agent_id' => 0, 'reseller_id' => 0])->andFilterWhere(['admin_id' => Yii::$app->user->identity->id]);
        if ($search) {
            $query = $query->andFilterWhere(['like', 'cld1', $search]);
        }
        $query = $query->limit($limit);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $users = $model->getUserList($mysubusr);

        $summary = $model->getSummary($mysubusr, false, true);
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
        $model = new Fsresellertb();
        $resellerId = isset($_GET['reseller_id']) ? $_GET['reseller_id'] : '';
        $cld1 = isset($_GET['cld1']) ?  $_GET['cld1']  : '';

        $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id, 'role' => 3]);
        $query = Fsresellertb::find()->where(['closing_date' => NULL])
            ->andFilterWhere(['in', 'reseller_id', $mysubusr]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $query->andFilterWhere(['like', 'cld1', $cld1]);
        $query->andFilterWhere(['reseller_id' =>  $resellerId]);

        $resellers = $model->getResellerList($mysubusr);

        return $this->render('detach_number', ['dataProvider' => $dataProvider, 'resellers' => $resellers, 'resellerId' => $resellerId, 'cld1' => $cld1]);
    }

    /*
    * Detach an assigned number from a user
    */
    public function actionDetachNumber()
    {
        //$id = Yii::$app->request->post('btn_id');
        $numbers = Yii::$app->request->post('btn_number');
        $query = Yii::$app->db->createCommand('
        UPDATE fsresellertb SET closing_date = "' . date('Y-m-d H:i:s') . '" WHERE cld1 IN(' . $numbers . ');
        UPDATE fsusertb SET closing_date = "' . date('Y-m-d H:i:s') . '" WHERE cld1 IN(' . $numbers . ');
        UPDATE fsmastertb SET reseller_id = 0, agent_id = 0 WHERE cld1 IN(' . $numbers . ');')
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
        $model = Fsresellertb::findOne($id);
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
        $cld2Rate = Yii::$app->request->post('cld2Rate');
        if (!empty($numbers) && $cld2Rate) {
            $query = Yii::$app->db->createCommand('UPDATE fsmastertb SET cld2rate = ' . $cld2Rate . ' WHERE fsmid IN(' . $numbers . ');')
                //->bindValue(':numbers', $numbers)
                ->execute();
            if ($query) {
                return $this->redirect(['sms-numbers']);
            } else {
                throw new ForbiddenHttpException('Failed to edit number, Try again.');
            }
        } else {
            throw new ForbiddenHttpException('cld2rate field should not be empty, Try again.');
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
            'billcycles' => $this->getBillcycleItems()
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


    public function actionSmsTdr()
    {
        $model = new Fsusertb();
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 10;
        $mysubusr = User::find()->select('id')->where(['role' => 2]);

        if ($filter == 'all') {
            $filter = '';
        }

        $searchModel = new TdrSearch();

        $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id, 'role' => 3]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search, false);
        $dataProvider->setPagination(['pageSize' => $filter]); 

        return $this->render('tdr', [
            'dataProvider' => $dataProvider, 
            'searchModel' => $searchModel,
            'search' => $search, 
            'filter' => $filter,
            'billgroups' => $this->getBillgroupItems(),
            'resellers' => $this->getResellerItems(),
        ]);
    }


    public function actionSummaryReport()
    {
        $model = new Fsusertb();
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 10;

        if ($filter == 'all') {
            $filter = '';
        }

        $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id, 'role' => 3]);

        $searchModel = new TdrSearchSummary();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search, false, false);
        $dataProvider->setPagination(['pageSize' => $filter]); 

        $searchModel_1 = new TdrSearchDetailed();
        $dataProvider_1 = $searchModel_1->search(Yii::$app->request->queryParams, $mysubusr, $search, false, false);
        $dataProvider_1->setPagination(['pageSize' => $filter]); 

        return $this->render('summary_report', [
            'dataProvider' => $dataProvider, 
            'dataProvider_1' => $dataProvider_1,
            'search' => $search, 
            'filter' => $filter,
            'resellers' => $this->getResellerItems(),
            'billgroups' => $this->getBillgroupItems(),
        ]);
    }

    public function actionDetailedReport()
    {
        $model = new Fsusertb();
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 10;

        if ($filter == 'all') {
            $filter = '';
        }

        $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id, 'role' => 3]);
        $searchModel = new TdrSearchSummary();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search, false, true);
        $dataProvider->setPagination(['pageSize' => $filter]); 

        $searchModel_1 = new TdrSearchDetailed();
        $dataProvider_1 = $searchModel_1->search(Yii::$app->request->queryParams, $mysubusr, $search, false, true);
        $dataProvider_1->setPagination(['pageSize' => $filter]); 

        return $this->render('detail_report', [
            'dataProvider' => $dataProvider, 
            'dataProvider_1' => $dataProvider_1, 
            'search' => $search, 
            'filter' => $filter,
            'resellers' => $this->getResellerItems(),
            'billgroups' => $this->getBillgroupItems(),
        ]);
    }

    public function actionTdrExport()
    {
        $a_z = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P','Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y'. 'Z'];

        $headers = [
            'ID',
            'From Number',
            'To Number',
            'SMS Message',
            'Bill Group',
            'Client',
            'Delivered Time'
        ];

        if(isset($_SERVER['QUERY_STRING']))
        {
            $searchModel = new TdrSearch();
            $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id, 'role' => 3]);
            $query = $searchModel->search(\Yii::$app->request->queryParams, $mysubusr, '', false)->query;
            $params = isset(\Yii::$app->request->queryParams['TdrSearch']) ? \Yii::$app->request->queryParams['TdrSearch'] : [];

            $billgroup_name = 'None';
            if(!empty(intval($params['billgroup_id'])))
            {
                $obj = Billgroup::findOne(intval($params['billgroup_id']));
                if(isset($obj->name)) $billgroup_name = $obj->name;
            }

            $client_name = 'None';
            if(isset($params['reseller_id']) && trim($params['reseller_id']) != "")
            {
                if($params['reseller_id'] == 0)
                {
                    $client_name = 'Un-located';
                } else {
                    $obj = User::findOne(intval($params['reseller_id']));
                    if(isset($obj->username)) $client_name = $obj->username;
                }
            }

            $filters = [
                'Bill Group' => $billgroup_name,
                'Client' => $client_name,
                'Delivered Time' => !empty($params['delivered_time']) ? $params['delivered_time'] : 'None',
                'From Number' => !empty($params['from_number']) ? $params['from_number'] : 'None',
                'SMS Message' => !empty($params['sms_message']) ? $params['sms_message'] : 'None',
                'ID' => !empty($params['id']) ? $params['id'] : 'None',
            ];

        }

        $csv_cols = ["", "", "", "", "", "", "", ""];
        $csv_arr = [];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // title
        $row = 1;
        $col = 1;

        $sheet->setCellValueByColumnAndRow($col, $row , "TDR REPORT");
        $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
        $temp = $csv_cols;
        $temp[$col-1] = "TDR REPORT";
        $csv_arr[] = $temp;
        $row++; $csv_arr[] = $csv_cols;

        $sheet->setCellValueByColumnAndRow($col, $row , "Created " . date('Y-m-d H:i:s'));
        $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
        $temp = $csv_cols;
        $temp[$col-1] = "Created " . date('Y-m-d H:i:s');
        $csv_arr[] = $temp;
        $row++; $csv_arr[] = $csv_cols;
        $row++; $csv_arr[] = $csv_cols;

        $sheet->setCellValueByColumnAndRow($col, $row , "Filters");
        $temp = $csv_cols;
        $temp[$col-1] =  "Filters";
        $csv_arr[] = $temp;
        $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
        $row++; $csv_arr[] = $csv_cols;

        if(is_array($filters) && count($filters) > 0)
        {
            $col = 1;
            $temp1 = $csv_cols;
            $temp2 = $csv_cols;
            foreach($filters as $k=>$v)
            {
                $sheet->setCellValueByColumnAndRow($col, $row , $k);
                $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
                $temp1[$col-1] = $k;
                $sheet->setCellValueByColumnAndRow($col, $row + 1 , $v);
                $temp2[$col-1] = $v;
                $col++;
            }
            $csv_arr[] = $temp1;
            $csv_arr[] = $temp2;
            $row++; $csv_arr[] = $csv_cols;
            $row++; $csv_arr[] = $csv_cols;
            $row++; $csv_arr[] = $csv_cols;
        }

        if(is_array($headers) && count($headers) > 0)
        {
            $col = 1;
            $temp = $csv_cols;
            foreach($headers as $v)
            {
                $sheet->setCellValueByColumnAndRow($col, $row , $v);
                $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
                $temp[$col-1] = $v;
                $col++;
            }
            $csv_arr[] = $temp;
            $row++;
        }

        $rows = $query->all();
        if(is_array($rows) && count($rows) > 0)
        {
            foreach($rows as $v)
            {
                $temp = $csv_cols;
                foreach($headers as $hk => $hv)
                {
                    switch($hv)
                    {
                        case "ID":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->id) ? $v->id : "");
                            $temp[$hk] =  isset($v->id) ? $v->id : "";
                            break; 
                        case "From Number":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->from_number) ? $v->from_number : "");
                            $temp[$hk] = isset($v->from_number) ? $v->from_number : "";
                            break; 
                        case "To Number":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->to_number) ? $v->to_number : "");
                            $temp[$hk] = isset($v->to_number) ? $v->to_number : "";
                            break; 
                        case "SMS Message":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->sms_message) ? $v->sms_message : "");
                            $temp[$hk] = isset($v->sms_message) ? $v->sms_message : "";
                            break; 
                        case "Bill Group":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->billgroup) ? $v->billgroup->name : "");
                            $temp[$hk] = isset($v->billgroup) ? $v->billgroup->name : "";
                            break; 
                        case "Client":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->resellers) ? $v->resellers->username : "");
                            $temp[$hk] = isset($v->resellers) ? $v->resellers->username : "";
                            break; 
                        case "Delivered Time":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->delivered_time) ? date('d-m-Y H:i:s', strtotime($v->delivered_time)) : "");
                            $temp[$hk] = isset($v->delivered_time) ? date('d-m-Y H:i:s', strtotime($v->delivered_time)) : "";
                            break; 
                    }
                }
                $csv_arr[] = $temp;
                $row++;
            }                
        }

        if(\Yii::$app->request->queryParams['mode'] == 'csv')
        {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="tdr.csv"');
            ob_end_clean();
            $output = fopen('php://output', 'w');
            foreach ($csv_arr as $row) {
                fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($output, $row);
            }
            fclose($output);
            exit();
        } else {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="tdr.xlsx"');
            $writer = new Xlsx($spreadsheet);
            ob_end_clean();
            $writer->save("php://output");
            exit();
        }
        exit();
    }

    public function actionTdrSummaryExport()
    {
        $a_z = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P','Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y'. 'Z'];

        $headers_summary = [
            'Currency',
            'Msgs',
            'In',
            'Out',
            'Profit',
            '% Profit'
        ];

        $headers_result = [
            'Client',
            'Bill Group',
            'Msgs',
            'In',
            'Out',
            'Profit'
        ];

        if(isset($_SERVER['QUERY_STRING']))
        {
            // SUMMARY
            $searchModel = new TdrSearchSummary();
            $mysubusr = User::find()->select('id')->where(['role' => 2]);
            $query = $searchModel->search(\Yii::$app->request->queryParams, $mysubusr, '', false, false)->query;
            $params = isset(\Yii::$app->request->queryParams['TdrSearchSummary']) ? \Yii::$app->request->queryParams['TdrSearchSummary'] : [];
            // FILTERS
            $billgroup_name = 'None';
            if(!empty(intval($params['billgroup_id'])))
            {
                $obj = Billgroup::findOne(intval($params['billgroup_id']));
                if(isset($obj->name)) $billgroup_name = $obj->name;
            }
            $client_name = 'None';
            if(isset($params['reseller_id']) && trim($params['reseller_id']) != "")
            {
                if($params['reseller_id'] == 0)
                {
                    $client_name = 'Un-located';
                } else {
                    $obj = User::findOne(intval($params['reseller_id']));
                    if(isset($obj->username)) $client_name = $obj->username;
                }
            }
            $filters = [
                'Bill Group' => $billgroup_name,
                'Client' => $client_name,
                'Delivered Time' => !empty($params['delivered_time']) ? $params['delivered_time'] : 'None'
            ];

            $searchModel_2 = new TdrSearchDetailed();
            $query_2 = $searchModel_2->search(\Yii::$app->request->queryParams, $mysubusr, '', false, false)->query;
            $rows_2 = $query_2->all();

        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $csv_cols = ["", "", "", "", "", ""];
        $csv_arr = [];

        // title
        $row = 1;
        $col = 1;

        $sheet->setCellValueByColumnAndRow($col, $row , "TDR SUMMARY REPORT");
        $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
        $temp = $csv_cols;
        $temp[$col-1] = "TDR SUMMARY REPORT";
        $csv_arr[] = $temp;
        $row++; $csv_arr[] = $csv_cols;

        $sheet->setCellValueByColumnAndRow($col, $row , "Created " . date('Y-m-d H:i:s'));
        $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
        $temp = $csv_cols;
        $temp[$col-1] = "Created " . date('Y-m-d H:i:s');
        $csv_arr[] = $temp;
        $row++; $csv_arr[] = $csv_cols;
        $row++; $csv_arr[] = $csv_cols;

        $sheet->setCellValueByColumnAndRow($col, $row , "Filters");
        $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
        $temp = $csv_cols;
        $temp[$col-1] = "Filters";
        $csv_arr[] = $temp;
        $row++; $csv_arr[] = $csv_cols;

        if(is_array($filters) && count($filters) > 0)
        {
            $col = 1;
            $temp1 = $csv_cols;
            $temp2 = $csv_cols;
            foreach($filters as $k=>$v)
            {
                $sheet->setCellValueByColumnAndRow($col, $row , $k);
                $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
                $temp1[$col-1] = $k;
                $sheet->setCellValueByColumnAndRow($col, $row + 1 , $v);
                $temp2[$col-1] = $v;
                $col++;
            }
            $csv_arr[] = $temp1;
            $csv_arr[] = $temp2;
            $row++; $csv_arr[] = $csv_cols;
            $row++; $csv_arr[] = $csv_cols;
            $row++; $csv_arr[] = $csv_cols;
        }

        $col = 1;
        $sheet->setCellValueByColumnAndRow($col, $row , "SUMMARY");
        $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
        $temp = $csv_cols;
        $temp[$col-1] = "Summary";
        $csv_arr[] = $temp;
        $row++; $csv_arr[] = $csv_cols;
        $row++; $csv_arr[] = $csv_cols;

        if(is_array($headers_summary) && count($headers_summary) > 0)
        {
            $col = 1;
            $temp = $csv_cols;
            foreach($headers_summary as $v)
            {
                $sheet->setCellValueByColumnAndRow($col, $row , $v);
                $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
                $temp[$col-1] = $v;
                $col++;
            }
            $csv_arr[] = $temp;
            $row++;
        }

        $rows = $query->all();
        if(is_array($rows) && count($rows) > 0)
        {
            foreach($rows as $v)
            {
                $temp = $csv_cols;
                foreach($headers_summary as $hk => $hv)
                {
                    switch($hv)
                    {
                        case "Currency":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->currency) ? $v->currency : "");
                            $temp[$hk] = isset($v->currency) ? $v->currency : "";
                            break; 
                        case "Msgs":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->msgs) ? number_format($v->msgs,0) : 0);
                            $temp[$hk] = isset($v->msgs) ? number_format($v->msgs,0) : 0;
                            break; 
                        case "In":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->rev_in) ? number_format($v->rev_in, 2) : number_format(0, 2));
                            $temp[$hk] = isset($v->rev_in) ? number_format($v->rev_in, 2) : number_format(0, 2);
                            break; 
                        case "Out":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->rev_out) ? number_format($v->rev_out, 2) : number_format(0, 2));
                            $temp[$hk] = isset($v->rev_out) ? number_format($v->rev_out, 2) : number_format(0, 2);
                            break; 
                        case "Profit":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->profit) ? number_format($v->profit, 2) : number_format(0, 2));
                            $temp[$hk] = isset($v->profit) ? number_format($v->profit, 2) : number_format(0, 2);
                            break; 
                        case "% Profit":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->profit_percentage) ? number_format($v->profit_percentage, 2) : number_format(0, 2));
                            $temp[$hk] = isset($v->profit_percentage) ? number_format($v->profit_percentage, 2) : number_format(0, 2);
                            break; 
                    }
                }
                $csv_arr[] = $temp;
                $row++;
            }                
        }

        $row++; $csv_arr[] = $csv_cols;
        $row++; $csv_arr[] = $csv_cols;
        $col = 1;
        $sheet->setCellValueByColumnAndRow($col, $row , "RESULTS");
        $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
        $temp = $csv_cols;
        $temp[$col-1] = "RESULTS";
        $csv_arr[] = $temp;
        $row++; $csv_arr[] = $csv_cols;
        $row++; $csv_arr[] = $csv_cols;

        if(is_array($headers_result) && count($headers_result) > 0)
        {
            $col = 1;
            $temp = $csv_cols;
            foreach($headers_result as $v)
            {
                $sheet->setCellValueByColumnAndRow($col, $row , $v);
                $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
                $temp[$col-1] = $v;
                $col++;
            }
            $csv_arr[] = $temp;
            $row++;
        }

        if(is_array($rows_2) && count($rows_2) > 0)
        {
            foreach($rows_2 as $v)
            {
                $temp = $csv_cols;
                foreach($headers_result as $hk => $hv)
                {
                    switch($hv)
                    {
                        case "Client":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->resellers) ? $v->resellers->username : "");
                            $temp[$hk] =  isset($v->resellers) ? $v->resellers->username : "";
                            break; 
                        case "Bill Group":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->billgroup) ? $v->billgroup->name : "");
                            $temp[$hk] = isset($v->billgroup) ? $v->billgroup->name : "";
                            break; 
                        case "Msgs":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->msgs) ? number_format($v->msgs, 0) : 0);
                            $temp[$hk] = isset($v->msgs) ? number_format($v->msgs, 0) : 0;
                            break; 
                        case "In":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->rev_in) ? number_format($v->rev_in, 2) : number_format(0, 2));
                            $temp[$hk] = isset($v->rev_in) ? number_format($v->rev_in, 2) : number_format(0, 2);
                            break; 
                        case "Out":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->rev_out) ? number_format($v->rev_out, 2) : number_format(0, 2));
                            $temp[$hk] = isset($v->rev_out) ? number_format($v->rev_out, 2) : number_format(0, 2);
                            break; 
                        case "Profit":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->profit) ? number_format($v->profit, 2) : number_format(0, 2));
                            $temp[$hk] = isset($v->profit) ? number_format($v->profit, 2) : number_format(0, 2);
                            break; 
                        // case "% Profit":
                        //     $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->profit_percentage) ? number_format($v->profit_percentage, 2) : number_format(0, 2));
                        //     break; 
                    }
                }
                $csv_arr[] = $temp;
                $row++; 
            }                
        }


        if(\Yii::$app->request->queryParams['mode'] == 'csv')
        {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="tdr_summary.csv"');
            if(is_array($csv_arr) && count($csv_arr) > 0)
            {
                ob_end_clean();
                $output = fopen('php://output', 'w');
                foreach ($csv_arr as $row) {
                    fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
                    fputcsv($output, $row);
                }
                fclose($output);
                exit();
            }
        } else {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="tdr_summary.xlsx"');
            $writer = new Xlsx($spreadsheet);
            ob_end_clean();
            $writer->save("php://output");
            exit();
        }
        exit();
    }

    public function actionTdrDetailedExport()
    {
        $a_z = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P','Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y'. 'Z'];

        $headers_summary = [
            'Bill Group',
            'Currency',
            'Msgs',
            'In',
            'Out',
            'Profit',
            '% Profit'
        ];

        $headers_result = [
            'Country Network',
            'Bill Group',
            'Client',
            'CLI',
            'BNUM',
            'Msgs'
        ];

        if(isset($_SERVER['QUERY_STRING']))
        {
            // SUMMARY
            $searchModel = new TdrSearchSummary();
            $mysubusr = User::find()->select('id')->where(['role' => 2]);
            $query = $searchModel->search(\Yii::$app->request->queryParams, $mysubusr, '', false, true)->query;
            $params = isset(\Yii::$app->request->queryParams['TdrSearchSummary']) ? \Yii::$app->request->queryParams['TdrSearchSummary'] : [];
            // FILTERS
            $billgroup_name = 'None';
            if(!empty(intval($params['billgroup_id'])))
            {
                $obj = Billgroup::findOne(intval($params['billgroup_id']));
                if(isset($obj->name)) $billgroup_name = $obj->name;
            }
            $client_name = 'None';
            if(isset($params['reseller_id']) && trim($params['reseller_id']) != "")
            {
                if($params['reseller_id'] == 0)
                {
                    $client_name = 'Un-located';
                } else {
                    $obj = User::findOne(intval($params['reseller_id']));
                    if(isset($obj->username)) $client_name = $obj->username;
                }
            }
            $filters = [
                'Bill Group' => $billgroup_name,
                'Client' => $client_name,
                'Delivered Time' => !empty($params['delivered_time']) ? $params['delivered_time'] : 'None'
            ];

            $searchModel_2 = new TdrSearchDetailed();
            $query_2 = $searchModel_2->search(\Yii::$app->request->queryParams, $mysubusr, '', false, true)->query;
            $rows_2 = $query_2->all();

        }

        $csv_cols = ["", "", "", "", "", "", ""];
        $csv_arr = [];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // title
        $row = 1;
        $col = 1;

        $sheet->setCellValueByColumnAndRow($col, $row , "TDR DETAILED REPORT");
        $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
        $temp = $csv_cols;
        $temp[$col-1] = "TDR DETAILED REPORT";
        $csv_arr[] = $temp;
        $row++; $csv_arr[] = $csv_cols;
        
        $sheet->setCellValueByColumnAndRow($col, $row , "Created " . date('Y-m-d H:i:s'));
        $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
        $temp = $csv_cols;
        $temp[$col-1] = "Created " . date('Y-m-d H:i:s');
        $csv_arr[] = $temp;
        $row++; $csv_arr[] = $csv_cols;
        $row++; $csv_arr[] = $csv_cols;

        $sheet->setCellValueByColumnAndRow($col, $row , "Filters");
        $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
        $temp = $csv_cols;
        $temp[$col-1] = "Filters";
        $csv_arr[] = $temp;
        $row++; $csv_arr[] = $csv_cols;

        if(is_array($filters) && count($filters) > 0)
        {
            $col = 1;
            $temp1 = $csv_cols;
            $temp2 = $csv_cols;
            foreach($filters as $k=>$v)
            {
                $sheet->setCellValueByColumnAndRow($col, $row , $k);
                $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
                $temp1[$col-1] = $k;
                $sheet->setCellValueByColumnAndRow($col, $row + 1 , $v);
                $temp2[$col-1] = $v;
                $col++;
            }
            $csv_arr[] = $temp1;
            $csv_arr[] = $temp2;
            $row++; $csv_arr[] = $csv_cols;
            $row++; $csv_arr[] = $csv_cols;
            $row++; $csv_arr[] = $csv_cols;
        }

        $col = 1;
        $sheet->setCellValueByColumnAndRow($col, $row , "SUMMARY");
        $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
        $temp = $csv_cols;
        $temp[$col-1] = "SUMMARY";
        $csv_arr[] = $temp;
        $row++; $csv_arr[] = $csv_cols;
        $row++; $csv_arr[] = $csv_cols;

        if(is_array($headers_summary) && count($headers_summary) > 0)
        {
            $col = 1;
            $temp = $csv_cols;
            foreach($headers_summary as $v)
            {
                $sheet->setCellValueByColumnAndRow($col, $row , $v);
                $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
                $temp[$col-1] = $v;
                $col++;
            }
            $csv_arr[] = $temp;
            $row++; 
        }

        $rows = $query->all();
        if(is_array($rows) && count($rows) > 0)
        {
            foreach($rows as $v)
            {
                $temp = $csv_cols;
                foreach($headers_summary as $hk => $hv)
                {
                    switch($hv)
                    {
                        case "Bill Group":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->billgroup) ? $v->billgroup->name : "");
                            $temp[$hk] = isset($v->billgroup) ? $v->billgroup->name : "";
                            break; 
                        case "Currency":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->currency) ? $v->currency : "");
                            $temp[$hk] = isset($v->currency) ? $v->currency : "";
                            break; 
                        case "Msgs":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->msgs) ? number_format($v->msgs, 0) : 0);
                            $temp[$hk] = isset($v->msgs) ? number_format($v->msgs, 0) : 0;
                            break; 
                        case "In":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->rev_in) ? number_format($v->rev_in, 2) : number_format(0, 2));
                            $temp[$hk] = isset($v->rev_in) ? number_format($v->rev_in, 2) : number_format(0, 2);
                            break; 
                        case "Out":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->rev_out) ? number_format($v->rev_out, 2) : number_format(0, 2));
                            $temp[$hk] = isset($v->rev_out) ? number_format($v->rev_out, 2) : number_format(0, 2);
                            break; 
                        case "Profit":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->profit) ? number_format($v->profit, 2) : number_format(0, 2));
                            $temp[$hk] = isset($v->profit) ? number_format($v->profit, 2) : number_format(0, 2);
                            break; 
                        case "% Profit":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->profit_percentage) ? number_format($v->profit_percentage, 2) : number_format(0, 2));
                            $temp[$hk] = isset($v->profit_percentage) ? number_format($v->profit_percentage, 2) : number_format(0, 2);
                            break; 
                    }
                }
                $csv_arr[] = $temp;
                $row++; 
            }                
        }

        $row++; $csv_arr[] = $csv_cols;
        $row++; $csv_arr[] = $csv_cols;
        $col = 1;
        $sheet->setCellValueByColumnAndRow($col, $row , "RESULTS");
        $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
        $temp = $csv_cols;
        $temp[$col-1] = "Results";
        $csv_arr[] = $temp;
        $row++; $csv_arr[] = $csv_cols;
        $row++; $csv_arr[] = $csv_cols;
        if(is_array($headers_result) && count($headers_result) > 0)
        {
            $col = 1;
            $temp = $csv_cols;
            foreach($headers_result as $v)
            {
                $sheet->setCellValueByColumnAndRow($col, $row , $v);
                $sheet->getStyle($a_z[$col - 1]  . $row)->applyFromArray(['font' => ['bold' => true]]);
                $temp[$col-1] = $v;
                $col++;
            }
            $csv_arr[] = $temp;
            $row++; 
        }

        if(is_array($rows_2) && count($rows_2) > 0)
        {
            foreach($rows_2 as $v)
            {
                $temp = $csv_cols;
                foreach($headers_result as $hk => $hv)
                {
                    switch($hv)
                    {
                        case "Country Network":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->country) ? $v->country->Country_Network : "");
                            $temp[$hk] = isset($v->country) ? $v->country->Country_Network : "";
                            break; 
                        case "Bill Group":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->billgroup) ? $v->billgroup->name : "");
                            $temp[$hk] = isset($v->billgroup) ? $v->billgroup->name : "";
                            break; 
                        case "Client":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->resellers) ? $v->resellers->username : "");
                            $temp[$hk] = isset($v->resellers) ? $v->resellers->username : "";
                            break; 
                        case "CLI":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->from_number) ? $v->from_number : "");
                            $temp[$hk] = isset($v->from_number) ? $v->from_number : "";
                            break; 
                        case "BNUM":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->to_number) ? $v->to_number : "");
                            $temp[$hk] = isset($v->to_number) ? $v->to_number : "";
                            break; 
                        case "Msgs":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->msgs) ? number_format($v->msgs,0) : 0);
                            $temp[$hk] = isset($v->msgs) ? number_format($v->msgs,0) : 0;
                            break; 
                    }
                }
                $csv_arr[] = $temp;
                $row++; 
            }                
        }


        if(\Yii::$app->request->queryParams['mode'] == 'csv')
        {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="tdr_detailed.csv"');
            if(is_array($csv_arr) && count($csv_arr) > 0)
            {
                ob_end_clean();
                $output = fopen('php://output', 'w');
                foreach ($csv_arr as $row) {
                    fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
                    fputcsv($output, $row);
                }
                fclose($output);
                exit();
            }
        } else {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="tdr_detailed.xlsx"');
            $writer = new Xlsx($spreadsheet);
            ob_end_clean();
            $writer->save("php://output");
            exit();
        }
        exit();
    }

    public function actionAllocateNumbers()
    {
        $user = Yii::$app->request->post('cboClient');
        $service = Yii::$app->request->post('cboService');
        $rev_out_rate = Yii::$app->request->post('revOutRate');
        $numbers = explode(",", Yii::$app->request->post('hdnAllocateNumbers'));
        foreach ($numbers as $key => $value) {
            Yii::$app->db->createCommand()
            ->update('fsmastertb', [
                    'reseller_id' => $user, 
                    'agent_id' => $user, 
                    'service_id' => $service,
                    'cld2rate' => $rev_out_rate,
                    'cld3rate' => 0,
                    //'allocated_date' => date('Y-m-d')
                ], 
                "cld1 = '" . $value . "'")
            ->execute();
        }
        //Yii::$app->session->setFlash('cld_added', Yii::$app->request->post('hdnAllocateNumbers') . (count($numbers) > 1 ? ' are' : ' is') . " assigned successfully");
        return $this->redirect('sms-numbers');
    }
    public function actionUnallocateNumbers()
    {
        $numbers = explode(",", Yii::$app->request->post('hdnUnallocateNumbers'));
        foreach ($numbers as $key => $value) {
            Yii::$app->db->createCommand()
            ->update('fsmastertb', [
                    'reseller_id' => 0, 
                    'agent_id' => 0, 
                    'service_id' => 0,
                    'cld2rate' => 0,
                    'cld3rate' => 0,
                    //'allocated_date' => date('Y-m-d')
                ], 
                "cld1 = '" . $value . "'")
            ->execute();
        }
        //Yii::$app->session->setFlash('cld_added', Yii::$app->request->post('hdnUnallocateNumbers') . (count($numbers) > 1 ? ' are' : ' is') . " assigned remove successfully");
        return $this->redirect('sms-numbers');
    }

    public function actionTestNumbers()
    {
        $model = new Fsusertb();
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 10;

        if ($filter == 'all') {
            $filter = '';
        }

        $searchModel = new FsmastertbSearch();
        $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id, 'role' => 3]);
        $summary = $model->getSummary($mysubusr, false, true);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search, User::isUserAdmin(\Yii::$app->user->id), User::isTestPanel(\Yii::$app->user->id));
        $dataProvider->pagination->pageSize = $filter;

        return $this->render('test_numbers', [
            'dataProvider' => $dataProvider, 
            'searchModel' => $searchModel,
            'summary' => $summary,
            'countries' => $this->getCountryItems(),
            'billgroups' => $this->getBillgroupItems(),
            'resellers' => $this->getResellerItems(),
            'clients_only' => $this->getResellerItems(false),
            'services' => $this->getServicesItems()
        ]);
    }

    public function actionTestTdr()
    {
        $model = new Fsusertb();
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 10;
        $mysubusr = User::find()->select('id')->where(['role' => 2]);

        if ($filter == 'all') {
            $filter = '';
        }

        $searchModel = new TdrSearch();

        $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id, 'role' => 3]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search, User::isUserAdmin(\Yii::$app->user->id), User::isTestPanel(\Yii::$app->user->id));
        $dataProvider->setPagination(['pageSize' => $filter]); 

        return $this->render('test_tdr', [
            'dataProvider' => $dataProvider, 
            'searchModel' => $searchModel,
            'search' => $search, 
            'filter' => $filter,
            'billgroups' => $this->getBillgroupItems(),
            'resellers' => $this->getResellerItems(),
        ]);
    }


}