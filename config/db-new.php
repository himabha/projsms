<?php

return [
	//local
    // 'class' => 'yii\db\Connection',
    // 'dsn' => 'mysql:host=localhost;dbname=freeswitch',
    // 'username' => 'root',
    // 'password' => '',
    // 'charset' => 'utf8',

//server

    'class' => 'yii\db\Connection',
//    'dsn' => 'mysql:host=localhost;dbname=fsdb',
    'dsn' => 'mysql:host=149.36.7.55;dbname=fsdb',
    'username' => 'FSDBUsr',
    'password' => 'Q^g4pu2$fXX75p',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
