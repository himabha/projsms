<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
?>
<div class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header card-header-primary">
						<h4 class="card-title ">Resellers List</h4>
						<!-- <p class="card-category"> Here is a subtitle for this table</p> -->
					</div>
					<div class="card-body">
						<?= Html::beginForm(['reseller/list-user'], 'get') ?>
						<div class="row">
							<div class="col-sm-3">
								<?= Html::textinput('name', $name, ['class' => 'form-control', 'placeholder' => 'Search Name']) ?>
							</div>
							<div class="col-sm-4">
								<?= Html::submitButton('Filter', ['class' => 'btn btn-success']) ?>
								<?= Html::a('Reset', ['list-user'], ['class' => 'btn btn-primary']); ?>
							</div>
							<div class="col-sm-3 col-xs-6">
								<?= Html::a('ADD Reseller', ['add-reseller'], ['class' => 'btn btn-success pull-left']) ?>
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
									'id',
									'username',
									'email',
									'lastlogin',

									[
										'class' => 'yii\grid\ActionColumn',
										'template' => '{edit-user} {delete-user}',
										'buttons' => [
											'edit-user' => function ($url, $model, $key) {
												return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, [
													'class' => 'btn btn-success btn-xs',
													'data-toggle' => 'tooltip',
													'title' => 'Edit'
												]);
											},
											/*'delete-user' => function ($url, $model, $key) {
							return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
								'class' => 'btn btn-danger btn-xs',
								'data-pjax' => "0",
								'data-method' => 'post',
								'data-confirm' => 'Are you sure you want to delete User?',
								'data-toggle' => 'tooltip',
								'title' => 'Delete'
							]);
						}*/
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