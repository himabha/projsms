<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 20;
?>
<div class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header card-header-primary">
						<h4 class="card-title ">Add CLD</h4>
					</div>
					<div class="card-body">
						<div class="row summary_outer">
							<div class="col-sm-4">
								<label>Stock :</label>
								<label> <?= $summary['stock'] ?></label>
							</div>
							<div class="col-sm-4">
								<label>Assigned CLDs :</label>
								<label> <?= $summary['assigned'] ?></label>
							</div>
							<div class="col-sm-4">
							</div>
						</div>
						<div class="row">
							<div class="col-sm-2 col-xs-12">
								<?= Html::a('Asign CLD to reseller', ['assign-cld-reseller'], ['class' => 'btn btn-success pull-left']) ?>
							</div>
							<div class="col-sm-10 col-xs-12">
								<?php
								$form = ActiveForm::begin(['id' => 'searchForm', 'method' => 'get']);
								?>
								<div class="pull_right-medium">
									<?= Html::textInput('search', $search, ['id' => 'search_box', 'class' => 'search_box', 'placeholder' => 'Search....']); ?>
									<?= Html::dropdownlist('filter', $filter, ['20' => '20', '50' => '50', '100' => '100', '1000' => '1000'], ['id' => 'filter_box', 'class' => 'filter_box']); ?>
								</div>
								<?php ActiveForm::end(); ?>
							</div>
						</div>
						<div class="table-responsive">
							<?= GridView::widget([
								'dataProvider' => $dataProvider,
								'tableOptions' => [
									'id' => 'list_cld_tbl',
									'class' => 'table'
								],
								'summary' => '',
								'pager' => [
									'firstPageLabel' => 'First',
									'lastPageLabel' => 'Last',
									'maxButtonCount' => '2',
								],
								'columns' => [

									'inboundip',
									'cld1',
									'cld2',
									'outboundip',
									'cld1description',
									'cld2description',
									'cld1rate',
									'cld2rate',
									// [
									// 	'header' => '<a href="Javascript::void(0);">User</a>',
									// 	'value' => 'cld.user.username'
									// ],
									[
										'class' => 'yii\grid\ActionColumn',
										'template' => '{show-number-routes} {update-cld} {delete-cld}',
										'buttons' => [
											'show-number-routes' => function ($url, $model, $key) {
												return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, [
													'class' => 'btn btn-info btn-xs',
													'data-toggle' => 'tooltip',
													'title' => 'Show list of all users who hold this number',
												]);
											},
											'update-cld' => function ($url, $model, $key) {
												return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, [
													'class' => 'btn btn-success btn-xs',
													'data-toggle' => 'tooltip',
													'title' => 'Edit'
												]);
											},
											'delete-cld' => function ($url, $model, $key) {
												return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
													'class' => 'btn btn-danger btn-xs',
													'data-pjax' => "0",
													'data-method' => 'post',
													'data-confirm' => 'Are you sure you want to delete CLD1?',
													'data-toggle' => 'tooltip',
													'title' => 'Delete'
												]);
											}
										],
									]

								],
							]); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$("#search_box").keyup(function() {
		if ($(this).val().length > 2 || !$(this).val().length) {
			$('#searchForm').submit();
		}
	});
	$(document).on('change', '#filter_box', function() {
		$('#searchForm').submit();
	})
</script>