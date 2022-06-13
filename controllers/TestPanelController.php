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

class TestPanelController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['test-numbers', 'test-tdr', 'allocate-numbers', 'unallocate-numbers', 'tdr-export'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return User::isTestPanel(Yii::$app->user->identity->id);
                        },

                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'delete-cld' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->redirect('test-numbers');
    }

    public function actionTestNumbers()
    {
        $model = new Fsusertb();
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 20;

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
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 20;
        $mysubusr = User::find()->select('id')->where(['role' => 2]);

        if ($filter == 'all') {
            $filter = '';
        }

        $searchModel = new TdrSearch();

        $mysubusr = User::find()->select('id')->where(['reseller_id' => Yii::$app->user->identity->id, 'role' => 3]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mysubusr, $search, User::isUserAdmin(\Yii::$app->user->id), User::isTestPanel(\Yii::$app->user->id));
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



}