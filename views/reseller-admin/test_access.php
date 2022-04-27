<?php

use app\models\User;
use app\models\FsaccessSearch;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

?>
<div class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header card-header-primary">
						<h4 class="card-title ">Access List Last 24 hours</h4>
						<!-- <p class="card-category"> Here is a subtitle for this table</p> -->
					</div>
					<div class="card-body">
						<?php
						/*		if (!User::isUserAdmin(Yii::$app->user->identity->id)) {
			?>
			<table align="center" class="testaccess_tbl">
			</table>
			<?php
		}
*/
						Pjax::begin(['id' => 'pjax_cdr_list']);
						?>
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
									'maxButtonCount' => '5',
								],
								'showFooter' => true,
								'summary' => '',
								'columns' => [
									'caller_number',
									'called_number',
									'caller_origination',
									'called_destination'
								],
							]); ?>
						</div>
						<?php Pjax::end() ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>