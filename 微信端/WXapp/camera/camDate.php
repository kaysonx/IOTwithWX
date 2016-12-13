<?php
	//使时区一致
	date_default_timezone_set('prc');
	$memcache = new Memcache;
	$memcache->connect('127.0.0.1',11211) or die('connect error!');
	$time = $memcache->get('time');
	if($time != "" && $time != null){
		echo date('Y-m-d H:i:s',$time);
	}else{
		echo date('Y-m-d H:i:s',time());
	}

?>
