<?php
include('../includes/db_connect.php');

$session_id=$_POST['SessionId'];
$app_key = $_POST['AppKey'];
$feed_url=$_POST['FeedUrl'];
$tags_serialized=$_POST['Tags'];
//$interval=$_POST['Interval'];

$tags=unserialize(stripslashes($tags_serialized));
$returnString = '<?xml version="1.0" encoding="UTF-8"?>';
$returnString .= "<response>";

$q_session ="select * from sessions where id='$session_id'";
$result_session=mysql_query($q_session) or die('Error in q_session: '.mysql_error());


if(mysql_num_rows($result_session)>0)
{
	while($line=mysql_fetch_array($result_session))
	{
		$user = $line['user'];
	}
	
	$q_check= "select * from app_details where app_key='$app_key'";
	$r_check=mysql_query($q_check);
	
	if(mysql_num_rows($r_check)>0)
	{

		$parents=array();           //creating parent of tag nodes - started
		$count=count($tags);
		$i=0;
		
		
		for($i = 0;$i < $count; $i++)
		{
			$temp=$tags[$i];
			$pos=strpos($temp,"_");
			$tags[$i]=substr($temp,0,$pos);
			$parents[$i]=substr($temp,$pos+1);
		}						//creating parent of tag nodes - end
		
		
		
		if($test = file_get_contents($feed_url))
		{
			$xml = simplexml_load_string($test);
			$doc = new DOMDocument(); 
			$str = $xml->asXML(); 
			$doc->loadXML($str); 
			
			$count = count($tags);
	
			for($i=0;$i<$count;$i++)          //getting the updates
			{
				$flag=0;
				$bar_count = $doc->getElementsByTagName("$tags[$i]");
				foreach($bar_count as $node)
				{
					if($flag==0)  //to get only the first one in the array..!!
					{
						if($node->parentNode->nodeName==$parents[$i])
						{
							$values[$i] = $node->nodeValue;
							$flag=1;
							//echo $node->parentNode->nodeName." - ".$node->nodeValue. "<br>";
						}	
					}
					else
					{
						break;
					}
				}
			}
			
			$tags_str = serialize($tags);
			$parents_str = serialize($parents);
			$values_str = serialize($values);
			

	
			$app_status = 1; //indicates the application isready to be run.
			
			$query = "update app_details set feed_url='$feed_url',tags='$tags_str',parents='$parents_str',update_values='$values_str' where app_key='$app_key'";
			if($result=mysql_query($query))
			{

				$returnString .= "<code>1</code><message>Application Details Stored</message>";
			}
		
			else
			{
				$returnString .= "<code>106</code><message>Unknown error, Please try later ".mysql_error()."</message>";
			}
		}
		else
		{
			$returnString .= "<code>108</code><message>URL Error</message>";
		}
	}
	else
	{
		$returnString .= "<code>107</code><message>Application Not Found</message>";
	}
	
}
else
{
	//header("Location: ../site/display-message.php?msg=<center>Please login to continue</center>");
	$returnString .= "<code>101</code><message>unauthorized</message>";
}

$returnString .= "</response>";
echo $returnString;

?>