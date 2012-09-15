<?php
include('../includes/db_connect.php');
ini_set( "display_errors", 0);
$app_key=$_GET['AppKey'];
$session_id=$_GET['SessionId'];

$q_session ="select * from sessions where id='$session_id'";
$result_session=mysql_query($q_session) or die('Error in q_session: '.mysql_error());

$returnString=null;
$returnString = '<?xml version="1.0" encoding="UTF-8"?>';
if(mysql_num_rows($result_session)>0)
{
	while($line=mysql_fetch_array($result_session))
	{
		$user = $line['user'];
	}
	
	$query_dev="select * from app_dev_link where developer='$user' and app_key='$app_key'";
	$result_dev=mysql_query($query_dev);
	if(mysql_num_rows($result_dev)>0)
	{
	
		$returnString .= "<appstatus>";
		$details_query = "select * from app_details where app_key='$app_key'";
		$details_result=mysql_query($details_query) or die("Error in details_query: ".mysql_error());
		
		if(mysql_num_rows($details_result)>0)
		{
			while($line = mysql_fetch_array($details_result))
			{
				$app_name=$line['app_name'];
				$app_key = $line['app_key'];
				$feed_url= $line['feed_url'];
				$running_status=$line['flag'];
				$trigger_status=$line['trigger_flag'];
				$interval=$line['interval'];
				$tags_serial=$line['tags'];
				$parents_serial=$line['parents'];
				$tags=array();
				$parents=array();
				$tags=unserialize($tags_serial);
				$parents=unserialize($parents_serial);
			}
			
			$returnString .= "<name>$app_name</name>
								<key>$app_key</key>
								<feedurl>$feed_url</feedurl>
								<interval>$interval</interval>
								<tags>$tags_serial</tags>
								<parents>$parents_serial</parents>
								<runningstatus>$running_status</runningstatus>
								<triggerstatus>$trigger_status</triggerstatus>";
			
		}
		else
		{
			
		}
		
		$returnString .= "</appstatus>";
	}
	else
	{
		$returnString ="<error><errorno>105</errorno><errormsg>UnAuthorized</errormsg></error>";
	}
}
else
{
	$returnString ="<error><errorno>104</errorno><errormsg>Not Authenticated</errormsg><error>";
}

echo $returnString;
?>