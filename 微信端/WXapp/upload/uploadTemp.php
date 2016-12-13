<?php

//定义服务器MySql相关信息
define("MYSQL_HOST","utfire.com.cn");
define("MYSQL_PORT","3306");
define("MYSQL_USER","root");
define("MYSQL_PASS","utfire");

if ($_POST['data'] && ($_POST['token'] == "wxData")) {//token，验证客户端是否合法
	$con = mysql_connect(MYSQL_HOST.':'.MYSQL_PORT,MYSQL_USER,MYSQL_PASS); 
	$data = $_POST['data'];
	mysql_select_db("app_991067661", $con);//要改成相应的数据库名
	mysql_query("SET NAMES 'utf8'");//注意一定要用双引号...
	
	
	//更新数据库温度湿度值
	$date = time();//获取时间
	$mysqltime=date('Y-m-d H:i:s',$date);
	$arr = json_decode($data,true);
	$sql ="UPDATE sensor SET timestamp='$mysqltime',temperature='$arr[temperature]',humidity='$arr[humidity]',place='$arr[place]' WHERE ID = '1'";//更新相应的传感器的值
	if(!mysql_query($sql,$con)){
	    die('Error: ' . mysql_error());//如果更新数据出错，显示错误
	}
	mysql_close($con);
}else{
	echo "Permission Denied";//请求中没有type或data或token或token错误时，显示Permission Denied
}
?>