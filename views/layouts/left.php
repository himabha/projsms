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
      V+ Panel</a>
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
          <!-- <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/reseller-admin/active-calls']); ?>">
              <i class="material-icons">content_paste</i>
              <p>Acitve Calls</p>
            </a>
          </li> -->
          <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/reseller-admin/billgroups']); ?>">
              <i class="material-icons">content_paste</i>
              <p>Bill Groups</p>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#reports" aria-expanded="true">
              <i><img style="width:25px" src="<?= \Yii::getAlias('@web/img/yii-logo.svg'); ?>"></i>
              <p>Reports
                <b class="caret"></b>
              </p>
            </a>
            <div class="collapse show" id="reports">
              <ul class="nav">
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/reseller-admin/date-report']); ?>">
                    <span class="sidebar-mini"> DS </span>
                    <span class="sidebar-normal"> Datewise Summary </span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/reseller-admin/fs-call-report']); ?>">
                    <span class="sidebar-mini"> RD </span>
                    <span class="sidebar-normal"> Resellerwise Detailed </span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/reseller-admin/reseller-summary']); ?>">
                    <span class="sidebar-mini"> RS </span>
                    <span class="sidebar-normal"> Resellerwise Summary </span>
                  </a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/reseller-admin/list-reseller']); ?>">
              <i class="material-icons">content_paste</i>
              <p>Resellers</p>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/reseller-admin/add-cld']); ?>">
              <i class="material-icons">library_books</i>
              <p>SMS Numbers</p>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/reseller-admin/cdr']); ?>">
              <i class="material-icons">bubble_chart</i>
              <p>SMS CDR</p>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/reseller-admin/fs-test']); ?>">
              <i class="material-icons">location_ons</i>
              <p>Test Numbers</p>
            </a>
          </li>
        <?php } elseif (User::isReseller(Yii::$app->user->identity->id)) { ?>
          <!-- <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/reseller/active-calls']); ?>">
              <i class="material-icons">content_paste</i>
              <p>Acitve Calls</p>
            </a>
          </li> -->
          <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/reseller/billgroups']); ?>">
              <i class="material-icons">content_paste</i>
              <p>Bill Groups</p>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#reports" aria-expanded="true">
              <i><img style="width:25px" src="<?= \Yii::getAlias('@web/img/yii-logo.svg'); ?>"></i>
              <p>Reports
                <b class="caret"></b>
              </p>
            </a>
            <div class="collapse show" id="reports">
              <ul class="nav">
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/reseller/date-report']); ?>">
                    <span class="sidebar-mini"> DS </span>
                    <span class="sidebar-normal"> Datewise Summary </span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/reseller/fs-call-report']); ?>">
                    <span class="sidebar-mini"> AD </span>
                    <span class="sidebar-normal"> Agentwise Detailed </span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/reseller/agent-summary']); ?>">
                    <span class="sidebar-mini"> AS </span>
                    <span class="sidebar-normal"> Agentwise Summary </span>
                  </a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/reseller/list-user']); ?>">
              <i class="material-icons">content_paste</i>
              <p>Agents</p>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/reseller/add-cld']); ?>">
              <i class="material-icons">library_books</i>
              <p>SMS Numbers</p>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/reseller/cdr']); ?>">
              <i class="material-icons">bubble_chart</i>
              <p>SMS CDR</p>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/reseller/fs-test']); ?>">
              <i class="material-icons">location_ons</i>
              <p>Test Numbers</p>
            </a>
          </li>
        <?php } elseif (User::isUserAdmin(Yii::$app->user->identity->id)) { ?>
          <!-- <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/admin/active-calls']); ?>">
              <i class="material-icons">content_paste</i>
              <p>Acitve Calls</p>
            </a>
          </li> -->
          <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/admin/billgroups']); ?>">
              <i class="material-icons">content_paste</i>
              <p>Bill Groups</p>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#reports" aria-expanded="true">
              <i><img style="width:25px" src="<?= \Yii::getAlias('@web/img/yii-logo.svg'); ?>"></i>
              <p>Reports
                <b class="caret"></b>
              </p>
            </a>
            <div class="collapse show" id="reports">
              <ul class="nav">
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/admin/date-report']); ?>">
                    <span class="sidebar-mini"> DR </span>
                    <span class="sidebar-normal"> Datewise Report </span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/admin/fs-call-report']); ?>">
                    <span class="sidebar-mini"> SR </span>
                    <span class="sidebar-normal"> Summary Report </span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/admin/agent-summary']); ?>">
                    <span class="sidebar-mini"> AR </span>
                    <span class="sidebar-normal"> Agentwise Report </span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/admin/reseller-summary']); ?>">
                    <span class="sidebar-mini"> RR </span>
                    <span class="sidebar-normal"> Resellerwise Report </span>
                  </a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/admin/upload']); ?>">
              <i class="material-icons">content_paste</i>
              <p>Import</p>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#reseller" aria-expanded="true">
              <i><img style="width:25px" src="<?= \Yii::getAlias('@web/img/yii-logo.svg'); ?>"></i>
              <p>Reseller
                <b class="caret"></b>
              </p>
            </a>
            <div class="collapse show" id="reseller">
              <ul class="nav">
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/admin/add-reseller']); ?>">
                    <span class="sidebar-mini"> AR </span>
                    <span class="sidebar-normal">Add Reseller</span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/admin/list-reseller']); ?>">
                    <span class="sidebar-mini"> LR </span>
                    <span class="sidebar-normal">List Reseller</span>
                  </a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#resellerAdmin" aria-expanded="true">
              <i><img style="width:25px" src="<?= \Yii::getAlias('@web/img/yii-logo.svg'); ?>"></i>
              <p>Reseller Admin
                <b class="caret"></b>
              </p>
            </a>
            <div class="collapse show" id="resellerAdmin">
              <ul class="nav">
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/admin/add-reseller-admin']); ?>">
                    <span class="sidebar-mini"> ARD </span>
                    <span class="sidebar-normal">Add Reseller Admin</span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/admin/list-reseller-admin']); ?>">
                    <span class="sidebar-mini"> LRD </span>
                    <span class="sidebar-normal">List Reseller Admin</span>
                  </a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#manageDDI" aria-expanded="true">
              <i><img style="width:25px" src="<?= \Yii::getAlias('@web/img/yii-logo.svg'); ?>"></i>
              <p>SMS Numbers
                <b class="caret"></b>
              </p>
            </a>
            <div class="collapse show" id="manageDDI">
              <ul class="nav">
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/admin/add-cld']); ?>">
                    <span class="sidebar-mini"> DS </span>
                    <span class="sidebar-normal">SMS Numbers</span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/admin/show-assigned-reseller-admin']); ?>">
                    <span class="sidebar-mini"> RD </span>
                    <span class="sidebar-normal"> Detach Number - Reseller Admin </span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/admin/show-assigned-reseller']); ?>">
                    <span class="sidebar-mini"> RD </span>
                    <span class="sidebar-normal"> Detach Number - Reseller </span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/admin/show-assigned']); ?>">
                    <span class="sidebar-mini"> RD </span>
                    <span class="sidebar-normal"> Detach Number - User </span>
                  </a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/admin/cdr']); ?>">
              <i class="material-icons">bubble_chart</i>
              <p>SMS CDR</p>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/admin/fs-test']); ?>">
              <i class="material-icons">location_ons</i>
              <p>Test Numbers</p>
            </a>
          </li>
        <?php } else { ?>
          <!-- <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/user/active-calls']); ?>">
              <i class="material-icons">content_paste</i>
              <p>Acitve Calls</p>
            </a>
          </li> -->
          <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/user/billgroups']); ?>">
              <i class="material-icons">content_paste</i>
              <p>Bill Groups</p>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/user/cdr']); ?>">
              <i class="material-icons">location_ons</i>
              <p>My CDRs</p>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/user/my-number']); ?>">
              <i class="material-icons">location_ons</i>
              <p>My Numbers</p>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= Url::to(['/user/fs-test']); ?>">
              <i class="material-icons">location_ons</i>
              <p>Test Numbers</p>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#reports" aria-expanded="true">
              <i><img style="width:25px" src="<?= \Yii::getAlias('@web/img/yii-logo.svg'); ?>"></i>
              <p>Reports
                <b class="caret"></b>
              </p>
            </a>
            <div class="collapse show" id="reports">
              <ul class="nav">
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/user/fs-call-report']); ?>">
                    <span class="sidebar-mini"> TS </span>
                    <span class="sidebar-normal"> Traffic Summary </span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="<?= Url::to(['/user/country-summary']); ?>">
                    <span class="sidebar-mini"> TC </span>
                    <span class="sidebar-normal"> Traffic Countrywise </span>
                  </a>
                </li>
              </ul>
            </div>
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