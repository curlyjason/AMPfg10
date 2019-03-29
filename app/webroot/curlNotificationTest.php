<?php
$url = "http://localhost/notificationReceipt.php";
// $url = "http://localhost/ampfg/xmlOrders/input";
 
$post_data = array (
    '0' => '<?xml version="1.0" encoding="UTF-8"?>
<Body><Credentials><company>Sad New Vistas in Testing</company><token>d27889affe5f30432a3723a5214d3d23363e</token></Credentials><Order><billing_company>Curly Media</billing_company><first_name>Jason</first_name><last_name>Tempestini</last_name><phone>925-895-4468</phone><billing_address>1107 Fountain Street</billing_address><billing_address2/><billing_city>Alameda</billing_city><billing_state>CA</billing_state><billing_zip>94501</billing_zip><billing_country>US</billing_country><note>This is a note for this shipment. It really could be quite a long note.
			It might even have carriage returns.</note><OrderItems><OrderItem><index>0</index><catalog_id>125</catalog_id><name>Kit - Inventory Both - Can Order</name><quantity>1</quantity></OrderItem><OrderItem><index>1</index><catalog_id>82</catalog_id><name>Elementary Geography - Single Copy</name><quantity>5</quantity></OrderItem><OrderItem><index>2</index><catalog_id>122</catalog_id><name>Mobile Record</name><quantity>1</quantity></OrderItem></OrderItems><Shipments><billing>Sender</billing><carrier>UPS</carrier><method>1DA</method><billing_account/><first_name>Jason</first_name><last_name>Tempestini</last_name><email>jason@tempestinis.com</email><phone>925-895-4468</phone><company>Curly Media</company><address>1107 Fountain Street</address><address2/><city>Alameda</city><state>CA</state><zip>94501</zip><country>US</country><tpb_company/><tpb_address/><tpb_city/><tpb_state/><tpb_zip/><tpb_phone/></Shipments></Order></Body>
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