<?php

use yii\helpers\Html;
use yii\helpers\CHtml;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

if($action == "create")
{
    $base_url = \Yii::$app->request->baseUrl; 

    $this->registerJs('
        jQuery(document).ready(function(){
            let upload_type = "' . $upload_type . '";
            if(upload_type && upload_type != "")
            {
                jQuery("#upload_tabs a.nav-link").each(function(item){
                    if(jQuery(this).attr("href") == upload_type)
                    {
                        jQuery("#hdn_upload_type").val(jQuery(this).attr("href"));
                        jQuery(this).trigger("click");
                    }
                });
            }
            jQuery("#upload_tabs a.nav-link").click(function(){
                let href = jQuery(this).attr("href");
                if(href == "#range" || href == "#manual" || href == "#single") {
                    jQuery("#hdn_upload_type").val(href);
                }
            });
            jQuery("#numbers-billgroup_id, #numbers-sender_id, #numbers-service_id").change(function(){
                let data = {"bg": jQuery("#numbers-billgroup_id").val(), "sup": jQuery("#numbers-sender_id").val(), "ser": jQuery("#numbers-service_id").val(), "initiator": this.id};
                jQuery("#numbers-billgroup_id, #numbers-sender_id, #numbers-service_id, #btnSaveUpload").attr("disabled", "disabled");
                jQuery.ajax({
                    url: "' . $base_url . '/admin/upload-numbers-deps",
                    type: "POST",
                    dataType: "json",
                    data: data
                }).done(function(response){
                    if(response.success && response.data)
                    {
                        let bg_options = "<option>Select Bill Group</option>";
                        Object.entries(response.data.billgroups).forEach(([key, val]) => {
                            if(key == response.data.defaults.bill) bg_options += "<option selected value=" + key + ">" + val + "</option>"; 
                            else bg_options += "<option value=" + key + ">" + val + "</option>"; 
                        });
                        jQuery("#numbers-billgroup_id").html(bg_options);

                        let sup_options = "<option>Select Supplier</option>";
                        Object.entries(response.data.suppliers).forEach(([key, val]) => {
                            if(key == response.data.defaults.sup) sup_options += "<option selected value=" + key + ">" + val + "</option>"; 
                            else sup_options += "<option value=" + key + ">" + val + "</option>"; 
                        });
                        jQuery("#numbers-sender_id").html(sup_options);

                        let ser_options = "<option>Select Service</option>";
                        Object.entries(response.data.services).forEach(([key, val]) => {
                            if(key == response.data.defaults.ser) ser_options += "<option selected value=" + key + ">" + val + "</option>"; 
                            else ser_options += "<option value=" + key + ">" + val + "</option>"; 
                        });
                        jQuery("#numbers-service_id").html(ser_options);
                    }
                }).always(function(){
                    setTimeout(function(){
                        jQuery("#numbers-billgroup_id, #numbers-sender_id, #numbers-service_id, #btnSaveUpload").removeAttr("disabled");
                    }, 2000);
                });
            });
        }); 
    ');
}
?>
<div class="content">
    <div class="container-fluid">
        <div class="card ">
            <div class="card-header card-header-primary card-header-icon">
                <div class="card-icon">
                    <i class="material-icons">account_box</i>
                </div>
                <h4 class="card-title">
                    <?= \Yii::t('app', 'Upload Numbers') ?>
                    <div class="pull-right">
                        <?= Html::a(Html::tag('b', 'keyboard_arrow_left', ['class' => 'material-icons']), ['bill-groups'], [
                            'class' => 'btn btn-xs btn-success btn-round btn-fab',
                            'rel' => "tooltip",
                            'data' => [
                                'placement' => 'bottom',
                                'original-title' => 'Back'
                            ],
                        ]) ?>
                    </div>
                </h4>
            </div>
            <div class="card-body">
                <?php $form = ActiveForm::begin([
                    'fieldConfig' => [
                        'template' => "{input} {error}",
                    ]
                ]); ?>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group bmd-form-group">
                            <?php echo $form->field($model, 'billgroup_id')->dropDownList($billgroups,['prompt' => 'Select Bill Group']); ?>
                            <div class="form-group bmd-form-group">
                                <?php echo $form->field($model, 'sender_id')->dropDownList($suppliers,['prompt' => 'Select Supplier']); ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group bmd-form-group">
                                <?php echo $form->field($model, 'service_id')->dropDownList($services, ['prompt' => 'Select Service']); ?>
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="card">
                                <div class="card-header card-header-tabs card-header-primary">
                                    <div class="nav-tabs-navigation">
                                        <div class="nav-tabs-wrapper" id="upload_tabs">
                                            <span class="nav-tabs-title">Upload Type:</span>
                                            <!-- <input type="hidden" name="hdn_upload_type" id="hdn_upload_type"> -->
                                            <?= $form->field($model, 'upload_type')->hiddenInput(['id' => 'hdn_upload_type']); ?>
                                            <ul class="nav nav-tabs" data-tabs="tabs">
                                                <li class="nav-item">
                                                    <a class="nav-link active" href="#range" data-toggle="tab">
                                                        <i class="material-icons">bug_report</i> Range
                                                        <div class="ripple-container"></div>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" href="#manual" data-toggle="tab">
                                                        <i class="material-icons">code</i> Manual
                                                        <div class="ripple-container"></div>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" href="#single" data-toggle="tab">
                                                        <i class="material-icons">cloud</i> Single
                                                        <div class="ripple-container"></div>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="range">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="form-group bmd-form-group">
                                                        <label for="" class="bmd-label-floating">Start Number:</label>
                                                        <?= $form->field($model, 'start_number')->textInput(['maxlength' => 20]); ?>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group bmd-form-group">
                                                        <label for="" class="bmd-label-floating">Number Qty:</label>
                                                        <?= $form->field($model, 'number_qty')->textInput(['type' => 'number', 'step' => 1]); ?>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="tab-pane" id="manual">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="form-group bmd-form-group">
                                                        <label for="" class="bmd-label-floating">Number List</label>
                                                        <?= $form->field($model, 'number_list')->textarea(['rows' => 6]); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="single">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="form-group bmd-form-group">
                                                        <label for="" class="bmd-label-floating">Single Number:</label>
                                                        <?= $form->field($model, 'single_number')->textInput(); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer ml-auto mr-auto">
                    <?= Html::submitButton(\Yii::t('app', 'Save'), ['id' => 'btnSaveUpload', 'class' => 'btn btn-success']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>