<?php

use app\models\User;
use yii\helpers\Url;
?>
<div class="sidebar" data-color="purple" data-background-color="white" data-image="<?= \Yii::getAlias('@web/img/sidebar-1.jpg'); ?>">
    <!--
        Tip 1: You can change the color of the sidebar using: data-color="purple | azure | green | orange | danger"

        Tip 2: you can also add an image using data-image tag
    -->
    <div class="logo"><a href="<?= Url::to(['/']); ?>" class="simple-text logo-normal">
            SMS+ Panel</a>
    </div>
    <div class="sidebar-wrapper">
        <ul class="nav">
            <?php if (Yii::$app->user->isGuest) { ?>

                <li class="nav-item active">
                    <a class="nav-link" href="<?= Url::to(['/']); ?>">
                        <i class="material-icons">dashboard</i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= Url::to(['/site/test-active-calls']); ?>">
                        <i class="material-icons">content_paste</i>
                        <p>Test Acitve Calls</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= Url::to(['/site/test-cdr']); ?>">
                        <i class="material-icons">content_paste</i>
                        <p>Test CDR</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= Url::to(['/site/test-numbers']); ?>">
                        <i class="material-icons">library_books</i>
                        <p>Test Numbers</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= Url::to(['/site/access']); ?>">
                        <i class="material-icons">bubble_chart</i>
                        <p>Access</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= Url::to(['/site/login']); ?>">
                        <i class="material-icons">location_ons</i>
                        <p>Login</p>
                    </a>
                </li>
            <?php } else {
            ?>
                <li class="nav-item active">
                    <a class="nav-link" href="<?= Url::to(['/']); ?>">
                        <i class="material-icons">dashboard</i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <?php
                if (User::isResellerAdmin(Yii::$app->user->identity->id)) {
                ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/reseller-admin/billgroups']); ?>">
                            <i class="material-icons">content_paste</i>
                            <p>Bill Groups</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/reseller-admin/sms-numbers']); ?>">
                            <i class="material-icons">library_books</i>
                            <p>SMS Numbers</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/reseller-admin/sms-tdr']); ?>">
                            <i class="material-icons">receipt</i>
                            <p>SMS TDR</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#report" aria-expanded="true">
                            <i><img style="width:25px" src="<?= \Yii::getAlias('@web/img/yii-logo.svg'); ?>"></i>
                            <p>Reports
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse show" id="report">
                            <ul class="nav">
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= Url::to(['/reseller-admin/summary-report']); ?>">
                                        <i class="material-icons">summarize</i>
                                        <p>Summary Report</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= Url::to(['/reseller-admin/detailed-report']); ?>">
                                        <i class="material-icons">feed</i>
                                        <p>Detailed Report</p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/reseller-admin/list-reseller']); ?>">
                            <i class="material-icons">account_box</i>
                            <p>Resellers</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/reseller-admin/test-numbers']); ?>">
                            <i class="material-icons">location_ons</i>
                            <p>Test Numbers</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/reseller-admin/test-tdr']); ?>">
                            <i class="material-icons">location_ons</i>
                            <p>Test TDR</p>
                        </a>
                    </li>
                <?php } elseif (User::isReseller(Yii::$app->user->identity->id)) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/reseller/billgroups']); ?>">
                            <i class="material-icons">content_paste</i>
                            <p>Bill Groups</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/reseller/sms-numbers']); ?>">
                            <i class="material-icons">library_books</i>
                            <p>SMS Numbers</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/reseller/sms-tdr']); ?>">
                            <i class="material-icons">receipt</i>
                            <p>SMS TDR</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#report" aria-expanded="true">
                            <i><img style="width:25px" src="<?= \Yii::getAlias('@web/img/yii-logo.svg'); ?>"></i>
                            <p>Reports
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse show" id="report">
                            <ul class="nav">
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= Url::to(['/reseller/summary-report']); ?>">
                                        <i class="material-icons">summarize</i>
                                        <p>Summary Report</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= Url::to(['/reseller/detailed-report']); ?>">
                                        <i class="material-icons">feed</i>
                                        <p>Detailed Report</p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/reseller/list-agent']); ?>">
                            <i class="material-icons">account_box</i>
                            <p>Agents</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/reseller/test-numbers']); ?>">
                            <i class="material-icons">location_ons</i>
                            <p>Test Numbers</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/reseller/test-tdr']); ?>">
                            <i class="material-icons">location_ons</i>
                            <p>Test TDR</p>
                        </a>
                    </li>
                <?php } elseif (User::isUserAdmin(Yii::$app->user->identity->id)) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/admin/billgroups']); ?>">
                            <i class="material-icons">content_paste</i>
                            <p>Billgroups</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/admin/sms-numbers']); ?>">
                            <i class="material-icons">library_books</i>
                            <p>SMS Numbers</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/admin/sms-tdr']); ?>">
                            <i class="material-icons">receipt</i>
                            <p>SMS TDR</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#report" aria-expanded="true">
                            <i><img style="width:25px" src="<?= \Yii::getAlias('@web/img/yii-logo.svg'); ?>"></i>
                            <p>Reports
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse show" id="report">
                            <ul class="nav">
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= Url::to(['/admin/summary-report']); ?>">
                                        <i class="material-icons">summarize</i>
                                        <p>Summary Report</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= Url::to(['/admin/detailed-report']); ?>">
                                        <i class="material-icons">feed</i>
                                        <p>Detailed Report</p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/admin/list-reseller-admin']); ?>">
                            <i class="material-icons">account_box</i>
                            <p>Reseller Admin</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/admin/fs-test']); ?>">
                            <i class="material-icons">location_ons</i>
                            <p>Test Numbers</p>
                        </a>
                    </li>
                <?php } elseif (Yii::$app->user->identity->id == \Yii::$app->params['test_panel_id']) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/test-panel/test-numbers']); ?>">
                            <i class="material-icons">receipt</i>
                            <p>Test Numbers</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/test-panel/test-tdr']); ?>">
                            <i class="material-icons">receipt</i>
                            <p>Test TDR</p>
                        </a>
                    </li>
                <?php } else { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/user/billgroups']); ?>">
                            <i class="material-icons">content_paste</i>
                            <p>Bill Groups</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/user/sms-numbers']); ?>">
                            <i class="material-icons">receipt</i>
                            <p>SMS Numbers</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/user/sms-tdr']); ?>">
                            <i class="material-icons">receipt</i>
                            <p>SMS TDR</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#report" aria-expanded="true">
                            <i><img style="width:25px" src="<?= \Yii::getAlias('@web/img/yii-logo.svg'); ?>"></i>
                            <p>Reports
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse show" id="report">
                            <ul class="nav">
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= Url::to(['/user/summary-report']); ?>">
                                        <i class="material-icons">summarize</i>
                                        <p>Summary Report</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= Url::to(['/user/detailed-report']); ?>">
                                        <i class="material-icons">feed</i>
                                        <p>Detailed Report</p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/user/test-numbers']); ?>">
                            <i class="material-icons">location_ons</i>
                            <p>Test Numbers</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/user/test-tdr']); ?>">
                            <i class="material-icons">location_ons</i>
                            <p>Test TDR</p>
                        </a>
                    </li>
                <?php } ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= Url::to(['/site/access']); ?>">
                        <i class="material-icons">location_ons</i>
                        <p>Access</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#account" aria-expanded="true">
                        <i><img style="width:25px" src="<?= \Yii::getAlias('@web/img/yii-logo.svg'); ?>"></i>
                        <p>Account
                            <b class="caret"></b>
                        </p>
                    </a>
                    <div class="collapse show" id="account">
                        <ul class="nav">
                            <li class="nav-item">
                                <a class="nav-link" href="<?= Url::to(['/site/change-password']); ?>">
                                    <span class="sidebar-mini"> CP </span>
                                    <span class="sidebar-normal"> Change Password </span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= Url::to(['/site/logout']); ?>">
                                    <span class="sidebar-mini"> L </span>
                                    <span class="sidebar-normal"> Logout </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>