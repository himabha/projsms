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

use app\models\TdrSearch;
use app\models\TdrSearchSummary;
use app\models\TdrSearchDetailed;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use app\models\Smscdr;

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
                'only' => ['sms-numbers', 'add-user', 'upload', 'update-cld', 'delete-cld', 'delete-user', 'list-assign-cld', 'cdr', 'list-user', 'assign-cld', 'edit-user', 'delete-assigned-cld', 'update-assigned-cld', 'date-report', 'detach-number', 'detach-number-reseller', 'show-assigned-reseller', 'detach-number-reseller-admin', 'show-assigned-reseller-admin', 'show-assigned', 'show-number-routes', 'fs-call-report', 'export-fscall', 'load-search-fields', 'agent-summary'],
                'rules' => [
                    [
                        'actions' => ['sms-numbers', 'add-user', 'upload', 'update-cld', 'delete-cld', 'delete-user', 'list-assign-cld', 'cdr', 'list-user', 'assign-cld', 'edit-user', 'delete-assigned-cld', 'update-assigned-cld', 'date-report', 'detach-number', 'show-assigned', 'detach-number-reseller', 'show-assigned-reseller', 'detach-number-reseller-admin', 'show-assigned-reseller-admin', 'show-number-routes', 'fs-call-report', 'export-fscall', 'load-search-fields', 'agent-summary', 'sms-tdr'],
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
    public function actionSmsNumbers()
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
        $dataProvider->setPagination(['pageSize' => $filter]); 

        return $this->render('add_cld', [
            'dataProvider' => $dataProvider, 
            'searchModel' => $searchModel,
            'summary' => $summary, 
            'search' => $search, 
            'filter' => $filter,
            'countries' => $this->getCountryItems(),
            'billgroups' => $this->getBillgroupItems(),
            'suppliers' => $this->getSupplierItems(),
            'clients' => $this->getResellerAdminItems(),
            'services' => $this->getServicesItems(),

        ]);
    }
    protected function getBillgroupItems()
    {
        $items = [];
        $res = Billgroup::find()->all();
        if(is_array($res) && count($res) > 0)
        {
            foreach($res as $v)
            {
                $items[$v->id] = $v->name;
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
    protected function getResellerAdminItems()
    {
        $items = [0 => "Un-allocated"];
        $res = User::find()->where(['role' => 4])->all();
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
                return $this->redirect(['sms-numbers']);
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
        return $this->redirect(['sms-numbers']);
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
                return $this->redirect(['sms-numbers']);
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
        //$model = new BillingGroup();

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
        $res = \app\models\Currency::find()->all();
        return \yii\helpers\ArrayHelper::map($res, 'id', 'currency');
    }
    protected function getBillcycleItems()
    {
        $res = \app\models\Billcycle::find()->all();
        return \yii\helpers\ArrayHelper::map($res, 'ID', 'billcycle');
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
        \Yii::$app->view->title = \Yii::t('app', 'Upload Numbers');
        $model = new Numbers(['scenario' => 'create']);
        $post = \Yii::$app->getRequest()->post();
        $data= [];
        if ($post) {    
            if(!empty($upload_type)) $upload_type = '#range';
            if($model->load($post) && $model->validate())
            {
                //if($model->validate())
                //{
                    // detect upload type  
                    switch($model->upload_type)
                    {
                        case '#range': 
                            $model->number_list = "";
                            $model->single_number = "";
    
                            $model->start_number = rtrim($model->start_number);
                            
                            if(empty($model->start_number)) { // empty
                                $model->addError('start_number', 'Please enter a number. Min. 7 digits.');
                            } else {
                                if(!is_numeric($model->start_number)) // non numeric
                                {
                                    $model->addError('start_number', 'Not a valid number (positive number only).');
                                } else {
                                    if($model->start_number < 1) // negative
                                    {
                                        $model->addError('start_number', 'Not a valid number (positive number only).');
                                    } else {
                                        $str = strval($model->start_number); 
                                        if(strlen($str) < 7) // min 7 digits
                                        {
                                            $model->addError('start_number', 'Please enter a number (min. 7 digits).');
                                        }
                                    }
                                }
                            }
    
                            if(empty($model->number_qty)) { // empty
                                $model->addError('number_qty', 'Please enter qty (1 - 1000).');
                            } else {
                                if($model->number_qty < 1) // negative
                                {
                                    $model->addError('number_qty', 'Please enter qty (1 - 1000).');
                                } else {
                                    if($model->number_qty > 1000) // max 1000
                                    {
                                        $model->addError('number_qty', 'Please enter qty (1 - 1000).');
                                    }
                                }
                            }
                            break;
                        case '#manual': 
                            $model->start_number = "";
                            $model->number_qty = "";
                            $model->single_number = "";
    
                            $model->number_list = rtrim($model->number_list);
    
                            if(empty($model->number_list))
                            {
                                $model->addError('number_list', 'At least 1 number required');
                            } else {
                                $number_list_arr = explode("\r\n", $model->number_list);
                                $numbers = [];
                                foreach($number_list_arr as $v)
                                {
                                    if(!empty($v) && is_numeric($v) && strlen(strval($v)) > 6 && $v > 0)
                                    {
                                        $numbers[] = $v;
                                    } 
                                }
    
                                if(count($numbers) != count($number_list_arr)){
                                    $model->addError('number_list', 'One or more numbers is not a valid number');
                                } else {
                                    array_unique($numbers);
                                    if(count($numbers) != count($number_list_arr)){
                                        $model->addError('number_list', 'One or more numbers is duplicate');
                                    }
                                }
                            }
                            break;
                        case '#single': 
                            $model->start_number = "";
                            $model->number_qty = "";
                            $model->number_list = ""; 
    
                            $model->single_number = rtrim($model->single_number);                        
                            
                            if(empty($model->single_number))
                            {
                                $model->addError('single_number', 'Please enter a number. Min. 7 digits.');
                            } else {
                                if(!is_numeric($model->single_number)) // non numeric
                                {
                                    $model->addError('single_number', 'Not a valid number (positive number only).');
                                } else {
                                    if($model->single_number < 1) // negative
                                    {
                                        $model->addError('single_number', 'Not a valid number (positive number only).');
                                    } else {
                                        $str = strval($model->single_number); 
                                        if(strlen($str) < 7) // min 7 digits
                                        {
                                            $model->addError('single_number', 'Please enter a number (min. 7 digits).');
                                        }
                                    }
                                }
                            }
                            break;
                    }
                    if(!$model->errors)
                    {
                        $bg_id = $model->billgroup_id;
                        $bg = Billgroup::findOne($bg_id);
                        if(!empty($bg))
                        {
                            switch($model->upload_type)
                            {
                                case '#range': 
                                    $range_arr = [];
                                    for ($i = 1; $i <= $model->number_qty; $i++) {
                                        $range_arr[] = $model->start_number + $i;
                                    }
    
                                    shuffle($range_arr);
    
                                    foreach($range_arr as $v)
                                    {
                                        $new_number = new Numbers();
                                        //$new_number->scenario =  Numbers::SCENARIO_CREATE;
                                        $new_number->status = 1; // default
                                        $new_number->cld1 = $v;
                                        $new_number->cld2 = $v;
                                        $new_number->cost_rate = $bg->cost_rate; 
                                        $new_number->cld1rate = $bg->cld1rate; // default - leave blank
                                        $new_number->cld2rate = 0; // default - leave blank, billgroup has this though
                                        $new_number->cld3rate = 0; // default - leave blank, billgroup has this though
                                        $new_number->cld1description = ''; // leave blank
                                        $new_number->cld2description = ''; // leave blank
                                        $new_number->maxduration = $bg->maxperday; // from billgroup
                                        $new_number->admin_id = 0; // default
                                        $new_number->reseller_id = 0; //default
                                        $new_number->agent_id = 0; //default
                                        $new_number->billcycle_id = $bg->billcycle_id; 
                                        $new_number->currency_id = $bg->currency_id; 
                                        $new_number->billgroup_id = $model->billgroup_id; // $_POST
                                        $new_number->country_id = $bg->country_id; 
                                        $new_number->countrynetwork_id = $bg->countrynetwork_id; 
                                        $new_number->service_id = $model->service_id; // $_POST
                                        $new_number->sender_id = $model->sender_id; // $_POST
                                        $new_number->receiver_id = 0; // default - leave blank
                                        $new_number->allocated_date = null; // default - leave blank
                                        $data[] = $new_number;
                                    }
                                    break;
                                case '#manual': 
                                    $number_list_arr = explode("\n", $model->number_list);
                                    foreach($number_list_arr as $v)
                                    {
                                        $new_number = new Numbers();
                                        //$new_number->scenario =  Numbers::SCENARIO_CREATE;
                                        $new_number->status = 1; // default
                                        $new_number->cld1 = $v;
                                        $new_number->cld2 = $v;
                                        $new_number->cost_rate = $bg->cost_rate; 
                                        $new_number->cld1rate = $bg->cld1rate; // default - leave blank
                                        $new_number->cld2rate = 0; // default - leave blank, billgroup has this though
                                        $new_number->cld3rate = 0; // default - leave blank, billgroup has this though
                                        $new_number->cld1description = ''; // leave blank
                                        $new_number->cld2description = ''; // leave blank
                                        $new_number->maxduration = $bg->maxperday; // from billgroup
                                        $new_number->admin_id = 0; // default
                                        $new_number->reseller_id = 0; //default
                                        $new_number->agent_id = 0; //default
                                        $new_number->billcycle_id = $bg->billcycle_id; 
                                        $new_number->currency_id = $bg->currency_id; 
                                        $new_number->billgroup_id = $model->billgroup_id; // $_POST
                                        $new_number->country_id = $bg->country_id; 
                                        $new_number->countrynetwork_id = $bg->countrynetwork_id; 
                                        $new_number->service_id = $model->service_id; // $_POST
                                        $new_number->sender_id = $model->sender_id; // $_POST
                                        $new_number->receiver_id = 0; // default - leave blank
                                        $new_number->allocated_date = null; // default - leave blank
                                        $data[] = $new_number;
                                    }
                                    break;
                                case '#single': 
                                    if(!empty($model->single_number))
                                    {
                                        // status = 1 - default
                                        // cld1 , cld2 => number , both similar
                                        // cld1rate fetch from billgroup
                                        // admin_id, reseller_id, agent_id keep 0
                                        // maxduration -> maxperday from billgroup
                                        // cld2rate, cld3rate , cld1description and cld2description , receiver_id, allocated_date -> leave blank
                                        // sender_id => supplier id from billgroup table
            
                                        $new_number = new Numbers();
                                        //$new_number->scenario =  Numbers::SCENARIO_CREATE;
                                        $new_number->status = 1; // default
                                        $new_number->cld1 = $model->single_number;
                                        $new_number->cld2 = $model->single_number;
                                        $new_number->cost_rate = $bg->cost_rate; 
                                        $new_number->cld1rate = $bg->cld1rate; // default - leave blank
                                        $new_number->cld2rate = 0; // default - leave blank, billgroup has this though
                                        $new_number->cld3rate = 0; // default - leave blank, billgroup has this though
                                        $new_number->cld1description = ''; // leave blank
                                        $new_number->cld2description = ''; // leave blank
                                        $new_number->maxduration = $bg->maxperday; // from billgroup
                                        $new_number->admin_id = 0; // default
                                        $new_number->reseller_id = 0; //default
                                        $new_number->agent_id = 0; //default
                                        $new_number->billcycle_id = $bg->billcycle_id; 
                                        $new_number->currency_id = $bg->currency_id; 
                                        $new_number->billgroup_id = $model->billgroup_id; // $_POST
                                        $new_number->country_id = $bg->country_id; 
                                        $new_number->countrynetwork_id = $bg->countrynetwork_id; 
                                        $new_number->service_id = $model->service_id; // $_POST
                                        $new_number->sender_id = $model->sender_id; // $_POST
                                        $new_number->receiver_id = 0; // default - leave blank
                                        $new_number->allocated_date = null; // default - leave blank
                                        $data[] = $new_number;
                                    }
                                    break;
                            }
                            if(is_array($data) && count($data) > 0)
                            {
                                $all_validated = \yii\base\Model::validateMultiple($data); 
                                if($all_validated) 
                                {
                                    $rows = [];
                                    foreach($data as $v)
                                    {
                                        $rows[] = [
                                            'status' => $v->status,
                                            'cld1' => $v->cld1,
                                            'cld2' => $v->cld2,
                                            'cost_rate' => $v->cost_rate,
                                            'cld1rate' => $v->cld1rate,
                                            'cld2rate' => $v->cld2rate,
                                            'cld3rate' => $v->cld3rate,
                                            'cld1description' => $v->cld1description,
                                            'cld2description' => $v->cld2description,
                                            'maxduration' => $v->maxduration,
                                            'admin_id' => $v->admin_id,
                                            'reseller_id' => $v->reseller_id,
                                            'agent_id' => $v->agent_id,
                                            'billcycle_id' => $v->billcycle_id,
                                            'currency_id' => $v->currency_id,
                                            'billgroup_id' => $v->billgroup_id,
                                            'country_id' => $v->country_id,
                                            'countrynetwork_id' => $v->countrynetwork_id,
                                            'service_id' => $v->service_id,
                                            'sender_id' => $v->sender_id,
                                            'receiver_id' => $v->receiver_id,
                                            'allocated_date' => $v->allocated_date
                                       ];
                                    }
                                    $postModel = new Fsmastertb;
                                    $batch = \Yii::$app->db->createCommand()->batchInsert(Fsmastertb::tableName(), [
                                        'status',
                                        'cld1',
                                        'cld2',
                                        'cost_rate',
                                        'cld1rate',
                                        'cld2rate',
                                        'cld3rate',
                                        'cld1description',
                                        'cld2description',
                                        'maxduration',
                                        'admin_id',
                                        'reseller_id',
                                        'agent_id',
                                        'billcycle_id',
                                        'currency_id',
                                        'billgroup_id',
                                        'country_id',
                                        'countrynetwork_id',
                                        'service_id',
                                        'sender_id',
                                        'receiver_id',
                                        'allocated_date'
                                    ], $rows)->execute();
                                    if($batch)
                                    {
                                        \Yii::$app->session->setFlash('success', "Uplaoded");
                                        return $this->redirect('billgroups');
                                    } else {
                                        \Yii::$app->session->setFlash('error', "Upload failed");
                                    }
                                }
                            } else {
                                \Yii::$app->session->setFlash('error', "At least one number is required");
                            }
                        } else {
                            $model->addError('billgroup_id', 'not a valid billgroup');
                        }
                        
                    }
                //}
            }
        }
        $deps = $this->uploadNumbersDeps(isset($post['Numbers']['billgroup_id']) ? intval($post['Numbers']['billgroup_id']) : 0, isset($post['Numbers']['sender_id']) ? intval($post['Numbers']['sender_id']) : 0, isset($post['Numbers']['service_id']) ? intval($post['Numbers']['service_id']) : 0);
        return $this->render('upload-numbers', [
            'action' => 'create',
            'model' => $model,
            'billgroups' => $deps['billgroups'],
            'suppliers' => $deps['suppliers'],
            'services' => $deps['services'],
            'upload_type' => !empty($model->upload_type) ? $model->upload_type : "#range" 
        ]);
    }

    // dropdownlist values - ajax requests
    public function  actionUploadNumbersDeps()
    {
        $response = [
            'success' => false
        ];
        if(isset($_POST))
        {
            $post = $_POST;
            $billgroup_id = !empty($post['bg']) ? intval($post['bg']) : "";
            $supplier_id = !empty($post['sup']) ? intval($post['sup']) : "";
            $service_id = !empty($post['ser']) ? intval($post['ser']) : "";
            $initiator = !empty($post['initiator']) ? $post['initiator'] : "";
            $deps = $this->uploadNumbersDeps($billgroup_id, $supplier_id, $service_id, $initiator);
            if(is_array($deps) && count($deps) > 0)
            {
                $response = [
                    'success' => true,
                    'data' => $deps
                ];
            }
        }
        return json_encode($response);
    }
    // dropdownlist values
    protected function uploadNumbersDeps($billgroup_id = null, $supplier_id = null , $service_id = null, $initiator = null)
    {
        $response = [
            'billgroups' => [],
            'suppliers' => [],
            'services' => [],
            'defaults' => [
                'bill' => $billgroup_id,
                'sup' => $supplier_id,
                'ser' => $service_id 
            ]
        ];
        if(empty($billgroup_id) && empty($supplier_id) && empty($service_id))
        {
            $billgroups = Billgroup::find()->asArray()->all();
            $response['billgroups'] = \yii\helpers\ArrayHelper::map($billgroups, 'id', 'name');
            $suppliers = Supplier::find()->asArray()->all();
            $response['suppliers'] = \yii\helpers\ArrayHelper::map($suppliers, 'id', 'name');
            $response['services'] = \Yii::$app->params['services'];
        } else {
            if(!empty($billgroup_id) || !empty($supplier_id) || !empty($service_id))
            {
                $bgs_bill = Billgroup::find();
                $bgs_bill->select('id, name, sender_id, service');

                $where = false;
                if(!empty($billgroup_id)) {
                    $bgs_bill->where(['id' => $billgroup_id]);
                    $where = true;
                }  

                if(!empty($supplier_id)) {
                    if($where) $bgs_bill->andWhere(['sender_id' => $supplier_id]);
                    else $bgs_bill->where(['sender_id' => $supplier_id]);
                    $where = true;
                }  
                if(!empty($service_id)) {
                    if($where) $bgs_bill->andWhere(['service' => $service_id]);  
                    else $bgs_bill->where(['service' => $service_id]);  
                    $where = true;
                }

                $res = $bgs_bill->all();
                $response['billgroups'] = \yii\helpers\ArrayHelper::map($res, 'id', 'name');
                if($initiator == 'numbers-billgroup_id')
                {
                    if(is_array($res) && count($res) == 1)
                    {
                        $response['defaults']['sup'] = $res[0]['sender_id'];
                        $response['defaults']['ser'] = $res[0]['service'];
                    }
                }                
                $sup_ids = \yii\helpers\ArrayHelper::getColumn($res, 'sender_id');
                $ser_ids = \yii\helpers\ArrayHelper::getColumn($res, 'service');


                if(is_array($sup_ids) && count($sup_ids) > 0)
                {
                    $sup_ids = array_unique($sup_ids, SORT_NUMERIC);
                    $suppliers = Supplier::find()->where(['IN', 'id', $sup_ids])->all();
                    $response['suppliers'] = \yii\helpers\ArrayHelper::map($suppliers, 'id', 'name');
                } else {
                    $suppliers = Supplier::find()->asArray()->all();
                    $response['suppliers'] = \yii\helpers\ArrayHelper::map($suppliers, 'id', 'name');
                }

                if(is_array($ser_ids) && count($ser_ids) > 0)
                {
                    $ser_ids = array_unique($ser_ids, SORT_NUMERIC);
                    foreach(\Yii::$app->params['services'] as $k=>$v)
                    {
                        if(in_array($k, $ser_ids))
                        {
                            $response['services'][$k] = $v;
                        }
                    }
                } else {
                    $response['services'] = \Yii::$app->params['services'];
                }
            }
        }



        return $response;
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

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search, true);
        $dataProvider->setPagination(['pageSize' => $filter]); 

        return $this->render('tdr', [
            'dataProvider' => $dataProvider, 
            'searchModel' => $searchModel,
            'search' => $search, 
            'filter' => $filter,
            'billgroups' => $this->getBillgroupItems(),
            'clients' => $this->getResellerAdminItems(),
            'suppliers' => $this->getSupplierItems(),
        ]);
    }

    public function actionSummaryReport()
    {
        $model = new Fsusertb();
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 20;
        $mysubusr = User::find()->select('id')->where(['role' => 2]);

        if ($filter == 'all') {
            $filter = '';
        }

        $searchModel = new TdrSearchSummary();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search, true, false);
        $dataProvider->setPagination(['pageSize' => $filter]); 

        $searchModel_1 = new TdrSearchDetailed();
        $dataProvider_1 = $searchModel_1->search(Yii::$app->request->queryParams, $mysubusr, $search, true, false);
        $dataProvider_1->setPagination(['pageSize' => $filter]); 

        return $this->render('summary_report', [
            'dataProvider' => $dataProvider, 
            'dataProvider_1' => $dataProvider_1, 
            'search' => $search, 
            'filter' => $filter,
            'clients' => $this->getResellerAdminItems(),
            'suppliers' => $this->getSupplierItems(),
            'billgroups' => $this->getBillgroupItems(),
        ]);
    }

    public function actionDetailedReport()
    {
        $model = new Fsusertb();
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 20;
        $mysubusr = User::find()->select('id')->where(['role' => 2]);

        if ($filter == 'all') {
            $filter = '';
        }

        $searchModel = new TdrSearchSummary();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search, true, true);
        $dataProvider->setPagination(['pageSize' => $filter]); 

        $searchModel_1 = new TdrSearchDetailed();
        $dataProvider_1 = $searchModel_1->search(Yii::$app->request->queryParams, $mysubusr, $search, true, true);
        $dataProvider_1->setPagination(['pageSize' => $filter]); 

        return $this->render('detail_report', [
            'dataProvider' => $dataProvider, 
            'dataProvider_1' => $dataProvider_1, 
            'search' => $search, 
            'filter' => $filter,
            'clients' => $this->getResellerAdminItems(),
            'suppliers' => $this->getSupplierItems(),
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
            'Supplier',
            'Delivered Time'
        ];

        if(isset($_SERVER['QUERY_STRING']))
        {
            $searchModel = new TdrSearch();
            $mysubusr = User::find()->select('id')->where(['role' => 2]);
            $query = $searchModel->search(\Yii::$app->request->queryParams, $mysubusr, '', true)->query;
            $params = isset(\Yii::$app->request->queryParams['TdrSearch']) ? \Yii::$app->request->queryParams['TdrSearch'] : [];

            $billgroup_name = 'None';
            if(!empty(intval($params['billgroup_id'])))
            {
                $obj = Billgroup::findOne(intval($params['billgroup_id']));
                if(isset($obj->name)) $billgroup_name = $obj->name;
            }

            $client_name = 'None';
            if(isset($params['admin_id']) && trim($params['admin_id']) != "")
            {
                if($params['admin_id'] == 0)
                {
                    $client_name = 'Un-located';
                } else {
                    $obj = User::findOne(intval($params['admin_id']));
                    if(isset($obj->username)) $client_name = $obj->username;
                }
            }

            $supplier_name = 'None';
            if(!empty(intval($params['sender_id'])))
            {
                $obj = Supplier::findOne(intval($params['sender_id']));
                if(isset($obj->name)) $supplier_name = $obj->name;
            }

            $filters = [
                'Bill Group' => $billgroup_name,
                'Client' => $client_name,
                'Supplier' => $supplier_name,
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
                            $temp[$hk] = isset($v->id) ? $v->id : "";
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
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->resellerAdmin) ? $v->resellerAdmin->username : "");
                            $temp[$hk] = isset($v->resellerAdmin) ? $v->resellerAdmin->username : "";
                            break; 
                        case "Supplier":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->supplier) ? $v->supplier->name : "");
                            $temp[$hk] = isset($v->supplier) ? $v->supplier->name : "";
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
            'Supplier',
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
            $query = $searchModel->search(\Yii::$app->request->queryParams, $mysubusr, '', true, false)->query;
            $params = isset(\Yii::$app->request->queryParams['TdrSearchSummary']) ? \Yii::$app->request->queryParams['TdrSearchSummary'] : [];
            // FILTERS
            $billgroup_name = 'None';
            if(!empty(intval($params['billgroup_id'])))
            {
                $obj = Billgroup::findOne(intval($params['billgroup_id']));
                if(isset($obj->name)) $billgroup_name = $obj->name;
            }
            $client_name = 'None';
            if(isset($params['admin_id']) && trim($params['admin_id']) != "")
            {
                if($params['admin_id'] == 0)
                {
                    $client_name = 'Un-located';
                } else {
                    $obj = User::findOne(intval($params['admin_id']));
                    if(isset($obj->username)) $client_name = $obj->username;
                }
            }
            $supplier_name = 'None';
            if(!empty(intval($params['sender_id'])))
            {
                $obj = Supplier::findOne(intval($params['sender_id']));
                if(isset($obj->name)) $supplier_name = $obj->name;
            }
            $filters = [
                'Bill Group' => $billgroup_name,
                'Client' => $client_name,
                'Supplier' => $supplier_name,
                'Delivered Time' => !empty($params['delivered_time']) ? $params['delivered_time'] : 'None'
            ];

            $searchModel_2 = new TdrSearchDetailed();
            $query_2 = $searchModel_2->search(\Yii::$app->request->queryParams, $mysubusr, '', true, false)->query;
            $rows_2 = $query_2->all();

        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $csv_cols = ["", "", "", "", "", "", "", ""];
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
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->resellerAdmin) ? $v->resellerAdmin->username : "");
                            $temp[$hk] =  isset($v->resellerAdmin) ? $v->resellerAdmin->username : "";
                            break; 
                        case "Supplier":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->supplier) ? $v->supplier->name : "");
                            $temp[$hk] = isset($v->supplier) ? $v->supplier->name : "";
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
            'Supplier',
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
            $query = $searchModel->search(\Yii::$app->request->queryParams, $mysubusr, '', true, true)->query;
            $params = isset(\Yii::$app->request->queryParams['TdrSearchSummary']) ? \Yii::$app->request->queryParams['TdrSearchSummary'] : [];
            // FILTERS
            $billgroup_name = 'None';
            if(!empty(intval($params['billgroup_id'])))
            {
                $obj = Billgroup::findOne(intval($params['billgroup_id']));
                if(isset($obj->name)) $billgroup_name = $obj->name;
            }
            $client_name = 'None';
            if(isset($params['admin_id']) && trim($params['admin_id']) != "")
            {
                if($params['admin_id'] == 0)
                {
                    $client_name = 'Un-located';
                } else {
                    $obj = User::findOne(intval($params['admin_id']));
                    if(isset($obj->username)) $client_name = $obj->username;
                }
            }
            $supplier_name = 'None';
            if(!empty(intval($params['sender_id'])))
            {
                $obj = Supplier::findOne(intval($params['sender_id']));
                if(isset($obj->name)) $supplier_name = $obj->name;
            }
            $filters = [
                'Bill Group' => $billgroup_name,
                'Client' => $client_name,
                'Supplier' => $supplier_name,
                'Delivered Time' => !empty($params['delivered_time']) ? $params['delivered_time'] : 'None'
            ];

            $searchModel_2 = new TdrSearchDetailed();
            $query_2 = $searchModel_2->search(\Yii::$app->request->queryParams, $mysubusr, '', true, true)->query;
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
                        case "Supplier":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->supplier) ? $v->supplier->name : "");
                            $temp[$hk] = isset($v->supplier) ? $v->supplier->name : "";
                            break; 
                        case "Client":
                            $sheet->setCellValueByColumnAndRow($hk + 1, $row , isset($v->resellerAdmin) ? $v->resellerAdmin->username : "");
                            $temp[$hk] = isset($v->resellerAdmin) ? $v->resellerAdmin->username : "";
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
}