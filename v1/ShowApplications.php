<?php
include('../includes/db_connect.php');
//include('../includes/header.php');

$user= $_GET['user'];

$query="select app from app_dev_link where developer='$user'";
$result=mysql_query($query) or die('You have error connecting database'.mysql_error()); 

$returnString=null;

$returnString  ="<response>";


if(mysql_num_rows($result)>0)
{
	
	while($line = mysql_fetch_assoc($result))
	{
		foreach($line as $app => $value)
		{
			$app_key=md5($value);
			$query_status = "select flag,trigger_flag from app_details where app_key='$app_key'";
			$result_status = mysql_query($query_status) or die("Error in Query_status: ".mysql_error());
			
			while($line = mysql_fetch_array($result_status))
			{
				$running_status = $line['flag'];
				$trigger_status = $line['trigger_flag'];
			}
			
			$returnString .="<code>1</code>";
			$returnString .= " <app>
				<name>$value</name>
				<key>$app_key</key>
				";
			
			
			if($running_status==1 && $trigger_status==1)
			{	
				$returnString .= "<status>Running</status>";	
			}
			else if($running_status==1 && $trigger_status==0)
			{
				$returnString .= "<status>Not Triggered</status>";	
			}
			else if($running_status==2)
			{
				$returnString .= "<status>Paused</status>";
			}
			else if($running_status==3)
			{
				$returnString .= "<status>Stopped</status>";
			}

			$returnString .= "</app>";
		}
	}

	
	
}
else
{
	  $returnString .="<code>101</code><message>No Applications Found</message>";
}
$returnString .= "</response>";
echo $returnString;

?>