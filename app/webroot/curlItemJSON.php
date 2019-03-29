<?php
//dev
$url = "http://dev.ampfg.com/robotStatuses/input/json";

//external
// $url = "https://fg.ampprinting.com/robotStatuses/input/json";
 
$post_data = ['
{
	"Credentials":
		{
			"company":"Curly Media",
			"token":"ac62001e66caaa8614610284b07d50c1a7f487b1"
		}
	,
	"Items":
	[
		{"item_number": "1902-AEKE", "description": "This is an item description", "starting_inventory": 500},
        {"item_number": "1902-AEKE", "description": "This is an item description", "starting_inventory": 500},
        {"item_number": "1902-AEKE", "description": "This is an item description", "starting_inventory": 500},
        {"item_number": "1902-AEKE", "description": "This is an item description", "starting_inventory": 500},
        {"item_number": "1902-AEKE", "description": "This is an item description", "starting_inventory": 500},
        {"item_number": "1902-AEKE", "description": "This is an item description", "starting_inventory": 500},
        {"item_number": "1902-AEKE", "description": "This is an item description", "starting_inventory": 500},
        {"item_number": "1902-AEKE", "description": "This is an item description", "starting_inventory": 500},
        {"item_number": "1902-AEKE", "description": "This is an item description", "starting_inventory": 500}
	]
}
'];

$ch = curl_init();

curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
 
curl_setopt($ch, CURLOPT_URL, $url);
 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// we are doing a POST request
curl_setopt($ch, CURLOPT_POST, 1);
// adding the post variables to the request
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
// adding verbose
curl_setopt($ch, CURLOPT_VERBOSE, 1);
 
$output = curl_exec($ch);

if($erno = curl_errno($ch)){
    $error_message = curl_strerror($erno);
    echo "cURL error ({$erno}):\n{$error_message}";
}

curl_close($ch);

echo "<pre>";
echo print_r(json_decode($output));
echo "</pre>";
//echo $output;
?>