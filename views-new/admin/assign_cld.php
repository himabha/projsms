<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;

?>
<h3>Assign CLD to user</h3>
<?php if (Yii::$app->session->hasFlash('cld_added')): ?>
	<div class="alert alert-success alert-dismissable">
		<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
		<h4 class="flash-message"><i class="icon fa fa-check"></i> <?=Yii::$app->session->getFlash('cld_added')?></h4>
	</div>
<?php endif;?>
<div class="row">
	<div class="col-sm-12">

		<div class="box-outer">
			<div class="row">
				<div class="col-sm-1">
				</div>
				<div class="col-sm-4 summary_box">
					<label>Available :</label>
					<label> <?= $summary['available'] ?></label>
				</div>
				<div class="col-sm-2">
				</div>
				<div class="col-sm-4 summary_box">
					<label>Assigned CIDs :</label>
					<label> <?= $summary['assigned'] ?></label>
				</div>
				<div class="col-sm-1">
				</div>
			</div>
            <div id="reseller_nam_outr" class="form-group">
				<label class="control-label">Reseller</label>
				<?= Html::dropDownList('reseller_id','',$resellers,['id' => 'reseller_id','prompt' => '---select---' ,'class' => 'form-control', 'onclick' => "getUsersByReseller(this)"]); ?>
				<div class="help-block reseller_hlpblk"></div>
			</div>

            <div id="user_nam_outr" class="form-group">
				<label class="control-label">User</label>
				<?= Html::dropDownList('user_id','',[],['disabled'=> true, 'id' => 'user_id','prompt' => '---select---' ,'class' => 'form-control']); ?>
				<div class="help-block user_hlpblk"></div>
			</div>
            <div class="help-block numbr-error_msg"></div>
            <button id="assign_nmbr" class="btn btn-primary">Submit</button>
        	<a href="<?= Url::toRoute(['admin/add-cld']) ?>" class="btn btn-default pull-right">Back</a>
            <div class="grid_holdr">
				<?php $form = ActiveForm::begin(['method' => 'get','action' => ['assign-cld']]); ?>
				<div class="row">
					<div class="col-sm-5">
						<?= Html::textInput('search',$search,['class' => 'form-control','placeholder' => 'search']); ?>
					</div>
					<div class="col-sm-3">
						<?= Html::dropDownList('limit',$limit,[20 => 20,50=>50,100=>100],['id' => 'limit','prompt' => 'Limit' ,'class' => 'form-control']); ?>
					</div>
					<div class="col-sm-4">
						<button class="btn btn-primary">Search</button>
					</div>
				</div>
				<?php ActiveForm::end(); ?>


				<hr>

				<?= GridView::widget([
					'id' => 'asign_nmbr_grd',
					'dataProvider' => $dataProvider,
					//'summary' => '',
					'tableOptions' => [
						'class' => 'numbr_tbl',
					],
					'columns' => [
						[
							'class' => 'yii\grid\CheckboxColumn',
							'checkboxOptions' => function($model, $key, $index, $widget) {

								return ["value" => $model->cld1];

							}

						],
						[
							'label' => 'Number',
							'attribute' => 'cld1',
						],

					],
				]); ?>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).on('click','#assign_nmbr',function () {
		var numbers = $('#asign_nmbr_grd').yiiGridView('getSelectedRows');
		var reseller = $('#reseller_id').val();
        var user = $('#user_id').val();
		var flag = true;
		if(numbers ==""){
			flag = false;
			$('.numbr-error_msg').text('Please select atleast one number from below list');
		} else {
			$('.numbr-error_msg').text('');
		}
        if(reseller ==""){
			flag = false;
			$('.reseller_hlpblk').text('Reseller cannot be empty');
			$('#reseller_nam_outr').addClass('has-error');
		}
        if(user ==""){
			flag = false;
			$('.user_hlpblk').text('User cannot be empty');
			$('#user_nam_outr').addClass('has-error');
		} else {
			$('.user_hlpblk, .reseller_hlpblk').text('');
			$('#user_nam_outr, #reseller_nam_outr').removeClass('has-error');
		}
		if (flag) {
			$(this).attr("disabled", true);
			var strvalue = "";
			$('input[name="selection[]"]:checked').each(function() {
				if(strvalue!="")
					strvalue = strvalue + ","+this.value;
				else
					strvalue = this.value;
			});
			$.ajax({
				url: baseurl + '?r=admin/assign-number',
				type: 'post',
				data: {user : user,reseller: reseller, numbers:strvalue},
				success: function (response) {
					location.reload();
				}
			});
		}
	});

    function getUsersByReseller(elem){
        if(elem.value != ''){
            updategridNumbers();
            $.ajax({
                url: baseurl + '?r=admin/get-users-by-reseller',
                type: 'post',
                data: {resellerId: elem.value},
                accept: 'application/json',
                success: function (response) {
                    var result = JSON.parse(response);
                    if(Object.keys(result).length > 0){
                        $("#user_id").removeAttr("disabled");
                        var option = "<option value>---select---</option>";
                        var obj = Object.keys(result);
                        $.each(obj, function(index){
                            var value = obj[index];
                            option += "<option value='"+value+"'>"+result[value]+"</option>";
                        });
                        $("#user_id").html(option);
                    }
                    else{
                        var option = "<option value>---select---</option>";
                        $("#user_id").attr("disabled", true);
                        $("#user_id").html(option);
                    }
                },
                error: function(err){
                    var option = "<option value>---select---</option>";
                    $("#user_id").attr("disabled", true);
                    $("#user_id").html(option);
                }
            });
        }
        else{
            var option = "<option value>---select---</option>";
            $("#user_id").attr("disabled", true);
            $("#user_id").html(option);
        }
    }

    function updategridNumbers()
    {
        var reseller_val = $("#reseller_id").val();
        if(reseller_val != ''){
            $.ajax({
                url: baseurl + '?r=admin/get-numbers-by-reseller',
                type: 'post',
                data: {resellerId: reseller_val},
                accept: 'application/json',
                success: function (response) {
                    $(".numbr_tbl tr").each(function(index){
                        console.log(index);
                    })
                    console.log(response);
                },
                error: function(err){
                }
            });
        }
    }
</script>
