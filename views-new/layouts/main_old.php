<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\models\User;
use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assetBundle\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <script type="text/javascript">var baseurl = '<?php echo Yii::$app->homeUrl; ?>';</script>
    <?php $this->head() ?>
</head>
<body>
    <?php $this->beginBody() ?>

    <div class="wrap">
        <?php
        NavBar::begin([
            'brandLabel' => 'V+ Panel',
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar-inverse navbar-fixed-top',
            ],
        ]);
        if (Yii::$app->user->isGuest) {
            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => [
                    ['label' => 'Test Acitve Calls', 'url' => ['/site/index']],
                    ['label' => 'Test CDR', 'url' => ['/site/test-cdr']],
                    ['label' => 'Test Numbers', 'url' => ['/site/test-numbers']],
                    ['label' => 'Access', 'url' => ['/site/access']],
                    ['label' => 'Login', 'url' => ['/site/login']],

                ],
            ]);
        } else {
            if (User::isUserAdmin(Yii::$app->user->identity->id)) {
                echo Nav::widget([
                    'options' => ['class' => 'navbar-nav navbar-right'],
                    'items' => [
                    ['label' => 'Active Calls', 'url' => ['/site/dashboard']],
                    ['label' => 'Report',
                    'url' => ['#'],
                    'items' => [
                    ['label' => 'Datewise Report', 'url' => ['/admin/date-report']],
                    ['label' => 'Summary Report', 'url' => ['/admin/fs-call-report']],
                    ['label' => 'Agentwise Report', 'url' => ['/admin/agent-summary']],
                    ['label' => 'Resellerwise Report', 'url' => ['/admin/reseller-summary']],
                    ],
                    ],
                    ['label' => 'Import', 'url' => ['/admin/upload']],
                    ['label' => 'Add User',
                    'url' => ['#'],
                    'items' => [
                    ['label' => 'Add User', 'url' => ['/admin/add-user']],
                    ['label' => 'List Users', 'url' => ['/admin/list-user']],
                    ['label' => 'Add Reseller', 'url' => ['/admin/add-reseller']],
                    ['label' => 'List Resellers', 'url' => ['/admin/list-reseller']],
                    ['label' => 'Add Reseller Admin', 'url' => ['/admin/add-reseller-admin']],
                    ['label' => 'List Reseller Admin', 'url' => ['/admin/list-reseller-admin']]
                    ],
                    ],
                    ['label' => 'Manage DDI', 'url' => ['/admin/add-cld'],
                     'items' => [
                       ['label' => 'Manage DDI', 'url' => ['/admin/add-cld']],
                       ['label' => 'Detach Number - Reseller Admin', 'url' => ['/admin/show-assigned-reseller-admin']],
                       //['label' => 'Manage CLD - Reseller', 'url' => ['/admin/add-cld-reseller']],
                       ['label' => 'Detach Number - Reseller', 'url' => ['/admin/show-assigned-reseller']],
                       //['label' => 'Manage DDI', 'url' => ['/admin/add-cld']],
                       ['label' => 'Detach Number - User', 'url' => ['/admin/show-assigned']],
                     ],
                   ],

                    ['label' => 'CDR', 'url' => ['/admin/cdr']],
                    ['label' => 'Test Numbers', 'url' => ['/admin/fs-test']],
                    ['label' => 'Account (' . Yii::$app->user->identity->username . ')',
                    'url' => ['#'],
                    'items' => [
                    ['label' => 'Change Password', 'url' => ['/site/change-password']],
                    '<li>'
                    . Html::beginForm(['/site/logout'], 'post')
                    . Html::submitButton(
                        'Logout (' . Yii::$app->user->identity->username . ')',
                        ['class' => 'btn btn-link logout']
                        )
                    . Html::endForm()
                    . '</li>'
                    ],
                    ],

                    ],
                    ]);
            } elseif (User::isReseller(Yii::$app->user->identity->id)) {
                echo Nav::widget([
                    'options' => ['class' => 'navbar-nav navbar-right'],
                    'items' => [
                    ['label' => 'Active Calls', 'url' => ['/site/dashboard']],
                    ['label' => 'Report',
                    'url' => ['#'],
                    'items' => [
                    ['label' => 'Datewise Summary', 'url' => ['/reseller/date-report']],
                    ['label' => 'Agentwise Detailed', 'url' => ['/reseller/fs-call-report']],
                    ['label' => 'Agentwise Summary', 'url' => ['/reseller/agent-summary']],
                    ],
                    ],
                    ['label' => 'Add User',
                    'url' => ['#'],
                    'items' => [
                    ['label' => 'Add User', 'url' => ['/reseller/add-user']],
                    ['label' => 'List Users', 'url' => ['/reseller/list-user']],
                    ],
                    ],
                    ['label' => 'Manage DDI', 'url' => ['/reseller/add-cld']],
/*                    'url' => ['#'],
                    'items' => [
                    ['label' => 'Manage DDI - User', 'url' => ['/reseller/add-cld']],
                    ['label' => 'Detach Number', 'url' => ['/reseller/show-assigned']],
                    ],
                    ],
*/
                    ['label' => 'CDR', 'url' => ['/reseller/cdr']],
                    ['label' => 'Test Numbers', 'url' => ['/reseller/fs-test']],
                    ['label' => 'Access', 'url' => ['/site/access']],
                    ['label' => 'Account (' . Yii::$app->user->identity->username . ')',
                    'url' => ['#'],
                    'items' => [
                    ['label' => 'Change Password', 'url' => ['/site/change-password']],
                    '<li>'
                    . Html::beginForm(['/site/logout'], 'post')
                    . Html::submitButton(
                        'Logout (' . Yii::$app->user->identity->username . ')',
                        ['class' => 'btn btn-link logout']
                        )
                    . Html::endForm()
                    . '</li>'
                    ],
                    ],

                    ],
                    ]);
            }elseif (User::isResellerAdmin(Yii::$app->user->identity->id)) {
                echo Nav::widget([
                  'options' => ['class' => 'navbar-nav navbar-right'],
                  'items' => [
                  ['label' => 'Active Calls', 'url' => ['/site/dashboard']],
                  ['label' => 'Report',
                  'url' => ['#'],
                  'items' => [
                  ['label' => 'Datewise Summary', 'url' => ['/reseller-admin/date-report']],
                  ['label' => 'Resellerwise Detailed', 'url' => ['/reseller-admin/fs-call-report']],
                  ['label' => 'Resellerwise Summary', 'url' => ['/reseller-admin/reseller-summary']],
                  ],
                  ],
                  ['label' => 'Add Reseller',
                  'url' => ['#'],
                  'items' => [
                  ['label' => 'Add Reseller', 'url' => ['/reseller-admin/add-reseller']],
                  ['label' => 'List Resellers', 'url' => ['/reseller-admin/list-reseller']],
                  ],
                  ],
                  ['label' => 'Manage DDI', 'url' => ['/reseller-admin/add-cld']],
                  /*                    'url' => ['#'],
                  'items' => [
                  ['label' => 'Manage DDI - User', 'url' => ['/reseller-admin/add-cld']],
                  ['label' => 'Detach Number', 'url' => ['/reseller-admin/show-assigned']],
                  ],
                  ],
                  */
                  ['label' => 'CDR', 'url' => ['/reseller-admin/cdr']],
                  ['label' => 'Test Numbers', 'url' => ['/reseller-admin/fs-test']],
                  ['label' => 'Access', 'url' => ['/site/access']],
                  ['label' => 'Account (' . Yii::$app->user->identity->username . ')',
                  'url' => ['#'],
                  'items' => [
                  ['label' => 'Change Password', 'url' => ['/site/change-password']],
                  '<li>'
                  . Html::beginForm(['/site/logout'], 'post')
                  . Html::submitButton(
                      'Logout (' . Yii::$app->user->identity->username . ')',
                      ['class' => 'btn btn-link logout']
                      )
                  . Html::endForm()
                  . '</li>'
                  ],
                  ],

                  ],
                  ]);
            } else {
                echo Nav::widget([
                    'options' => ['class' => 'navbar-nav navbar-right'],
                    'items' => [
                        ['label' => 'Active Calls', 'url' => ['/site/dashboard']],
                        ['label' => 'My CDRs', 'url' => ['/user/cdr']],
                        ['label' => 'My Numbers', 'url' => ['/user/my-number']],
                        ['label' => 'Test Numbers', 'url' => ['/user/fs-test']],

                        ['label' => 'Reports',
                        'url' => ['#'],
                        'items' => [
                            ['label' => 'Traffic Summary', 'url' => ['/user/fs-call-report']],
                            ['label' => 'Traffic Countrywise', 'url' => ['/user/country-summary']],
                        ],
                    ],
                        ['label' => 'Account (' . Yii::$app->user->identity->username . ')',
                        'url' => ['#'],
                        'items' => [
                            ['label' => 'Change Password', 'url' => ['/site/change-password']],
                            '<li>'
                            . Html::beginForm(['/site/logout'], 'post')
                            . Html::submitButton(
                                'Logout (' . Yii::$app->user->identity->username . ')',
                                ['class' => 'btn btn-link logout']
                            )
                            . Html::endForm()
                            . '</li>'
                        ],
                    ],
                ],
            ]);
            }

        }

        NavBar::end();
        ?>

        <div class="container">
            <?= $content ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">

        </div>
    </footer>

    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
