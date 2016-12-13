<?php 
define("MAX_VALUE","30");
function post($url, $data){//使用 file_get_content 进行POST
        $postdata = http_build_query(
            $data
        );
        $opts = array('http' =>
                      array(
 
                          'method'  => 'POST',
 
                          'header'  => 'Content-type: application/x-www-form-urlencoded',
 
                          'content' => $postdata
 
						)
 
        );
		$context = stream_context_create($opts);
		$result = file_get_contents($url, false, $context);
        return $result;
}

$url = "http://www.smsadmin.cn/smsmarketing/wwwroot/api/post_send/";
$msg = "报告！主人您的房间温度已超过".MAX_VALUE."!!!请尽快查看~~~";
$data = array("uid"=>"991067661",  "pwd"=>"X18408249668", "mobile"=>"18408249668", "msg" =>"$msg");
header ("Content-Type: text/html; charset=UTF-8" );
echo post($url,$data);

?>

