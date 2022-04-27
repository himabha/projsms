<?php

use yii\helpers\Html;
use yii\helpers\CHtml;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
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
                            <?php echo $form->field($model, 'billgroup_id')->dropDownList(
                                ArrayHelper::map($billgroups, 'id', 'name'),
                                ['prompt' => 'Select Bill Group']
                            ); ?>
                            <span class="bmd-help"><?= Html::activeHint($model, 'billgroup_id'); ?></span>
                            <div class="form-group bmd-form-group">
                                <?php echo $form->field($model, 'sender_id')->dropDownList(
                                    ArrayHelper::map($suppliers, 'id', 'name'),
                                    ['prompt' => 'Select Supplier']
                                ); ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group bmd-form-group">
                                <?php echo $form->field($model, 'service_id')->dropDownList([
                                    '1' => 'HYBRID',
                                    '2' => 'PPC',
                                    '3' => 'PPM',
                                    '4' => 'PRN',
                                    '5' => 'SMS',
                                    '6' => 'UK Geo',
                                    '7' => 'UK Non Geo'
                                ], ['prompt' => 'Select Service']); ?>
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="card">
                                <div class="card-header card-header-tabs card-header-primary">
                                    <div class="nav-tabs-navigation">
                                        <div class="nav-tabs-wrapper">
                                            <span class="nav-tabs-title">Upload Type:</span>
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
                                                        <!-- <?php //echo $form->field($model, 'start_number')->textInput(['maxlength' => true, 'required' => true])->label(false); 
                                                                ?>
                                                    <span class="bmd-help"><?= Html::activeHint($model, 'start_number'); ?></span> -->
                                                        <input type="text" name="start_number" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group bmd-form-group">
                                                        <label for="" class="bmd-label-floating">Number Qty:</label>
                                                        <!-- <?php //echo $form->field($model, 'number_qty')->textInput(['maxlength' => true])->label(false); 
                                                                ?>
                                                    <span class="bmd-help"><?= Html::activeHint($model, 'number_qty'); ?></span> -->
                                                        <input type="text" name="number_qty" class="form-control">
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="tab-pane" id="manual">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="form-group bmd-form-group">
                                                        <label for="" class="bmd-label-floating">Number List</label>
                                                        <!-- <?php //echo $form->field($model, 'number_list')->textarea(['rows' => '6']); 
                                                                ?>
                                                    <span class="bmd-help"><?= Html::activeHint($model, 'number_list'); ?></span> -->
                                                        <textarea name="number_list" rows="6" class="form-control"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="single">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="form-group bmd-form-group">
                                                        <label for="" class="bmd-label-floating">Single Number:</label>
                                                        <!-- <?php //echo $form->field($model, 'single_number')->textInput(['maxlength' => true])->label(false); 
                                                                ?>
                                                    <span class="bmd-help"><?= Html::activeHint($model, 'single_number'); ?></span> -->
                                                        <input type="text" name="single_number" class="form-control">
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
                    <?= Html::submitButton(\Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>