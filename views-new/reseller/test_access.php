<?php
use app\models\User;
use app\models\FsaccessSearch;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

?>
<div class="row">
	<div class="col-sm-1">
	</div>
	<div class="col-sm-10">
		<h3>Access List Last 24 hours</h3>
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
					'id'=>'list_cld_tbl',
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
	<div class="col-sm-1">
	</div>
</div>

