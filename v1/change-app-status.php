<?php

include('../includes/db_connect.php');

$app_key = $_POST['app_key'];

if(isset($_POST['status']))
{
	$running_status =$_POST['status'];
	
	$returnString = "<response>";
	
	if($running_status != null || $running_status == 3 || $running_status == 2 || $running_status == 1)
	{
		$query = "update app_details set flag='$running_status' where app_key='$app_key'";
		if(mysql_query($query))
		{
			header("Location : ../site/display-message?msg=<center>Application Status Changed Successfully</center>");	
			
			$returnString .= "<code>1</code><message>OK</message>";
		}
		else
		{
			$error=mysql_error();
			header("Location : ../site/display-message?msg=<center>Mysql Error: $error</center>");
			$returnString .= "<code>0</code><message>Mysql Error: $error</message>";
			
		}
	}
	else
	{
		header("Location : ../site/display-message?msg=<center>There was a error, Please try again.</center>");
		$returnString .= "<code>0</code><message>There was a error, Please try again.</message>";
	}
}

echo $returnString;

//header("Location: ../site/display-message.php?msg=<center>UnExpected error.</center>");



?>