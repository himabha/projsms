<?php
namespace app\controllers;

use Yii;
use app\models\Fscdr;
use app\models\Fsusertb;
use yii\console\Controller;
use app\models\Fscallreport;


Class CronjobController extends \yii\web\Controller
{
    /*
    * Update agent details in fscdr
    */
    public function actionAddAgent()
    {
        $model = Fscdr::find()->where(['agent_id' => NULL])->limit(3000)->all();        
    	foreach ($model as $fscdr) {
    		$usertb = Fsusertb::find()->where(['closing_date' => NULL,'cld1' => $fscdr->cld1])->one();
    		if ($usertb) {
    			$fscdr->agent_id = $usertb->user_id;
    			$fscdr->save();
    		} else {
    			$fscdr->agent_id = 1;
    			$fscdr->save();
    		}
    	}
    }
    /*
    * Cron to upload yesterday fscallreport
    */
    public function actionFscallReport()
    {
        $query = \Yii::$app->db->createCommand()
        ->delete('fscallreport', ['Date' => date('Y-m-d',strtotime("-1 day")).' 00:00:00'])->execute();

        $yesterday = date('Y-m-d',strtotime('-1 day')).' 00:00:01';
        $today = date('Y-m-d').' 23:59:59';

        //$yesterday = '2018-11-13 00:00:01';
        //$today = '2018-11-14 23:59:59';

        $query2 = "SELECT date(a.call_startdate) as Date, a.agent_id as Agent_Id, b.cld1description as Country, a.ani as Caller_Id, a.cld1 as CLD1, b.cld1rate as CLD1Rate, b.cld2rate as CLD2Rate, count(a.cld1) as Total_Calls, sum(a.call_duration)/60.0 as Total_Duration, ((sum(a.call_duration)/60.0) * b.cld1rate) as Charges, ((sum(a.call_duration)/60.0) * b.cld2rate) as Cost FROM fscdr a, fsmastertb b WHERE a.cld1 = b.cld1 AND a.call_duration > 0 AND a.call_startdate BETWEEN '$yesterday' AND '$today' GROUP BY date(a.call_startdate), a.agent_id, b.cld1description, a.ani, a.cld1, b.cld1rate, b.cld2rate";
        //echo $query2; exit();
        $result =  \Yii::$app->db->createCommand($query2)->queryAll();
        if ($result) {
            foreach ($result as $report) {
                $Fscallreport = new Fscallreport();
                $Fscallreport->Date = $report['Date'];
                $Fscallreport->agent_id = $report['Agent_Id'];
                $Fscallreport->Country = $report['Country'];
                $Fscallreport->Caller_ID = $report['Caller_Id'];
                $Fscallreport->Cld1 = $report['CLD1'];
                $Fscallreport->Cld1_Rate = $report['CLD1Rate'];
                $Fscallreport->Cld2_Rate = $report['CLD2Rate'];
                $Fscallreport->Total_Calls = $report['Total_Calls'];
                $Fscallreport->Call_Duration = $report['Total_Duration'];
                $Fscallreport->Charges = $report['Charges'];
                $Fscallreport->Cost = $report['Cost'];
                //$Fscallreport->save();
                if (!$Fscallreport->save()) {
                    $data = PHP_EOL;
                    $errors = $Fscallreport->getErrors();
                    //$data .=  (string)$Fscallreport->getErrors();
                    if ($errors) {
                        foreach ($errors as $error) {
                            if (is_array($error)) {
                                foreach ($error as $key => $value) {
                                    $data .= $report['Date']."  ".$value;
                                    $data .= PHP_EOL;
                                }
                            } else {
                                $data .= $error;
                                $data .= PHP_EOL;
                            }
                            
                        }
                    }
                    $file= Yii::getAlias('@app/crone/cron_error.txt'); 
                    $fp = fopen($file, 'a');
                    fwrite($fp, $data);
                    fclose($fp);
                }
            }
        }
    }

    public function actionRunReport()
    {
        $query = \Yii::$app->db->createCommand()
        ->delete('fscallreport', ['Date' => '2019-12-08 00:00:00'])->execute();

        // $yesterday = date('Y-m-d',strtotime('-1 day')).' 00:00:01';
        // $today = date('Y-m-d').' 23:59:59';

        $yesterday = '2019-12-08 00:00:01';
        $today = '2019-12-09 23:59:59';

        $query2 = "SELECT date(a.call_startdate) as Date, a.agent_id as Agent_Id, b.cld1description as Country, a.ani as Caller_Id, a.cld1 as CLD1, b.cld1rate as CLD1Rate, b.cld2rate as CLD2Rate, count(a.cld1) as Total_Calls, sum(a.call_duration)/60.0 as Total_Duration, ((sum(a.call_duration)/60.0) * b.cld1rate) as Charges, ((sum(a.call_duration)/60.0) * b.cld2rate) as Cost FROM fscdr a, fsmastertb b WHERE a.cld1 = b.cld1 AND a.call_duration > 0 AND a.call_startdate BETWEEN '$yesterday' AND '$today' GROUP BY date(a.call_startdate), a.agent_id, b.cld1description, a.ani, a.cld1, b.cld1rate, b.cld2rate";
        //echo $query2; exit();
        $result =  \Yii::$app->db->createCommand($query2)->queryAll();
        if ($result) {
            foreach ($result as $report) {
                $Fscallreport = new Fscallreport();
                $Fscallreport->Date = $report['Date'];
                $Fscallreport->agent_id = $report['Agent_Id'];
                $Fscallreport->Country = $report['Country'];
                $Fscallreport->Caller_ID = $report['Caller_Id'];
                $Fscallreport->Cld1 = $report['CLD1'];
                $Fscallreport->Cld1_Rate = $report['CLD1Rate'];
                $Fscallreport->Cld2_Rate = $report['CLD2Rate'];
                $Fscallreport->Total_Calls = $report['Total_Calls'];
                $Fscallreport->Call_Duration = $report['Total_Duration'];
                $Fscallreport->Charges = $report['Charges'];
                $Fscallreport->Cost = $report['Cost'];
                //$Fscallreport->save();
                if (!$Fscallreport->save()) {
                    $data = PHP_EOL;
                    $errors = $Fscallreport->getErrors();
                    //$data .=  (string)$Fscallreport->getErrors();
                    if ($errors) {
                        foreach ($errors as $error) {
                            if (is_array($error)) {
                                foreach ($error as $key => $value) {
                                    $data .= $report['Date']."  ".$value;
                                    $data .= PHP_EOL;
                                }
                            } else {
                                $data .= $error;
                                $data .= PHP_EOL;
                            }
                            
                        }
                    }
                    $file= Yii::getAlias('@app/crone/cron_error.txt'); 
                    $fp = fopen($file, 'a');
                    fwrite($fp, $data);
                    fclose($fp);
                }
            }
        }
    }
}
