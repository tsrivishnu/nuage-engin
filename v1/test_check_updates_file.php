
<?php
include("../db_connect.php");
include("send-push-app.php");

$app_name = "push_munna";

//$tags_db = array();
//$parents_db = array();
//$values_db = array();

//while(1)
for($mun=0;$mun<10;$mun++)
{

	$query = "select * from app_details where app_name='$app_name'";
	$result=mysql_query($query)or die("Error in query :" .mysql_error());
	
	$tags_db = array();
	$parents_db = array();
	$values_db = array();
	
	if(mysql_num_rows($result)==1)
	{
		while($line = mysql_fetch_array($result))	
		{
			$tags_db=unserialize($line['tags']);
			$parents_db=unserialize($line['parents']);
			$values_db=unserialize($line['update_values']);
			$feed_url=$line['feed_url'];
			$interval=$line['interval'];
			$app_status = $line['flag'];
			//echo $tags_db;
			$app_key=$line['app_key'];
			//$interval=$interval*60;
		}
	}
	else
	{
		echo "Some error happened";
	}
	
	if($app_status==1)
	{
		
		
		
		//$test = file_get_contents("feed.xml");
		$test = file_get_contents($feed_url);
		$xml = simplexml_load_string($test);
		$doc = new DOMDocument(); 
		$str = $xml->asXML(); 
		$doc->loadXML($str); 
		
		
		$arr =array();
		
		$count=count($tags_db);           //number of tags need
		for($i=0;$i<$count;$i++)          //getting the updates
		{
			$flag=0;
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
			
			$updates_serialized = serialize($update_values);
			
			$update_query = "UPDATE  app_details SET  update_values='$updates_serialized' where app_name='$app_name'";
			$update_result = mysql_query($update_query) or die("Error in update_query: ".mysql_error());
			
			$tags_to_send_push = serialize($tags_db);
			
			$message = $updates_serialized."------".$tags_to_send_push;
			
			C2DMPush::SendPush($app_key,$message);
			
		}
		else
		{
			//echo "no updates found for now..!! thats oki dude.. chill...";
			
		$message= "No updates found..!!";
		
		C2DMPush::SendPush($app_key,$message);
		
			
		}
		
		sleep($interval);
	}
	
	else if($app_status==2)
	{
	
		$message= "In the case 2";
		
		C2DMPush::SendPush($app_key,$message);
		
		sleep($interval);
	}
	else if($app_status==3)
	{
	
		$message= "Exitting";
		
		C2DMPush::SendPush($app_key,$message);
		
		StopUpdateCheck();	
	}
}

function StopUpdateCheck()
{
	echo "exitting";
	exit();
}

?>