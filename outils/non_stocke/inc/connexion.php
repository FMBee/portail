<?php 

    $serveur    = 'localhost';
    $user       = 'root';
    $pass       = 'eclipse';
//     $user = 'portail';
//     $pass = 'eclipse@9';
    $db         = 'commandedimension';
    
    $mysqldb = new PDO( "mysql:dbname={$db};host={$serveur}", $user, $pass );
 
    
    
    $serveur_sql = "10.106.76.111";
    $port       = '1433';
    $username   = "sa";
    $password   = "Logiwin06";
    $base_wp    = "winpneu";
    
    $os = strtoupper(substr(PHP_OS, 0, 3));
    
    switch( $os ) {
        
        case 'WIN':
            
            $sqldb = new PDO( "sqlsrv:Server={$serveur_sql},{$port};Database={$base_wp}", $username, $password );
            break;
            
        case 'LIN':
            
            $sqldb = new PDO( "dblib:host={$serveur_sql}:{$port};dbname={$base_wp};charset=utf8", $username, $password );
            break;
    }
