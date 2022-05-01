<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use app\models\Fsmastertb;
use app\models\Fsusertb;
use app\models\Fsresellertb;
use app\models\Fsadmintb;
use app\models\Fscallsnow;
use app\models\FstestSearch;
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
use app\models\Billgroup;
use app\models\BillgroupSearch;
use app\models\Country;
use app\models\Numbers;
use app\models\Supplier;

class AdminController extends \yii\web\Controller
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
                'only' => ['add-cld', 'add-user', 'upload', 'update-cld', 'delete-cld', 'delete-user', 'list-assign-cld', 'cdr', 'list-user', 'assign-cld', 'edit-user', 'delete-assigned-cld', 'update-assigned-cld', 'date-report', 'detach-number', 'detach-number-reseller', 'show-assigned-reseller', 'detach-number-reseller-admin', 'show-assigned-reseller-admin', 'show-assigned', 'show-number-routes', 'fs-call-report', 'export-fscall', 'load-search-fields', 'agent-summary'],
                'rules' => [
                    [
                        'actions' => ['add-cld', 'add-user', 'upload', 'update-cld', 'delete-cld', 'delete-user', 'list-assign-cld', 'cdr', 'list-user', 'assign-cld', 'edit-user', 'delete-assigned-cld', 'update-assigned-cld', 'date-report', 'detach-number', 'show-assigned', 'detach-number-reseller', 'show-assigned-reseller', 'detach-number-reseller-admin', 'show-assigned-reseller-admin', 'show-number-routes', 'fs-call-report', 'export-fscall', 'load-search-fields', 'agent-summary'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return User::isUserAdmin(Yii::$app->user->identity->id);
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
     * Displays active calls admin.
     *
     * @return string
     */
    public function actionActiveCalls()
    {
        $caller_id = isset($_GET['caller_id']) ? $_GET['caller_id'] : '';
        $called_no = isset($_GET['called_no']) ? $_GET['called_no'] : '';
        if (User::isUserAdmin(Yii::$app->user->identity->id)) {
            $query = Fscallsnow::find()->orderBy(['call_start' => SORT_DESC]);
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
    }

    /*
    * Add cld
    */
    public function actionUpload()
    {
        $flag = 0;
        $model =  new Fsmastertb();
        $Fsusertb = new Fsusertb();
        $model->scenario = 'upload';
        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file'); //print_r($model->file); exit();
            $file = fopen($model->file->tempName, "r");
            while (($csvData = fgetcsv($file, 1000, ",")) !== FALSE) {

                if (array_filter($csvData)) {
                    if (count($csvData) == 8) {
                        $model =  new Fsmastertb();
                        $model->scenario = 'save';
                        $model->fsmid = $model->getIdValue();
                        $model->inboundip = trim($csvData[0]);
                        $model->cld1 = trim($csvData[1]);
                        $model->cld2 = trim($csvData[2]);
                        $model->outboundip = trim($csvData[3]);
                        $model->cld1rate = trim($csvData[4]);
                        $model->cld2rate = trim($csvData[5]);
                        $model->cld1description = trim($csvData[6]);
                        $model->cld2description = trim($csvData[7]);
                        if (!$model->save()) {
                            $errors[] = $model->errors;
                            $flag = 1;
                        }
                    } else {
                        Yii::$app->session->setFlash('csv_failed', "Please follow the specified structure for csv.");
                        break;
                    }
                }
            }
            fclose($file);
            if ($flag == 0) {
                Yii::$app->session->setFlash('csv_success', "Uploaded successfully.");
            }
        }

        $summary = $Fsusertb->getSummary();
        $mstrtb_cnt = Fsmastertb::find()->count();
        $usrtb_cnt = Fsusertb::find()->count();
        $summary['available'] = $mstrtb_cnt - $usrtb_cnt;
        return $this->render('upload', ['model' => $model, 'summary' => $summary]);
    }

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
            $user->role = 2;
            $user->setPassword($user->password);

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
    * Add reseller
    */
    public function actionAddReseller()
    {
        $flag = 0;
        $user = new User();
        if ($user->load(Yii::$app->request->post())) {
            $id = $user->getIdValue();
            $user->id = $id;
            $user->role = 3;
            $user->setPassword($user->password);

            if ($user->save()) {
                $user = new User();
                $model = new Fsusertb();
                Yii::$app->session->setFlash('user_add_success', "User added successfully.");
            } else {
                $user->password = "";
                Yii::$app->session->setFlash('user_add_failed', "Failed to save detail try again.");
            }
        }
        return $this->render('add_user', ['user' => $user, 'isReSeller' => true]);
    }

    /*
    * List all Users
    */
    public function actionListReseller()
    {

        $dataProvider = new ActiveDataProvider([
            'query' => User::find()->where(['role' => 3]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('list_user', ['dataProvider' => $dataProvider, 'isReSeller' => true]);
    }

    /*
    * List all Users
    */
    public function actionListUser()
    {

        $dataProvider = new ActiveDataProvider([
            'query' => User::find()->where(['role' => 2]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('list_user', ['dataProvider' => $dataProvider]);
    }

    /*
    * Add cld to users
    */
    public function actionAddCld()
    {
        $model = new Fsusertb();
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 20;
        $mysubusr = User::find()->select('id')->where(['role' => 2]);

        if ($filter == 'all') {
            $filter = '';
        }

        $searchModel = new FsmastertbSearch();

        $summary = $model->getSummary($mysubusr, true);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search, true);
        $dataProvider->pagination->pageSize = $filter;

        return $this->render('add_cld', ['dataProvider' => $dataProvider, 'summary' => $summary, 'search' => $search, 'filter' => $filter]);
    }

    /*
    * Asign a cld to user
    */
    public function actionAssignCld()
    {
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $limit = isset($_GET['limit']) ? !empty($_GET['limit']) ? $_GET['limit'] : 20 : 20;
        $model = new Fsusertb();
        $mysubusr = User::find()->select('id')->where(['role' => 2]);
        $mysubrslr = User::find()->select('id')->where(['role' => 3]);
        /*$fsuser = Fsusertb::find()->select('cld1')->where(['closing_date' => NULL]);
        $mysubusr = User::find()->select('id')->where(['role' => 2]);
        $query = Fsmastertb::find()->where(['like','cld1' , $search])->andWhere(['not in','cld1',$fsuser])->limit($limit);*/
        $query = Fsmastertb::find()
            //->joinWith(['user'])
            ->where(['>', 'fsmastertb.reseller_id', 0])
            ->andFilterWhere(['fsmastertb.agent_id' => 0]);
        //->andFilterWhere(['closing_date' => NULL]);
        if ($search) {
            $query = $query->andFilterWhere(['like', 'cld1', $search]);
        }
        $query = $query->limit($limit);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $users = $model->getUserList($mysubusr);
        $resellers = $model->getUserList($mysubrslr);

        $summary = $model->getSummary($mysubusr, true, false, true);
        /*$mstrtb_cnt = Fsmastertb::find()->where(['>', 'reseller_id', 0])
        ->andFilterWhere(['agent_id' => 0])->count();
        $usrtb_cnt = Fsusertb::find()->count();*/
        $summary['available'] = $summary['stock'] - $summary['assigned'];

        return $this->render('assign_cld', ['users' => $users, 'resellers' => $resellers, 'summary' => $summary, 'dataProvider' => $dataProvider, 'search' => $search, 'limit' => $limit]);
    }

    /*
    * Assign cld number to user
    */
    public function actionAssignNumber()
    {
        $reseller = Yii::$app->request->post('reseller');
        $user = Yii::$app->request->post('user');
        $numbers = explode(",", Yii::$app->request->post('numbers'));

        foreach ($numbers as $key => $value) {

            $usertbmodel = new Fsusertb();
            $usertbmodel->cld1 = $value;
            $usertbmodel->user_id = $user;
            $usertbmodel->assigned_date = date("Y-m-d H:i:s");
            $usertbmodel->save();

            $mastertbmodel = Fsmastertb::findOne(['cld1' => $value]);
            $mastertbmodel->reseller_id = $reseller;
            $mastertbmodel->agent_id = $user;
            $mastertbmodel->save();
        }
        Yii::$app->session->setFlash('cld_added', Yii::$app->request->post('numbers') . (count($numbers) > 1 ? ' are' : ' is') . " assigned successfully");
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
                return $this->redirect(['add-cld']);
            }
        }
        return $this->render('update_cld', ['model' => $model]);
    }

    /*
    * Delete Cld
    */
    public function actionDeleteCld($id)
    {
        $model = Fsmastertb::find()->where(['fsmid' => $id])->one();
        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $Fsusertb = Fsusertb::find()->where(['cld1' => $model->cld1])->one();
        if (!$Fsusertb->delete()) {
            throw new ForbiddenHttpException('Failed to delete user Try again.');
        }
        if (!$model->delete()) {
            throw new ForbiddenHttpException('Failed to delete user Try again.');
        }
        return $this->redirect(['add-cld']);
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
                if ($model->role === 2) {
                    return $this->redirect(['list-user']);
                } else if ($model->role === 3) {
                    return $this->redirect(['list-reseller']);
                } else if ($model->role === 4) {
                    return $this->redirect(['list-reseller-admin']);
                }
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
            ->select(['call_startdate', 'ani', 'called_number', 'cld1', 'country', 'call_duration', '(cld1_ratepersec * (call_duration/60)) AS Charges', '(cld2_ratepersec * (call_duration/60)) AS Cost'])
            ->where(['between', 'call_startdate', $startT, $endT]);

        $date = $start . ' - ' . $end;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
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
    * List assigned clds
    */
    public function actionListAssignCld()
    {
        $searchModel = new FsusertbSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list_assign_cld', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /*
    * Delete Assigned Cld
    */
    public function actionDeleteAssignedCld($id)
    {
        $model = Fsusertb::findOne($id);
        if ($model) {
            if ($model->delete()) {
                return $this->redirect(['list-assign-cld']);
            } else {
                throw new ForbiddenHttpException('Something went wrong. Unable to delete.');
            }
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /*
    * Edit assigned clds
    */
    public function actionUpdateAssignedCld($id)
    {
        $model = Fsusertb::findOne($id);
        if ($model) {
            $cld_Model = new Fsusertb(); //$current_cld = "";
            $current_cld = \yii\helpers\ArrayHelper::map(Fsusertb::find()->where(['user_id' => $model->user_id])->all(), 'cld1', 'cld1');
            if ($cld_Model->load(Yii::$app->request->post())) {
                $input = (array)$cld_Model->cld1;
                $new = array_diff($input, $current_cld);
                $removed = array_diff($current_cld, $input);
                if ($removed) {
                    foreach ($removed as $key => $value) {
                        $model = Fsusertb::find()->where(['cld1' => $value])->one();
                        if ($model) {
                            $model->delete();
                        }
                    }
                }
                if ($new) {
                    foreach ($new as $key => $value) {
                        $usertbmodel = new Fsusertb();
                        $usertbmodel->cld1 = $value;
                        $usertbmodel->user_id = $model->user_id;
                        $usertbmodel->assigned_date = date("Y-m-d H:i:s");
                        $usertbmodel->save();
                    }
                }
                return $this->redirect(['list-assign-cld']);
            }
            $cld_1 = $cld_Model->getCldList();
            $summary = $cld_Model->getSummary();
            $summary['available'] = count($cld_1);
            $cld = array_merge($cld_1, $current_cld);
            return $this->render('edit_cld', ['current_cld' => $current_cld, 'cld' =>  $cld, 'summary' => $summary, 'cld_Model' => $cld_Model]);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /*
    * Datewise connected call report
    */
    public function actionDateReport()
    {
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => "SELECT DATE_FORMAT(call_getdate, '%d-%m-%Y') AS `date`,COUNT(*) as call_count, SUM(`call_duration`) as minute,sum(((call_duration/60)*cld2_ratepersec) + ((cld1_ratepersec/60)*call_duration)) as sum FROM `fscdr` WHERE `fsmid` !='' GROUP BY DATE_FORMAT(call_getdate, '%d-%m-%Y') ORDER BY `call_getdate` DESC",
            'pagination' => [
                'pageSize' => 40,
            ],
        ]);

        return $this->render('date_wise_report', ['dataProvider' => $dataProvider]);
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
            ->select(['call_startdate', 'ani', 'called_number', 'cld1', 'country', 'call_duration', '(cld1_ratepersec * (call_duration/60)) AS Charges', '(cld2_ratepersec * (call_duration/60)) AS Cost'])
            ->where(['between', 'call_startdate', $startT, $endT]);

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
                'pageSize' => 20,
            ],
        ]);


        // 	if($model->agent_id == 0 && $model->reseller_id == 0 && $model->admin_id !== 0){
        //   $dataProvider = new ActiveDataProvider([
        //       'query' => Fsadmintb::find()->where(['cld1' => $model->cld1])->orderBy(['closing_date' => 'SORT_DESC']),
        //       'pagination' => [
        //           'pageSize' => 20,
        //       ],
        //   ]);
        // }
        // else if($model->agent_id == 0 && $model->reseller_id !== 0 && $model->admin_id !== 0){
        // 	$dataProvider = new ActiveDataProvider([
        // 			'query' => Fsresellertb::find()->where(['cld1' => $model->cld1])->orderBy(['closing_date' => 'SORT_DESC']),
        // 			'pagination' => [
        // 					'pageSize' => 20,
        // 			],
        // 	]);
        // }
        // else if($model->agent_id !== 0 && $model->reseller_id !== 0 && $model->admin_id !== 0){
        // 	$dataProvider = new ActiveDataProvider([
        // 			'query' => Fsusertb::find()->where(['cld1' => $model->cld1])->orderBy(['closing_date' => 'SORT_DESC']),
        // 			'pagination' => [
        // 					'pageSize' => 20,
        // 			],
        // 	]);
        // }

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

        $query = Fscallreport::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
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
        $Cost = $query->sum('Cost');


        $agent = Fscallreport::find()->groupBy(['agent_id'])->where(['between', 'Date', $start, $end])->all();
        $country = Fscallreport::find()->groupBy(['Country'])->where(['between', 'Date', $start, $end])->all();
        $Caller_ID = Fscallreport::find()->groupBy(['Caller_ID'])->where(['between', 'Date', $start, $end])->all();
        $cld1 = Fscallreport::find()->groupBy(['Cld1'])->where(['between', 'Date', $start, $end])->limit(1000)->all();


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
                'Datepickr' => $Datepickr,
                'called_num' => $called_num
            ]
        );
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

        $query = Fscallreport::find();

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

        $query = Fscallreport::find()->select('agent_id,Country,SUM(Total_Calls) As Total_Calls,SUM(Call_Duration) AS Call_Duration,SUM(cld2_cost) AS Charges,SUM(cld3_cost) AS Cost, (SUM(cld2_cost) -SUM(cld3_cost)) AS margin')->groupBy(['Country', 'agent_id']);

        //echo $query->createCommand()->getRawSql(); exit();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $query->andFilterWhere([
            'agent_id' => $agent_id,
            'Country' => $country_id,
        ]);
        $query->andFilterWhere(['between', 'Date', $start, $end]);


        $agent = Fscallreport::find()->groupBy(['agent_id'])->all();
        $country = Fscallreport::find()->groupBy(['Country'])->all();

        return $this->render('agent_summary', ['dataProvider' => $dataProvider, 'date_range' => $date_range, 'agent' => $agent, 'country' => $country, 'agent_id' => $agent_id, 'country_id' => $country_id]);
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

        $query = Fscallreport::find()->select('reseller_id,Country,SUM(Total_Calls) As Total_Calls,SUM(Call_Duration) AS Call_Duration,SUM(cld1_cost) AS Charges, SUM(cld2_cost) AS Cost, (SUM(cld1_cost) -SUM(cld2_cost)) AS margin')->groupBy(['Country', 'reseller_id']);

        //echo $query->createCommand()->getRawSql(); exit();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $query->andFilterWhere([
            'reseller_id' => $reseller_id,
            'Country' => $country_id,
        ]);
        $query->andFilterWhere(['between', 'Date', $start, $end]);


        $reseller = Fscallreport::find()->groupBy(['reseller_id'])->all();
        $country = Fscallreport::find()->groupBy(['Country'])->all();

        return $this->render('reseller_summary', ['dataProvider' => $dataProvider, 'date_range' => $date_range, 'reseller' => $reseller, 'country' => $country, 'reseller_id' => $reseller_id, 'country_id' => $country_id]);
    }

    /*
    * Add cld to reseller
    */
    public function actionAddCldReseller()
    {
        $model = new Fsusertb();
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 20;

        if ($filter == 'all') {
            $filter = '';
        }
        $mysubusr = User::find()->select('id')->where(['role' => 3]);
        $searchModel = new FsmastertbSearch();

        $summary = $model->getSummary($mysubusr);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search, true);
        $dataProvider->pagination->pageSize = $filter;

        return $this->render('add_cld_reseller', ['dataProvider' => $dataProvider, 'summary' => $summary]);
    }

    /*
    * Asign a cld to reseller
    */
    public function actionAssignCldReseller()
    {
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $limit = isset($_GET['limit']) ? !empty($_GET['limit']) ? $_GET['limit'] : 20 : 20;
        $model = new Fsusertb();
        $mysubusr = User::find()->select('id')->where(['role' => 3]);

        /*$fsuser = Fsusertb::find()->select('cld1')->where(['closing_date' => NULL])->andFilterWhere(['in', 'user_id', $mysubusr]);
        $query = Fsmastertb::find()->where(['like','cld1' , $search])->andWhere(['not in','cld1',$fsuser])->limit($limit);*/
        // echo Fsmastertb::find()->count()."<br />";
        // echo Fsmastertb::find()->where(['reseller_id' => 0])->count()."<br />";
        //
        //
        // echo Fsmastertb::find()->where(['reseller_id' => 0])->andFilterWhere(['agent_id' => 0])->count()."<br />";
        // echo Fsmastertb::find()->where(['=', 'reseller_id', 0])->andFilterWhere(['>', 'agent_id', 0])->count()."<br />";
        // echo Fsmastertb::find()->where(['>', 'reseller_id', 0])->andFilterWhere(['agent_id' => 0])->count()."<br />";
        // echo Fsmastertb::find()->where(['>', 'reseller_id', 0])->andFilterWhere(['>', 'agent_id', 0])->count()."<br />";
        // 10475
        // 3147
        // 1432
        // 1715
        // 3
        // 7325
        // exit;
        $query = Fsmastertb::find()->where(['admin_id' => 0])
            ->andFilterWhere(['reseller_id' => 0])
            ->andFilterWhere(['agent_id' => 0]);
        if ($search) {
            $query = $query->andFilterWhere(['like', 'cld1', $search]);
        }
        $query = $query->limit($limit);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);


        $users = $model->getUserList($mysubusr);

        $summary = $model->getSummary($mysubusr, true, true);
        // $mstrtb_cnt = Fsmastertb::find()->count();
        // $usrtb_cnt = Fsusertb::find()->count();
        //$summary['available'] = $mstrtb_cnt-$usrtb_cnt;
        $summary['available'] = $summary['stock'] - $summary['assigned'];

        return $this->render('assign_cld_reseller', ['users' => $users, 'summary' => $summary, 'dataProvider' => $dataProvider, 'search' => $search, 'limit' => $limit]);
    }

    /*
    * Check calls send to this number
    */
    public function actionCheckCallsend()
    {
        $id = Yii::$app->request->post('id');
        $roleType = Yii::$app->request->post('roleType');
        $model = null;
        if ($roleType == 2) {
            $model = Fsusertb::findOne($id);
        } else if ($roleType == 3) {
            $model = Fsresellertb::findOne($id);
        } else if ($roleType == 4) {
            $model = Fsadmintb::findOne($id);
        }
        if (!$model) {
            return json_encode([
                'error' => true,
                'message' => 'Data not available.'
            ]);
        }
        $fscdr = Fscdr::find()->where(['>=', 'call_startdate', $model->assigned_date])->sum('call_duration');
        return json_encode([
            'error' => false,
            'message' => 'Total of ' . round($fscdr / 60, 2) . ' Minutes has been made through this number. Are you sure want to detach?.',
        ]);
    }

    /*
    * Show all assigned number to a reseller
    */
    public function actionShowAssignedResellerAdmin()
    {
        $model = new Fsadmintb();
        $resellerAdminId = isset($_GET['admin_id']) ? $_GET['admin_id'] : '';
        $cld1 = isset($_GET['cld1']) ?  $_GET['cld1']  : '';

        $mysubusr = User::find()->select('id')->where(['role' => 4]);
        $query = Fsadmintb::find()->where(['closing_date' => NULL])
            ->andFilterWhere(['in', 'admin_id', $mysubusr]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $query->andFilterWhere(['like', 'cld1', $cld1]);
        $query->andFilterWhere(['admin_id' =>  $resellerAdminId]);

        $resellerAdmins = $model->getResellerAdminList($mysubusr);

        return $this->render('detach_number_reseller_admin', ['dataProvider' => $dataProvider, 'resellerAdmins' => $resellerAdmins, 'resellerAdminId' => $resellerAdminId, 'cld1' => $cld1]);
    }

    /*
		* Detach an assigned number from a reseller
		*/
    public function actionDetachNumberResellerAdmin()
    {
        //$id = Yii::$app->request->post('btn_id');
        $numbers = Yii::$app->request->post('btn_number');
        $query = Yii::$app->db->createCommand('
				UPDATE fsadmintb SET closing_date = "' . date('Y-m-d H:i:s') . '" WHERE cld1 IN(:numbers);
				UPDATE fsresellertb SET closing_date = "' . date('Y-m-d H:i:s') . '" WHERE cld1 IN(:numbers);
				UPDATE fsusertb SET closing_date = "' . date('Y-m-d H:i:s') . '" WHERE cld1 IN(:numbers);
				UPDATE fsmastertb SET admin_id = 0, reseller_id = 0, agent_id = 0 WHERE cld1 IN(:numbers);')->bindValue(':numbers', $numbers)->execute();
        if ($query) {
            return $this->redirect(['show-assigned-reseller-admin']);
        } else {
            throw new ForbiddenHttpException('Failed to detach number, Try again.');
        }
    }

    /*
    * Show all assigned number to a reseller
    */
    public function actionShowAssignedReseller()
    {
        $model = new Fsresellertb();
        $resellerId = isset($_GET['reseller_id']) ? $_GET['reseller_id'] : '';
        $cld1 = isset($_GET['cld1']) ?  $_GET['cld1']  : '';

        $mysubusr = User::find()->select('id')->where(['role' => 3]);
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

        return $this->render('detach_number_reseller', ['dataProvider' => $dataProvider, 'resellers' => $resellers, 'resellerId' => $resellerId, 'cld1' => $cld1]);
    }

    /*
    * Detach an assigned number from a reseller
    */
    public function actionDetachNumberReseller()
    {
        //$id = Yii::$app->request->post('btn_id');
        $numbers = Yii::$app->request->post('btn_number');
        $query = Yii::$app->db->createCommand('
			UPDATE fsresellertb SET closing_date = "' . date('Y-m-d H:i:s') . '" WHERE cld1 IN(:numbers);
			UPDATE fsusertb SET closing_date = "' . date('Y-m-d H:i:s') . '" WHERE cld1 IN(:numbers);
			UPDATE fsmastertb SET reseller_id = 0, agent_id = 0 WHERE cld1 IN(:numbers);')->bindValue(':numbers', $numbers)->execute();
        if ($query) {
            return $this->redirect(['show-assigned-reseller']);
        } else {
            throw new ForbiddenHttpException('Failed to detach number, Try again.');
        }
    }

    /*
    * Show all assigned number to a user
    */
    public function actionShowAssigned()
    {
        $model = new Fsusertb();
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : '';
        $cld1 = isset($_GET['cld1']) ?  $_GET['cld1']  : '';
        $mysubusr = User::find()->select('id')->where(['role' => 2]);

        $query = Fsusertb::find()->where(['closing_date' => NULL])->andFilterWhere(['in', 'user_id', $mysubusr]);

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
    * Assign cld number to user
    */
    public function actionAssignNumberReseller()
    {
        $user = Yii::$app->request->post('user');
        $numbers = explode(",", Yii::$app->request->post('numbers'));

        foreach ($numbers as $key => $value) {

            $usertbmodel = new Fsadmintb();
            $usertbmodel->cld1 = $value;
            $usertbmodel->reseller_id = $user;
            $usertbmodel->save();

            $usertbmodel = new Fsresellertb();
            $usertbmodel->cld1 = $value;
            $usertbmodel->reseller_id = $user;
            $usertbmodel->assigned_date = date("Y-m-d H:i:s");
            $usertbmodel->save();

            $mastertbmodel = Fsmastertb::findOne(['cld1' => $value]);
            $mastertbmodel->reseller_id = $user;
            $mastertbmodel->save();
        }
        Yii::$app->session->setFlash('cld_added', Yii::$app->request->post('numbers') . (count($numbers) > 1 ? ' are' : ' is') . " assigned successfully");
    }

    /*
    * Get Users bty reseller
    */
    public function actionGetUsersByReseller()
    {
        $reseller_id = Yii::$app->request->post('resellerId');
        $mysubusr = ArrayHelper::map(User::find()->select('id, username')->where(['reseller_id' => $reseller_id])->all(), 'id', 'username');
        return json_encode($mysubusr);
    }

    /*
    * Get numbers by reseller
    */
    public function actionGetNumbersByReseller()
    {
        $reseller_id = Yii::$app->request->post('resellerId');
        $numbers = Fsmastertb::find()->select('fsmid, cld1', 'reseller_id')->where(['reseller_id' => $reseller_id])->all();

        return json_encode($numbers);
    }

    /*
    * Add reseller admin
    */
    public function actionAddResellerAdmin()
    {
        $flag = 0;
        $user = new User();
        if ($user->load(Yii::$app->request->post())) {
            $id = $user->getIdValue();
            $user->id = $id;
            $user->role = 4;
            $user->setPassword($user->password);

            if ($user->save()) {
                $user = new User();
                $model = new Fsusertb();
                Yii::$app->session->setFlash('user_add_success', "Reseller Admin added successfully.");
            } else {
                $user->password = "";
                Yii::$app->session->setFlash('user_add_failed', "Failed to save detail try again.");
            }
        }
        return $this->render('add_user', ['user' => $user, 'isReSellerAdmin' => true]);
    }

    /*
    * List all Reseller Admin
    */
    public function actionListResellerAdmin()
    {

        $dataProvider = new ActiveDataProvider([
            'query' => User::find()->where(['role' => 4]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('list_user', ['dataProvider' => $dataProvider, 'isReSellerAdmin' => true]);
    }

    /*
    * Add cld to reseller admin
    */
    public function actionAddCldResellerAdmin()
    {
        $model = new Fsusertb();
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 20;

        if ($filter == 'all') {
            $filter = '';
        }
        $mysubusr = User::find()->select('id')->where(['role' => 4]);
        $searchModel = new FsmastertbSearch();

        $summary = $model->getSummary($mysubusr);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search, true);
        $dataProvider->pagination->pageSize = $filter;

        return $this->render('add_cld_reseller', ['dataProvider' => $dataProvider, 'summary' => $summary]);
    }

    /*
    * Asign a cld to reseller admin
    */
    public function actionAssignCldResellerAdmin()
    {
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $limit = isset($_GET['limit']) ? !empty($_GET['limit']) ? $_GET['limit'] : 20 : 20;
        $model = new Fsusertb();
        $mysubusr = User::find()->select('id')->where(['role' => 4]);

        /*$fsuser = Fsusertb::find()->select('cld1')->where(['closing_date' => NULL])->andFilterWhere(['in', 'user_id', $mysubusr]);
        $query = Fsmastertb::find()->where(['like','cld1' , $search])->andWhere(['not in','cld1',$fsuser])->limit($limit);*/
        // echo Fsmastertb::find()->count()."<br />";
        // echo Fsmastertb::find()->where(['reseller_id' => 0])->count()."<br />";
        //
        //
        // echo Fsmastertb::find()->where(['reseller_id' => 0])->andFilterWhere(['agent_id' => 0])->count()."<br />";
        // echo Fsmastertb::find()->where(['=', 'reseller_id', 0])->andFilterWhere(['>', 'agent_id', 0])->count()."<br />";
        // echo Fsmastertb::find()->where(['>', 'reseller_id', 0])->andFilterWhere(['agent_id' => 0])->count()."<br />";
        // echo Fsmastertb::find()->where(['>', 'reseller_id', 0])->andFilterWhere(['>', 'agent_id', 0])->count()."<br />";
        // 10475
        // 3147
        // 1432
        // 1715
        // 3
        // 7325
        // exit;
        $query = Fsmastertb::find()->where(['admin_id' => 0])
            ->andFilterWhere(['reseller_id' => 0])
            ->andFilterWhere(['agent_id' => 0]);
        if ($search) {
            $query = $query->andFilterWhere(['like', 'cld1', $search]);
        }
        $query = $query->limit($limit);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);


        $users = $model->getUserList($mysubusr);

        $summary = $model->getSummary($mysubusr, true, false, false, true);
        // $mstrtb_cnt = Fsmastertb::find()->count();
        // $usrtb_cnt = Fsusertb::find()->count();
        //$summary['available'] = $mstrtb_cnt-$usrtb_cnt;
        $summary['available'] = $summary['stock'] - $summary['assigned'];

        return $this->render('assign_cld_reseller_admin', ['users' => $users, 'summary' => $summary, 'dataProvider' => $dataProvider, 'search' => $search, 'limit' => $limit]);
    }


    /*
    * Assign cld number to user
    */
    public function actionAssignNumberResellerAdmin()
    {
        $user = Yii::$app->request->post('user');
        $numbers = explode(",", Yii::$app->request->post('numbers'));

        foreach ($numbers as $key => $value) {

            $usertbmodel = new Fsadmintb();
            $usertbmodel->cld1 = $value;
            $usertbmodel->admin_id = $user;
            $usertbmodel->assigned_date = date("Y-m-d H:i:s");
            $usertbmodel->save();

            $mastertbmodel = Fsmastertb::findOne(['cld1' => $value]);
            $mastertbmodel->admin_id = $user;
            $mastertbmodel->save();
        }
        Yii::$app->session->setFlash('cld_added', Yii::$app->request->post('numbers') . (count($numbers) > 1 ? ' are' : ' is') . " assigned successfully");
    }

    public function actionFsTest()
    {
        $searchModel = new FstestSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('fs_test', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionEditNumber()
    {
        $numbers = Yii::$app->request->post('btn_number');
        $cld1Rate = Yii::$app->request->post('cld1Rate');
        $cld2Rate = Yii::$app->request->post('cld2Rate');
        if ($numbers && ($cld1Rate || $cld2Rate)) {

            $set_string = 'SET ';

            if ($cld1Rate) {
                $set_string .= 'cld1rate = ' . $cld1Rate;
            }
            if ($cld1Rate && $cld2Rate) {
                $set_string .= ', ';
            }
            if ($cld2Rate) {
                $set_string .= 'cld2rate = ' . $cld2Rate;
            }
            $query = Yii::$app->db->createCommand('UPDATE fsmastertb ' . $set_string . ' WHERE fsmid IN(' . $numbers . ');')
                //->bindValue(':numbers', $numbers)
                ->execute();
            if ($query) {
                return $this->redirect(['add-cld']);
            } else {
                throw new ForbiddenHttpException('Failed to edit number, Try again.');
            }
        } else {
            throw new ForbiddenHttpException('Atleast one of cld1rate and cld2rate field should not be empty, Try again.');
        }
    }

    public function actionExportDdi()
    {
        ini_set('memory_limit', '-1');

        $model = new Fsusertb();
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 20;
        $mysubusr = User::find()->select('id')->where(['role' => 2]);

        if ($filter == 'all') {
            $filter = '';
        }

        $searchModel = new FsmastertbSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search, true);
        $query = $dataProvider->query->all();

        if ($query) {
            $filename = "export_ddi_" . date('Y-m-d_H:is') . ".xls";
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");

            echo 'cld1' . "\t" . 'reseller_id' . "\t" . 'agent_id' . "\t" . 'admin_id' . "\t" . 'cld2description' . "\t" . 'cld1rate' . "\t" . 'cld2rate' . "\n";

            foreach ($query as $key => $value) {
                $row = "";
                $row .= $value->cld1 . "\t";
                if ($value->resellers) {
                    $row .= $value->resellers->username . "\t";
                } else {
                    $row .= '';
                }

                if ($value->users) {
                    $row .= $value->users->username . "\t";
                } else {
                    $row .= '';
                }

                if ($value->resellerAdmin) {
                    $row .= $value->resellerAdmin->username . "\t";
                } else {
                    $row .= '';
                }
                $row .= $value->cld2description . "\t";
                $row .= $value->cld1rate . "\t";
                $row .= $value->cld2rate . "\t";
                $row .= "\n";
                echo $row;
            }
            exit;
        } else {
            return $this->redirect(['cdr']);
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
        ]);
    }

    /**
     * @param $id
     *
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */

    public function actionCreateBillgroup()
    {
        $model = new Billgroup([
            'scenario' => Billgroup::SCENARIO_CREATE
        ]);

        $countries = Country::find()->groupBy('Country')->asArray()->all();
        $suppliers = Supplier::find()->asArray()->all();

        \Yii::$app->view->title = \Yii::t('app', 'Create Bill Group');
        $post = \Yii::$app->getRequest()->post();
        if ($post) {
            $post['Billgroup']['countrynetwork_id'] = 1;
            $post['Billgroup']['cld1rate'] = '0.00';
            $post['Billgroup']['cld2rate'] = '0.00';
            $post['Billgroup']['cld3rate'] = '0.00';
            $post['Billgroup']['cost_rate'] = '0.00';
        }
        if ($model->load($post) && $model->save()) {
            return $this->redirect(['update-billgroup', 'id' => $model->id]);
        } else {
            return $this->render('billgroup-form', [
                'action' => 'create',
                'model' => $model,
                'countries' => $countries,
                'suppliers' => $suppliers
            ]);
        }
    }

    /**
     * @param $id
     *
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */

    public function actionUploadNumbers()
    {
        $model = new Numbers([
            'scenario' => Numbers::SCENARIO_CREATE
        ]);

        $billgroups = Billgroup::find()->asArray()->all();
        $suppliers = Supplier::find()->asArray()->all();

        \Yii::$app->view->title = \Yii::t('app', 'Upload Numbers');
        $post = \Yii::$app->getRequest()->post();
        $data = ['Numbers' => []];
        if ($post) {            
            for ($i = 1; $i <= $post['number_qty']; $i++) {
                $post['Numbers'][$i]['cld1'] = $post['start_number'] + $i;
                $post['Numbers'][$i]['billgroup_id'] = $post['Numbers']['billgroup_id'];
                $post['Numbers'][$i]['service_id'] = $post['Numbers']['service_id'];
                $post['Numbers'][$i]['sender_id'] = $post['Numbers']['sender_id'];
            }
        }
        if ($model->load($post) && $model->save()) {
            return $this->redirect(['billgroups']);
        } else {
            return $this->render('upload-numbers', [
                'action' => 'create',
                'model' => $model,
                'billgroups' => $billgroups,
                'suppliers' => $suppliers
            ]);
        }
    }

    /**
     * @param $id
     *
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdateBillgroup($id)
    {
        $model = $this->findBillgroupModel($id);
        $countries = Country::find()->groupBy('Country')->asArray()->all();
        $suppliers = Supplier::find()->asArray()->all();
        $post = \Yii::$app->getRequest()->post();
        if ($post) {
            $post['Billgroup']['countrynetwork_id'] = 1;
            $post['Billgroup']['cld1rate'] = '0.00';
            $post['Billgroup']['cld2rate'] = '0.00';
            $post['Billgroup']['cld3rate'] = '0.00';
            $post['Billgroup']['cost_rate'] = '0.00';
        }
        if ($model->load($post) && $model->save()) {
            return $this->redirect(['update-billgroup', 'id' => $model->id]);
        } else {
            \Yii::$app->view->title = \Yii::t('app', 'Update {billgroup}', ['billgroup' => $model->name]);

            return $this->render('billgroup-form', [
                'action' => 'update',
                'model' => $model,
                'countries' => $countries,
                'suppliers' => $suppliers
            ]);
        }
    }

    /**
     * @param $id
     *
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteBillgroup($id)
    {
        $model = $this->findBillgroupModel($id);

        $model->delete();

        return $this->redirect(['/admin/billgroups']);
    }

    /**
     * @param $id
     *
     * @return User|null
     * @throws NotFoundHttpException
     */
    protected function findBillgroupModel($id)
    {
        if (($model = Billgroup::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
