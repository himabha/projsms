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
                    ['label' => 'Home', 'url' => ['/site/index']],
                    ['label' => 'Test Numbers', 'url' => ['/site/test-numbers']],
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
                            ['label' => 'Datewise', 'url' => ['/admin/date-report']],
                            ['label' => 'FsCall Report', 'url' => ['/admin/fs-call-report']],
                            ['label' => 'Agent wise Report', 'url' => ['/admin/agent-summary']],
                        ],
                    ],
                    ['label' => 'Import', 'url' => ['/admin/upload']],
                    ['label' => 'Add User',  
                    'url' => ['#'],
                    'items' => [
                        ['label' => 'Add User', 'url' => ['/admin/add-user']],
                        ['label' => 'List Users', 'url' => ['/admin/list-user']],
                    ],
                ],
                ['label' => 'Manage CLD',  
                'url' => ['#'],
                'items' => [
                    ['label' => 'Manage CLD', 'url' => ['/admin/add-cld']],
                    ['label' => 'Detach Number', 'url' => ['/admin/show-assigned']],
                ],
            ],

            ['label' => 'CDR', 'url' => ['/admin/cdr']],
            ['label' => 'Test Numbers', 'url' => ['/user/fs-test']],
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
                        ['label' => 'Dashboard', 'url' => ['/site/dashboard']],
                        ['label' => 'My CDR', 'url' => ['/user/cdr']],
                        ['label' => 'My Numbers', 'url' => ['/user/my-number']],
                        ['label' => 'Test Numbers', 'url' => ['/user/fs-test']],
                        
                        ['label' => 'Report',  
                        'url' => ['#'],
                        'items' => [
                            ['label' => 'FsCall Report', 'url' => ['/user/fs-call-report']],
                            ['label' => 'Country wise Report', 'url' => ['/user/country-summary']],
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
