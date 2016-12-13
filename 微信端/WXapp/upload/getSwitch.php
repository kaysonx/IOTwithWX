<?php

//定义服务器MySql相关信息
define("MYSQL_HOST","utfire.com.cn");
define("MYSQL_PORT","3306");
define("MYSQL_USER","root");
define("MYSQL_PASS","utfire");
define("MYSQL_DBNAME","app_991067661");


if ($_POST['token'] == "wxData") {//token，验证客户端是否合法
	/*
	$con = mysql_connect(MYSQL_HOST.':'.MYSQL_PORT,MYSQL_USER,MYSQL_PASS); 
	mysql_select_db(MYSQL_DBNAME, $con);//要改成相应的数据库名
	
	//读取灯光状态
	$result = mysql_query("SELECT * FROM switch");
	while($arr = mysql_fetch_array($result)){//找到需要的数据的记录，并读出状态值
		if ($arr['ID'] == 1) {
			$state = $arr['state'];
		}
	}
	
	mysql_close($con);
	*/
	$memcache = new Memcache;
	$memcache->connect('127.0.0.1',11211) or die('connect error!');
	$state = $memcache->get('switch');
	if ($state == "" || $state == null){
		$state = "0";
	}
	
	//返回灯光状态值，加“{”是为了帮助客户端确定数据是否正确,且方便读取数据
    echo "<".$state.">";
}else{
	echo "Permission Denied";//请求中没有type或token或token错误时
}
?>