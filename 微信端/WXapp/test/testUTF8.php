<?php

//定义服务器MySql相关信息
define("MYSQL_HOST","utfire.com.cn");
define("MYSQL_PORT","3306");
define("MYSQL_USER","root");
define("MYSQL_PASS","utfire");

$con = mysql_connect(MYSQL_HOST.':'.MYSQL_PORT,MYSQL_USER,MYSQL_PASS); 
mysql_select_db("app_991067661", $con);//修改数据库名
mysql_query("SET NAMES 'utf8'");
$result = mysql_query("SELECT * FROM sensor");
while($arr = mysql_fetch_array($result)){
  if ($arr['ID'] == 1) {//只用一条数据，保存当时的温度
	$tempr = $arr["temperature"];
	$humidity = $arr["humidity"];
	$where = $arr["where"];
	$time = $arr["timestamp"];
  }
}
mysql_close($con);
header ( "Content-Type: text/html; charset=UTF-8" );
	echo $where;
	echo "--------------------";
	echo $humidity;
?>