<?php
use app\models\User;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

?>
<div class="row">
	<div class="col-sm-1">
	</div>
	<div class="col-sm-10">
		<h3>Test Numbers List</h3>
		<?php
		if (!User::isUserAdmin(Yii::$app->user->identity->id)) {
			?>
			<table align="center" class="testnmbr_tbl">
                                <tr>
 					<td colspan="3">
                                                <a href="http://portal.vpluspanel.com/index.php?r=site%2Flogin"> <strong> TEST ACTIVE CALLS LINK </strong> </a>
					</td>
				</tr>
                                <tr>
                                    <td colspan="3"><strong>Please login with below </strong>:</td>
                                </tr>

				<tr>
					<th>Username</th>
					<td>:</td>
					<td>tsagnt</td>
				</tr>
<!--				<tr>
					<th>Account</th>
					<td>:</td>
					<td>ddigwtst</td>
				</tr>
-->
				<tr>
					<th>Password</th>
					<td>:</td>
					<td>testddi43$</td>
				</tr>
			</table>
			<?php
		}
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
					'maxButtonCount' => '2',
				],
				'showFooter' => true,
				'summary' => '',
				'columns' => [
					'Country',
					'Test_Number',
					'Rate',
					'Number_Range'
				],
			]); ?>
		</div>
		<?php Pjax::end() ?>
	</div>
	<div class="col-sm-1">
	</div>
</div>


