<?php
if (($_FILES["file"]["type"] == "image/jpeg")&& ($_FILES["file"]["size"] < 300000))//图片上传限制300KB
{
  if ($_FILES["file"]["error"] > 0)
  {
    echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
  }
  else
  {
	echo "Upload: " . $_FILES["file"]["name"] . "<br />";
	echo "Type: " . $_FILES["file"]["type"] . "<br />";
	echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
	echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";
	if (! empty ( $_FILES ['file'] ['name']))
	{
		$tmp_file = $_FILES ['file'] ['tmp_name'];
		$file_types = explode (".",$_FILES ['file'] ['name'] );
		$file_type = $file_types[count( $file_types)-1];
		$savePath = 'C:/xampp/htdocs/WXapp/CamPic/';
		$file_name = "CamPic.jpg";
		if (! copy ($tmp_file, $savePath.$file_name))
		{
			 echo("failed");
		}
		else
		{
			echo($savePath . $file_name);
			echo("success<br>");
		}
	}
  }
}
else
{
  echo "Invalid file";
}
?>