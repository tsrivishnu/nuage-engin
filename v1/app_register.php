<?php
//include('../includes/header.php');
include('../includes/db_connect.php');
//include('send-push-app.php');
//echo"<div id='content'>
//<div id='display'>";

session_start();
if(isset($_SESSION['user']))
{
	$xml_string=$_POST['xml_string'];
	//echo strlen($xml_string);
	$url_flag=$_POST['url_valid_flag'];    //to check whether a valid url is provided.
	if($url_flag)
	{
		$user=$_SESSION['user'];
		$app_name=$_POST['app_name'];
		$app_key = md5($app_name);
		$update_type=$_POST['updates_type_select'];
		$xml_string=$_POST['xml_string'];
		//$package_name_select = $_POST['package_name_select'];
		if($update_type=="1")
		{
			$feed_url=$_POST['feed_url'];
			$tags=$_POST['tags'];
			$interval=$_POST['interval'];
		}
		else
		{
		}
		//if($package_name_select==1)
		//{
			$package_name = $_POST['package_name'];
		//}
		//else
		//{
			//$package_name = $package_name_select;
		//}
		
		//echo "user is $user<br>app_name is $app_name<br>package name is $package_name<br>feed_url=$feed_url<br>";
		
		$parents=array();           //creating parent of tag nodes - started
		$count=count($tags);
		$i=0;
		
		//echo "here".$tags[2];
		for($i = 0;$i < $count; $i++)
		{
			$temp=$tags[$i];
			$pos=strpos($temp,"_");
			$tags[$i]=substr($temp,0,$pos);
			$parents[$i]=substr($temp,$pos+1);
		}						//creating parent of tag nodes - end
		
		
		//print_r($tags); echo "<br>";
		//print_r($parents);
		
		//echo $xml_string;
		//echo "here it is..!! +><pre>".$xml_string."</pre>";
		
		
		$test = file_get_contents("http://urgrove.com/feed");
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
		
		//print_r($tags);
		//print_r($parents);
		//print_r($values);	
		$tags_str = serialize($tags);
		$parents_str = serialize($parents);
		$values_str = serialize($values);
		
		$app_id=md5($app_name);
		
		$app_status = 1; //indicates the application isready to be run.
		
		$query = "insert into app_details values ('$app_id','$app_name','$feed_url','$tags_str','$parents_str','$values_str','$interval','1','0')";
		$query1="insert into app_dev_link values ('$app_id','$app_name','$user')";
		$result=mysql_query($query) or die("Error in query : ".mysql_error());
		$result1=mysql_query($query1) or die("Error in query1 : ".mysql_error());
		
		
		//creating the application specific file run.php
		
		mkdir("../app_details/$app_key", 0700);
		$myFile = "../app_details/$app_key/run.php";
		$fh = fopen($myFile, 'w') or die("can't open file");
		
		$stringData = <<< 'file_content0'
		<?php
		include("../../includes/db_connect.php");
		include("../../v1/send-push-app.php");
		
		$app_name = 
file_content0;
		
		$stringData .= '"'.$app_name.'";';
		$stringData .= '$app_key=';
		$stringData .= '"'.$app_key.'";';
		
		$stringData .= <<< 'file_content1'
		$query_chng_stat = "update app_details set trigger_flag='1' where app_key='$app_key'";
		$result_chng_stat = mysql_query($query_chng_stat);
		
		$app_key=md5($app_name);
		while(1)
		{
		
			$query = "select * from app_details where app_key='$app_key'";
			$result=mysql_query($query)or die("Error in query :" .mysql_error());
			
			$tags_db = array();
			$parents_db = array();
			$values_db = array();
			
			if(mysql_num_rows($result)==1)
			{
				while($line=mysql_fetch_array($result))
				{
					$tags_db = unserialize($line['tags']);
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
file_content1;
	
		$myFile2 = "../app_details/$app_key/trigger.php";
		$fh2 = fopen($myFile2, 'w') or die("can't open file");
		$stringData2 = <<<'file_content2'
<?php
		exec("php run.php > /dev/null &",$test);
header("Location: ../../site/display-message.php?msg=<center>Application triggered successfully..!!<br>You users will start receiving the push messages</center>");
?>
file_content2;
		
		if(fwrite($fh, $stringData) && fwrite($fh2, $stringData2))
		{
			$file_flag=1;
			header("Location: ../site/display-message.php?msg=<center>Application Details are stored successfully.</center>");
		}
		fclose($fh);
		fclose($fh2);
	}
	
	else
	{	
		header("Location: ../site/display-message.php?msg=<center>Please go back and provide a valid URL</center>");
	}
}
else
{
	header("Location: ../site/display-message.php?msg=<center>Please login to continue</center>");
}
echo "</div>

</div>  <!-- Page div end --!>";
include('../includes/sidebar.php');
include('../includes/footer.php');


?>