<?php
$img = file_get_contents('http://5c5401bb.ngrok.natapp.cn/?action=snapshot'); 
file_put_contents('./camPic/'.date('U').'.jpg',$img); 
echo '<img src="1.gif">';
?>
