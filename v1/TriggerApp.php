<?php
include('../includes/db_connect.php');
$session_id=$_POST['SessionId'];
$app_key=$_POST['AppKey'];
session_start();

$q_session ="select * from sessions where id='$session_id'";
$result_session=mysql_query($q_session);
while($line=mysql_fetch_array($result_session))
{
	$user = $line['user'];
}

$returnString=null;
if(mysql_num_rows($result_session)>0)
{
	
	$app_user_q= "select * from app_dev_link where developer='$user' and app_key='$app_key'";
	$app_user_r=mysql_query($app_user_q);
	if(mysql_num_rows($app_user_r)>0)
	{
		exec("php run.php > /dev/null &",$test);
	}
	else
	{
		$returnString .="<code>2</code><message>Not Authorized</message>"
	}
	
	
}

else
{
	
}


?>