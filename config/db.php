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
      'dsn' => 'mysql:host=localhost;dbname=fsdbsms',
      'username' => 'root',
      'password' => 'P4ssword',
      'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
