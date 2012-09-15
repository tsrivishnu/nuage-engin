<?php

class C2DMPush
{
	
	function SendPush($app_key,$message)	
	{
	
		$query = "select * from app_details where app_key = '$app_key'";
		$result=mysql_query($query) or die("Error in Query: ".mysql_error());
		
		if(mysql_num_rows($result)>0)
		{
				
				
				$query2="select id from id_table where app_key='$app_key'";
				
				$result2=mysql_query($query2) or die("error running the query2, Error is ".mysql_error());
				
				if(mysql_num_rows($result2)>0)
				{
					//$counter=mysql_num_rows($result2);
						while($line = mysql_fetch_assoc($result2))
						{
							foreach($line as $app => $value)
							{
								$deviceRegistrationId=$value;
								
							}
						}
				}
				  else
				  {
					  
				}
				
				$msgType="1";
				
			
				
				$messageText=$message;
				
			
				
				
				if($authtoken=C2DMPush::googleAuthenticate("t.srivishnu@gmail.com","mypassword","Savys_in-test-1.0","ac2dm"))
				{
					
					
					if($mes_send = C2DMPush::sendMessageToPhone($authtoken, $deviceRegistrationId, $msgType, $messageText) )
						{
							//echo "message to mobile sent successfully<br>";
							echo "<br>$mes_send";
						}
						
				}
		}		
		else
		{
			echo "<center><font color=red>Application Details are not Found</font><br>Visit <a href='save_app_details'>this</a> Page to register details</center>";
			
		}
		
		
	}
		
		
		
		//functions here
		
		
	
	function googleAuthenticate($username, $password, $source="Savys_in-test-1.0", $service="ac2dm") 
	{    
	
		session_start();
		if( isset($_SESSION['google_auth_id']) && $_SESSION['google_auth_id'] != null)
			return $_SESSION['google_auth_id'];
	
		// get an authorization token
		$ch = curl_init();
		if(!$ch)
		{
			return false;
		}
	
		curl_setopt($ch, CURLOPT_URL, "https://www.google.com/accounts/ClientLogin");
		$post_fields = "accountType=" . urlencode('HOSTED_OR_GOOGLE')
			. "&Email=" . urlencode($username)
			. "&Passwd=" . urlencode($password)
			. "&source=" . urlencode($source)
			. "&service=" . urlencode($service);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);    
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
		// for debugging the request
		//curl_setopt($ch, CURLINFO_HEADER_OUT, true); // for debugging the request
	
		$response = curl_exec($ch);
	
		//var_dump(curl_getinfo($ch)); //for debugging the request
		//var_dump($response);
	
		curl_close($ch);
	
		if (strpos($response, '200 OK') === false) 
		{
			echo "response is other than 200 OK";
			echo $response;
			return false;
			
		}
	
		// find the auth code
		preg_match("/(Auth=)([\w|-]+)/", $response, $matches);
	
		if (!$matches[2]) 
		{
			echo "mathces more than 2";
			return false;
		}
	
		$_SESSION['google_auth_id'] = $matches[2];
		echo "auth token is ";
		echo $_SESSION['google_auth_id'];
		return $matches[2];
	}
			
	function sendMessageToPhone($authCode, $deviceRegistrationId, $msgType, $messageText) 
	{
	
		$headers = array('Authorization: GoogleLogin auth=' . $authCode);
		$data = array(
			'registration_id' => $deviceRegistrationId,
			'collapse_key' => $msgType,
			'data.message' => $messageText //TODO Add more params with just simple data instead           
		);
	
		$ch = curl_init();
	
		curl_setopt($ch, CURLOPT_URL, "https://android.apis.google.com/c2dm/send");
		if ($headers)
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	
	
		$response = curl_exec($ch);
	
		curl_close($ch);
	
		return $response;
		//echo "Send message response is ------- ".$response;
	}

}

?>