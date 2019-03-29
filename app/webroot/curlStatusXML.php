<?php
//dev
$url = "http://dev.ampfg.com/robotStatuses/input/xml";

//external
// $url = "https://fg.ampprinting.com/robotStatuses/input/xml";
 
$post_data = array (
    '0' => '<?xml version="1.0" encoding="UTF-8"?>
<Body>
    <Credentials>
        <company>Curly Media</company>
        <token>146567403f8aadb4bbd468b9aa7879742704c2ca</token>
    </Credentials>
    <Orders>
        <OrderNumbers>
            <order_number>1902-AEEM</order_number>
            <order_number>1902-AEEN</order_number>
        </OrderNumbers>
        <OrderReferences>
            <order_reference>order15</order_reference>
            <order_reference>order16</order_reference>
            <order_reference>Jason</order_reference>
        </OrderReferences>    
    </Orders>
</Body>
');

// $post_data = array (
//     0 => '');
 
$ch = curl_init();

curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
 
curl_setopt($ch, CURLOPT_URL, $url);
 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// we are doing a POST request
curl_setopt($ch, CURLOPT_POST, 1);
// adding the post variables to the request
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
 
$output = curl_exec($ch);
 
curl_close($ch);
 
echo $output;
?>