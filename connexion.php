<?php 

$serveur = 'localhost';
$user = 'root';
$pass = 'eclipse';
$db = mysql_connect($serveur, $user, $pass); 
mysql_query("SET NAMES UTF8");
mysql_select_db('portail',$db); 

$serveur_sql="10.106.76.111";
$username="sa";
$password="Logiwin06";
$base_wp = "winpneu";
// $sqlconnect=mssql_connect($serveur_sql, $username, $password);	//CODE
// $sqldb=mssql_select_db($base_wp,$sqlconnect);	//CODE
?>
