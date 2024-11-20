<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            //'dsn' => 'dblib:host=172.17.1.30:1433;dbname=SiacPersonal',
            //'dsn' => 'sqlsrv:Server=172.17.1.30;Database=SiacPersonal',
            //'dsn' => 'sqlsrv:Server=172.16.1.250;Database=SiacPersonal;Encrypt=0;TrustServerCertificate=1',
            'dsn' => 'sqlsrv:Server=172.16.1.250;Database=RecursosHumanosPrueba;Encrypt=0;TrustServerCertificate=1',
            //'username' => 'usrwebrrhh',
            //'password' => 'r3c%r505',
            //'username' => 'dticgerzon',
            //'password' => 'gbc437966',
            //'username' => 'urswebsiac',
            //'password' => '6#XomJo%QElZ',
            'username' => 'sa',
            'password' => 'sapo',
            'charset' => 'utf8',
        ],
        'dbAcad' => [
            'class' => 'yii\db\Connection',
            //'dsn' => 'dblib:host=172.17.1.20:1433;dbname=Academica',
            //'dsn' => 'sqlsrv:Server=172.17.1.20;Database=Academica',
            'dsn' => 'sqlsrv:Server=172.16.1.250;Database=Academica;Encrypt=0;TrustServerCertificate=1',
            //'username' => 'usrwebrrhh',
            //'password' => 'r3c%r505',
            //'username' => 'dticgerzon',
            //'password' => 'gbc437966',
            //'username' => 'urswebsiac',
            //'password' => '6#XomJo%QElZ',
            'username' => 'sa',
            'password' => 'sapo',
            'charset' => 'utf8',
        ],
        'dbDecJu' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlsrv:Server=172.17.1.30;Database=DeclaracionJuradaRRHH;Encrypt=0;TrustServerCertificate=1',
            //'dsn' => 'dblib:host=172.17.1.30:1433;dbname=SECGRAL',
            'username' => 'urswebsiac',
            'password' => '6#XomJo%QElZ',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
    ],
];
