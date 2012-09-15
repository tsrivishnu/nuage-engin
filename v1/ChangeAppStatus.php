<?php
include('../includes/db_connect.php');
$session_id=$_POST['SessionId'];
session_start();

$q_session ="select * from sessions where id='$session_id'";
$result_session=mysql_query($q_session);

if(mysql_num_rows($result_session)>0)
{

	$returnString = "Error";
	$app_key = $_POST['AppKey'];
	if(isset($_POST['AppKey'])&&isset($_POST['status']))
	{
		$running_status =$_POST['status'];
		
		$returnString = "<response>";
		
		if($running_status != null || $running_status == 3 || $running_status == 2 || $running_status == 1)
		{
			$query = "update app_details set flag='$running_status' where app_key='$app_key'";
			if(mysql_query($query))
			{
				
				
				if($running_status == 3)
				{
					$query_trigger = "update app_details set trigger_flag='0' where app_key='$app_key'";
					if(mysql_query($query_trigger))
					{
						$returnString .= "<code>1</code><message>OK</message>";
					}
					else
					{
						$returnString .= "<code>0</code><message>Mysql Error: $error</message>";
					}
				}
				else
				{
					$returnString .= "<code>1</code><message>OK</message>";
				}
				
				
			}
			else
			{
				$error=mysql_error();
				//header("Location : ../site/display-message?msg=<center>Mysql Error: $error</center>");
				$returnString .= "<code>0</code><message>Mysql Error: $error</message>";
				
			}
		}
		else
		{
			//header("Location : ../site/display-message?msg=<center>There was a error, Please try again.</center>");
			$returnString .= "<code>0</code><message>There was a error, Please try again.</message>";
		}
	}
	$returnString .="</response>";
	echo $returnString;
	
	//header("Location: ../site/display-message.php?msg=<center>UnExpected error.</center>");

}
else
{
	echo "User not set";
}

?>