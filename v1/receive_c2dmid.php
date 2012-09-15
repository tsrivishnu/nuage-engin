<?php
$id = $_POST["reg_id"];
$app_key=$_POST["app_key"];
$imei=$_POST["IMEI"];

//$id="jkdshfjhdsga";
//$app_name="push_munna";


//$app_key=md5($app_name);


$link = mysql_connect('localhost','******','****************') or die('Hav Error:'.mysql_error());
mysql_select_db('***********')or die('Could not select database'.mysql_error());

$query0="select * from id_table where app_key='$app_key' and imei='$imei'";
$query = "insert into id_table values ('$imei','$app_key','$id')";
$query2="update id_table set id='$id' where app_key='$app_key' and imei='$imei'";

$result0 = mysql_query($query0) or die('Query0 Failed:'.mysql_error());
if(mysql_num_rows($result0)>0)
{
	$result2 = mysql_query($query2) or die('Query2 Failed:'.mysql_error());
}
else
{
	$result = mysql_query($query) or die('Query Failed:'.mysql_error());
}


//$result = mysql_query($query) or die('Query Failed:'.mysql_error());
?>