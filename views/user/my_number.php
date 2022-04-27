<?php

use app\models\User;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

$get = isset($_GET['FsusertbSearch']) ? $_GET['FsusertbSearch'] : array();

?>


<br>


<div class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header card-header-primary">
						<h4 class="card-title ">My Numbers</h4>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="panel-box">
								<div class="border-box">
									<h3><?= Html::encode($this->title) ?></h3>
									<div class="col-sm-12 col-md-5 col-lg-3">
										<div class="dashboard-box one">
											<p>My stock</p>
											<?= $summary['mystock']; ?>
										</div>
									</div>
									<div class="col-sm-12 col-md-5 col-lg-3">

									</div>
									<div class="col-sm-12 col-md-5 col-lg-3">
									</div>
								</div>
							</div>
						</div>
						<div class="table-responsive">
							<?= GridView::widget([
								'dataProvider' => $dataProvider,
								'filterModel' => $searchModel,
								'tableOptions' => [
									'id' => 'list_cld_tbl',
									'class' => 'table'
								],
								'pager' => [
									'firstPageLabel' => 'First',
									'lastPageLabel' => 'Last',
									'maxButtonCount' => '2',
								],
								'summary' => '',
								'columns' => [
									[
										'attribute' => 'country',
										'header' => '<a href="Javascript::void(0);">Country</a>',
										'value' => 'master.cld1description',
									],
									'cld1'
								]
							]); ?>
						</div>

						<?php
						$form = ActiveForm::begin(['id' => 'pageNmbrForm', 'method' => 'get']);
						?>
						<?= Html::dropDownList('pg_nmbr', isset($_GET['pg_nmbr']) ? $_GET['pg_nmbr'] : '', [20 => 20, 50 => 50, 100 => 100, 1000 => 1000], ['id' => 'drpwn_pg_nmbr', 'class' => 'filter_box pull-right']); ?>
						<?php ActiveForm::end(); ?>
						<?php
						$form = ActiveForm::begin(['id' => 'nmbrExportForm', 'method' => 'get', 'action' => ['export-mynumber']]);
						?>
						<?= Html::hiddenInput('country', isset($get['country']) ? $get['country'] : '', ['id' => 'export_country']); ?>
						<?= Html::hiddenInput('cld1', isset($get['cld1']) ? $get['cld1'] : '', ['id' => 'export_cld1']); ?>

						<!--	<?= Html::hiddenInput('cld2_rate', isset($get['cld2_rate']) ? $get['cld2_rate'] : '', ['id' => 'export_cld2_rate']); ?>
-->
						<?= Html::submitButton('Export to Excel', ['class' => 'btn btn-success']) ?>
						<?php ActiveForm::end(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).on('change', '#drpwn_pg_nmbr', function() {
		$('#pageNmbrForm').submit();
	})
</script>