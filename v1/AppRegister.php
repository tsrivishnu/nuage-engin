<?php
include('../includes/db_connect.php');


ini_set( "display_errors", 0);
$session_id=$_POST['SessionId'];
$app_name=$_POST['AppName'];
$app_key = md5($app_name);
$feed_url=$_POST['FeedUrl'];
$tags_serialized=$_POST['Tags'];
$interval=$_POST['Interval'];
$package_name = $_POST['PackageName'];

$tags=unserialize(stripslashes($tags_serialized));

$returnString = "<response>";

$q_session ="select * from sessions where id='$session_id'";
$result_session=mysql_query($q_session) or die('Error in q_session: '.mysql_error());


if(mysql_num_rows($result_session)>0)
{
	while($line=mysql_fetch_array($result_session))
	{
		$user = $line['user'];
	}
	

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
		
		$query = "insert into app_details values ('$app_key','$app_name','$feed_url','$tags_str','$parents_str','$values_str','$interval','1','0')";
		$query1="insert into app_dev_link values ('$app_key','$app_name','$user')";
		$result=mysql_query($query) or die("Error in query : ".mysql_error());
		$result1=mysql_query($query1) or die("Error in query1 : ".mysql_error());
		
		
		//creating the application specific file run.php
		
		mkdir("../app_details/$app_key", 0700);
		$myFile = "../app_details/$app_key/run.php";
		$fh = fopen($myFile, 'w') or die("can't open file");		
$stringData=<<<file_content0
<?php
set_time_limit(0);
		include("../../includes/db_connect.php");
		include("../../v1/send-push-app.php");
		
		\x24app_name = 
file_content0;
		
		$stringData .= '"'.$app_name.'";';
		$stringData .= '$app_key=';
		$stringData .= '"'.$app_key.'";';
		
		$stringData .= <<< file_content1
\x24query_chng_stat = "update app_details set trigger_flag='1' where app_key='\x24app_key'";
		\x24result_chng_stat = mysql_query(\x24query_chng_stat);
		
		while(1)
		{
		
			\x24query = "select * from app_details where app_key='\x24app_key'";
			\x24result=mysql_query(\x24query)or die("Error in query :" .mysql_error());
			
			\x24tags_db = array();
			\x24parents_db = array();
			\x24values_db = array();
			
			if(mysql_num_rows(\x24result)==1)
			{
				while(\x24line=mysql_fetch_array(\x24result))
				{
file_content1;

$stringData .= "
					\x24temp_tags=\x24line['tags'];
					\x24temp_parents=\x24line['parents'];
					\x24temp_values=\x24line['update_values'];
					\x24tags_db=unserialize(\x24temp_tags);
					\x24parents_db=unserialize(\x24temp_parents);
					\x24values_db=unserialize(\x24temp_values);
					\x24feed_url=\x24line['feed_url'];
					\x24interval=\x24line['interval'];
					\x24app_status =\x24line['flag'];
					
					\x24app_key=\x24line['app_key'];
					
				}
			}";
$stringData.=<<<file_content1
			else
			{
				echo "Some error happened";
			}
			
			if(\x24app_status==1)
			{
file_content1;

$stringData .= '
				$test = file_get_contents($feed_url);
				$xml = simplexml_load_string($test);
				$doc = new DOMDocument(); 
				@$str = $xml->asXML(); 
				$doc->loadXML($str); 
				
				
				$arr =array(); ';
$stringData .=<<<file_content1
				\x24count=count(\x24tags_db);           //number of tags need
				for(\x24i=0;\x24i<\x24count;\x24i++)          //getting the updates
				{
					\x24flag=0;
file_content1;
$stringData.='
					$bar_count = $doc->getElementsByTagName("$tags_db[$i]");
					foreach($bar_count as $node)
					{
						if($flag==0)  //to get only the first one in the array..!!
						{
							if($node->nodeName == $tags_db[$i])
							{
								if($node->parentNode->nodeName == $parents_db[$i] )
								{
									$update_values[$i] = $node->nodeValue;
									$flag=1;
									echo $node->parentNode->nodeName." - ".$node->nodeValue. "<br>";
								}
							}
						}
						else
						{
							break;
						}
					}
				}
				
				$flag_2 = 0;     //to check whether a update is found.
				
				
				echo "<pre>";
				print_r($update_values);
				echo "</pre>";
				
				
				for($i=0;$i<$count;$i++)
				{
					if($update_values[$i] != $values_db[$i])
					{
						$flag_2 = 1;
						
					}
				}
				
				if($flag_2 == 1)
				{
					echo "updates found..!!";
					
					$updates_serialized = serialize($update_values);';
$stringData .=<<<file_content1
					\x24update_query = "UPDATE  app_details SET  update_values='\x24updates_serialized' where app_name='\x24app_name'";
					\x24update_result = mysql_query(\x24update_query) or die("Error in update_query: ".mysql_error());
					
					\x24tags_to_send_push = serialize(\x24tags_db);
					
					//\x24message = \x24updates_serialized."------".\x24tags_to_send_push;
					
					\x24counter=count(\x24update_values);
					
					\x24message="<message>";
					for(\x24i=0;\x24i<\x24counter;\x24i++)
					{
						\x24message .="<\x24tags_db[\x24i]>\x24update_values[\x24i]</\x24tags_db[\x24i]>";
					}
					\x24message.="</message>";
					
					//C2DMPush::SendPush(\x24app_key,\x24message);
					AskPush(\x24app_key,\x24message);
					
				}
				else
				{
					//echo "no updates found for now..!! thats oki dude.. chill...";
					
				\x24message= "No updates found..!!";
				
				//C2DMPush::SendPush(\x24app_key,\x24message);
				AskPush(\x24app_key,\x24message);
					
				}
				
				sleep(\x24interval);
			}
			
			else if(\x24app_status==2)
			{
			
				\x24message= "In the case 2";
				
				//C2DMPush::SendPush(\x24app_key,\x24message);
				AskPush(\x24app_key,\x24message);
				sleep(\x24interval);
			}
			else if(\x24app_status==3)
			{
				
				\x24query_trigger = "update app_details set trigger_flag='0' where app_key='\x24app_key'";
				mysql_query(\x24query_trigger);
				
				\x24message= "Exitting";
				
				//C2DMPush::SendPush(\x24app_key,\x24message);
				AskPush(\x24app_key,\x24message);
				
				StopUpdateCheck();	
			}
		}
		
		function StopUpdateCheck()
		{
			echo "exitting";
			exit();
		}
file_content1;
$stringData .='
		function AskPush($app_key_r, $message_r)
		{
			$url = "http://nuage-engin.savys.in/v1/send-push-message.php";
			$data = "AppKey=$app_key_r&Message=$message_r";
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url); 
			curl_setopt($ch, CURLOPT_COOKIESESSION, true); 
			//curl_setopt($ch, CURLOPT_HEADER, TRUE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			curl_setopt($ch, CURLOPT_POST, true); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			$response = curl_exec($ch); 
			curl_close($ch);
			
			echo $response;
		
		}
		?>';

		$myFile2 = "../app_details/$app_key/trigger.php";
		$fh2 = fopen($myFile2, 'w') or die("can't open file");
		$stringData2 = <<<file_content2
<?php
		exec("php run.php > /dev/null &",$test);
header("Location: ../../site/display-message.php?msg=<center>Application triggered successfully..!!<br>You users will start receiving the push messages</center>");
?>
file_content2;
		
		if(fwrite($fh, $stringData) && fwrite($fh2, $stringData2))
		{
			$file_flag=1;
			//header("Location: ../site/display-message.php?msg=<center>Application Details are stored successfully.</center>");
			
			$returnString .= "<code>1</code><message>Application Details Stored</message>";
		}
		fclose($fh);
		fclose($fh2);
		}
		else
		{
			$returnString .= "<code>106</code><message>URL error</message>";
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