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
                    <?= $model->isNewRecord ? \Yii::t('app', 'Create Billgroup') : \Yii::t('app', 'Update Billgroup') ?>
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
                    <div class="col-sm-6">
                        <div class="form-group bmd-form-group">
                            <label for="<?= Html::getInputId($model, 'name'); ?>" class="bmd-label-floating"><?= Html::activeLabel($model, 'name'); ?></label>
                            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'required' => true])->label(false); ?>
                            <span class="bmd-help"><?= Html::activeHint($model, 'name'); ?></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group bmd-form-group">
                            <?php echo $form->field($model, 'country_id')->dropDownList(
                                ArrayHelper::map($countries, 'ID', 'Country'),
                                ['prompt' => 'Select Country']
                            ); ?>
                            <span class="bmd-help"><?= Html::activeHint($model, 'country_id'); ?></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group bmd-form-group">
                            <?php echo $form->field($model, 'currency_id')->dropDownList([
                                '1' => 'GBP', 
                                '2' => 'EUR', 
                                '3' => 'USD'
                                ], ['prompt' => 'Select Currency']); ?>
                            <span class="bmd-help"><?= Html::activeHint($model, 'currency'); ?></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group bmd-form-group">
                            <?php echo $form->field($model, 'billcycle_id')->dropDownList([
                                '1' => '15/15',
                                '2' => '15/30',
                                '3' => '30/30',
                                '4' => '30/35',
                                '5' => '30/45',
                                '6' => '30/50',
                                '7' => '30/55',
                                '8' => '30/60',
                                '9' => '30/65',
                                '10' => '30/70',
                                '11' => '45 Days',
                                '12' => '90 Days',
                                '13' => 'Daily 1/1',
                                '14' => 'Weekly 1/1',
                                '15' => 'Weekly 7/7'
                            ], ['prompt' => 'Select Terms']); ?>
                            <span class="bmd-help"><?= Html::activeHint($model, 'payment_terms'); ?></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group bmd-form-group">
                            <?php echo $form->field($model, 'service')->dropDownList([
                                '1' => 'HYBRID',
                                '2' => 'PPC',
                                '3' => 'PPM',
                                '4' => 'PRN',
                                '5' => 'SMS',
                                '6' => 'UK Geo',
                                '7' => 'UK Non Geo'
                            ], ['prompt' => 'Select Number']); ?>
                            <span class="bmd-help"><?= Html::activeHint($model, 'service'); ?></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group bmd-form-group">
                            <label for="<?= Html::getInputId($model, 'selfallocation'); ?>" class="bmd-label-floating"><?= Html::activeLabel($model, 'selfallocation'); ?></label>
                            <?= $form->field($model, 'selfallocation')->textInput(['maxlength' => true])->label(false); ?>
                            <span class="bmd-help"><?= Html::activeHint($model, 'selfallocation'); ?></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group bmd-form-group">
                            <label for="<?= Html::getInputId($model, 'maxperday'); ?>" class="bmd-label-floating"><?= Html::activeLabel($model, 'maxperday'); ?></label>
                            <?= $form->field($model, 'maxperday')->textinput(['maxlength' => true])->label(false); ?>
                            <span class="bmd-help"><?= Html::activeHint($model, 'maxperday'); ?></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group bmd-form-group">
                            <?php echo $form->field($model, 'sender_id')->dropDownList(
                                ArrayHelper::map($suppliers, 'id', 'name'),
                                ['prompt' => 'Select Supplier']
                            ); ?>
                            <span class="bmd-help"><?= Html::activeHint($model, 'sender_id'); ?></span>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group bmd-form-group">
                            <label for="<?= Html::getInputId($model, 'notes'); ?>" class="bmd-label-floating"><?= Html::activeLabel($model, 'notes'); ?></label>
                            <?= $form->field($model, 'notes')->textarea(['rows' => '6']); ?>
                            <span class="bmd-help"><?= Html::activeHint($model, 'notes'); ?></span>
                        </div>
                    </div>
                </div>

            </div>
            <div class="card-footer ml-auto mr-auto">
                <?= Html::submitButton($model->isNewRecord ? \Yii::t('app', 'Create') : \Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>