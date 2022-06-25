<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\Fstest;
use app\models\FstestSearch;
use app\models\Fsusertb;
use app\models\Fsresellertb;
use app\models\Fsmastertb;
use app\models\Fsadmintb;
use app\models\Fsaccess;
use app\models\FsaccessSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Fscallsnow;
use yii\data\ActiveDataProvider;
use yii\base\UserException;
use app\models\Fsmycdr;
use app\models\Brandname;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'dashboard'],
                'rules' => [
                    [
                        'actions' => ['logout', 'dashboard', 'change-password'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['get', 'post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $this->layout = '@app/views/layouts/admin';

        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['dashboard']);
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(['dashboard']);
        }
        $model->username = '';
        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Displays test active calls.
     *
     * @return string
     */
    public function actionTestActiveCalls()
    {
        $foldername = array_reverse(explode("/", getcwd()))[0];
        $brandname = explode(".com", $_SERVER['SERVER_NAME'])[0];
        $findBrand = Brandname::findOne(['foldername' => $foldername, 'name' => $brandname]);
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['dashboard']);
        }
        $caller_id = isset($_GET['caller_id']) ? $_GET['caller_id'] : '';
        $called_no = isset($_GET['called_no']) ? $_GET['called_no'] : '';
        $myclds = Fsresellertb::find()->select('cld1')->where(['reseller_id' => $findBrand ? $findBrand->admin_id : 0, 'closing_date' => NULL]);
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

        return $this->render('test_active_calls', ['dataProvider' => $dataProvider, 'count' => $count, 'caller_id' => $caller_id, 'brandData' => $findBrand, 'called_no' => $called_no]);
    }

    /**
     * Displays dashboard for user and admin.
     *
     * @return string
     */
    public function actionDashboard()
    {
        if (User::isUserAdmin(Yii::$app->user->identity->id)) {
            return $this->render('dashboard', []);
        } elseif (User::isReseller(Yii::$app->user->identity->id)) {
            return $this->render('reseller_dashboard', []);
        } elseif (User::isReSellerAdmin(Yii::$app->user->identity->id)) {
            return $this->render('reseller_admin_dashboard', []);
        } else {
            return $this->render('user_dashboard', []);
        }
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        $this->layout = '@app/views/layouts/admin';

        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['dashboard']);
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(['dashboard']);
        }
        $model->username = '';
        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionAccess()
    {
        $foldername = array_reverse(explode("/", getcwd()))[0];
        $brandname = explode(".com", $_SERVER['SERVER_NAME'])[0];
        $findBrand = Brandname::findOne(['foldername' => $foldername, 'name' => $brandname]);
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
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $findBrand ? $findBrand->admin_id : 0);

        return $this->render('test_access', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'called_destination' => $called_destination,
            'called_number' => $called_number
        ]);
    }


    public function actionTestNumbers()
    {
        $foldername = array_reverse(explode("/", getcwd()))[0];
        $brandname = explode(".com", $_SERVER['SERVER_NAME'])[0];
        $findBrand = Brandname::findOne(['foldername' => $foldername, 'name' => $brandname]);
        $get = Yii::$app->request->queryParams;

        if (isset($get['FstestSearch']['Country'])) {
            $Country = $get['FstestSearch']['Country'];
        } else {
            $Country = '';
        }

        if (isset($get['FstestSearch']['Test_Number'])) {
            $Test_Number = $get['FstestSearch']['Test_Number'];
        } else {
            $Test_Number = '';
        }

        $searchModel = new FstestSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $findBrand ? $findBrand->admin_id : 0);

        return $this->render('test_numbers', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'Country' => $Country,
            'Test_Number' => $Test_Number
        ]);
    }

    public function actionAdUsLog($id)
    {
        $user = User::findOne($id);
        Yii::$app->user->switchIdentity($user, 0);
        return $this->redirect(['dashboard']);
    }

    /*
    * Change password
    */
    public function actionChangePassword()
    {
        $model = User::findOne(Yii::$app->user->id);
        if (!$model) {
            throw new UserException("User doesnot exist");
        }
        $model->scenario = 'resetpass';
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $model->setPassword($model->new_pass);
                if ($model->save()) {
                    return $this->redirect(['dashboard']);
                } else {
                    throw new UserException("Failed to update. Try again.");
                }
            }
        }
        return $this->render('change_password', ['model' => $model]);
    }

    /*
    * Show Test CDR details
    */

    public function actionTestCdr()
    {
        $foldername = array_reverse(explode("/", getcwd()))[0];
        $brandname = explode(".com", $_SERVER['SERVER_NAME'])[0];
        $findBrand = Brandname::findOne(['foldername' => $foldername, 'name' => $brandname]);

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

        $clds = Fsresellertb::find()->where(['reseller_id' => $findBrand ? $findBrand->admin_id : 0, 'closing_date' => NULL])->all();

        $i = 0;
        $query = Fsmycdr::find()->select(['call_startdate', 'ani', 'called_number', 'cld1', 'country', 'call_duration'])->where(['between', 'call_startdate', $startT, $endT]);

        $date = $start . ' - ' . $end;


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $condition[] = 'OR';

        if ($clds) {
            foreach ($clds as $cld) {
                $condition[] = ['and', ['cld1' => $cld->cld1], ['>=', 'call_startdate', $cld->assigned_date]];
            }
        } else {
            $condition[] = ['cld1' => '.1'];
        }

        $query->andFilterWhere($condition)
            ->andFilterWhere(['like', 'ani', $caller_id])
            ->andFilterWhere(['like', 'called_number', $called_no]);


        $query = $query->orderBy(['call_startdate' => SORT_DESC]);

        return $this->render('test_cdr', ['dataProvider' => $dataProvider, 'date' => $date, 'caller_id' => $caller_id, 'called_no' => $called_no]);
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

        $clds = Fsusertb::find()->where(['user_id' => '3523034', 'closing_date' => NULL])->all();

        $query = Fsmycdr::find()
            ->select(['call_startdate', 'ani', 'called_number', 'cld1', 'country', 'call_duration'])
            ->where(['between', 'call_startdate', $startT, $endT]);

        $condition[] = 'OR';

        if ($clds) {
            foreach ($clds as $cld) {
                $condition[] = ['and', ['cld1' => $cld->cld1], ['>=', 'call_startdate', $cld->assigned_date]];
            }
        } else {
            $condition[] = ['cld1' => '.1'];
        }

        $query = $query->andWhere($condition);

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

            echo 'call_startdate' . "\t" . 'Caller Id' . "\t" . 'called_number' . "\t" . 'country' . "\t" . 'Call Duration' . "\n";
            if ($query)
                foreach ($query as $value) {

                    echo $value->call_startdate . "\t" . $value->ani . "\t" . $value->called_number . "\t" . $value->country . "\t" . round($value->call_duration / 60, 2) . "\n";
                }
            exit;
        } else {
            return $this->redirect(['test-cdr']);
        }
    }

    public function actionExportTestNumber()
    {
        ini_set('memory_limit', '-1');

        $country = isset($_GET['Country']) ? $_GET['Country'] : '';
        $Test_Number = isset($_GET['Test_Number']) ? $_GET['Test_Number'] : '';

        $query = Fstest::find();

        if (!empty($country)) {
            $query = $query->andWhere(['like', 'Country', $country]);
        }

        if (!empty($Test_Number)) {
            $query = $query->andWhere(['like', 'Test_Number', $Test_Number]);
        }

        $query = $query->all();


        if ($query) {

            $filename = "TestNumber.xls";
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");

            echo 'Country' . "\t" . 'Test Number' . "\n";
            if ($query)
                foreach ($query as $value) {

                    echo $value->Country . "\t" . $value->Test_Number . "\n";
                }
            exit;
        } else {
            return $this->redirect(['test-numbers']);
        }
    }
}
