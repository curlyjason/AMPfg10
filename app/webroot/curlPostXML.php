<?php
//dev
$url = "http://dev.ampfg.com/robotOrders/input/xml";

//external
// $url = "https://fg.ampprinting.com/robotOrders/input/xml";
 
$post_data = array (
    '0' => '<?xml version="1.0" encoding="UTF-8"?>
<Body>
    <Credentials>
        <company>Curly Media</company>
        <token>146567403f8aadb4bbd468b9aa7879742704c2ca</token>
    </Credentials>
    <Orders>
        <Order>
            <billing_company>Sad New Vistas in Testing</billing_company>
            <first_name>Jason</first_name>
            <last_name>Tempestini</last_name>
            <phone>925-895-4468</phone>
            <billing_address>1107 Fountain Street</billing_address>
            <billing_address2/>
            <billing_city>Alameda</billing_city>
            <billing_state>CA</billing_state>
            <billing_zip>94501</billing_zip>
            <billing_country>US</billing_country>
            <order_reference>orderzz</order_reference>
            <note>This is a note for this shipment. It really could be quite a long note.
                It might even have carriage returns.</note>
            <OrderItems>
                <OrderItem>
                    <catalog_id>52</catalog_id>
                    <customer_item_code/>
                    <name>Eucalyptus</name>
                    <quantity>1</quantity>
                </OrderItem>
                <OrderItem>
                    <catalog_id/>
                    <customer_item_code>bc1</customer_item_code>
                    <name>Ball Cap</name>
                    <quantity>1</quantity>
                </OrderItem>
                <OrderItem>
                    <catalog_id>100</catalog_id>
                    <customer_item_code/>
                    <name>Bag o Rocks</name>
                    <quantity>5</quantity>
                </OrderItem>
            </OrderItems>
            <Shipments>
                <billing>Sender</billing>
                <carrier>UPS</carrier>
                <method>1DA</method>
                <billing_account/>
                <first_name>Jason</first_name>
                <last_name>Tempestini</last_name>
                <email>jason@tempestinis.com</email>
                <phone>925-895-4468</phone>
                <company>Curly Media</company>
                <address>1107 Fountain Street</address>
                <address2/>
                <city>Alameda</city>
                <state>CA</state>
                <zip>94501</zip>
                <country>US</country>
                <tpb_company/>
                <tpb_address/>
                <tpb_city/>
                <tpb_state/>
                <tpb_zip/>
                <tpb_phone/>
            </Shipments>
        </Order>
        <Order>
            <billing_company>Sad New Vistas in Testing</billing_company>
            <first_name>Jason</first_name>
            <last_name>Tempestini</last_name>
            <phone>925-895-4468</phone>
            <billing_address>1107 Fountain Street</billing_address>
            <billing_address2/>
            <billing_city>Alameda</billing_city>
            <billing_state>CA</billing_state>
            <billing_zip>94501</billing_zip>
            <billing_country>US</billing_country>
            <order_reference>orderaa</order_reference>
            <note>This is a note for this shipment. It really could be quite a long note.
                It might even have carriage returns.</note>
            <OrderItems>
                <OrderItem>
                    <catalog_id>52</catalog_id>
                    <customer_item_code/>
                    <name>Eucalyptus</name>
                    <quantity>10</quantity>
                </OrderItem>
                <OrderItem>
                    <catalog_id/>
                    <customer_item_code>bc1</customer_item_code>
                    <name>Ball Cap</name>
                    <quantity>10</quantity>
                </OrderItem>
            </OrderItems>
            <Shipments>
                <billing>Sender</billing>
                <carrier>UPS</carrier>
                <method>1DA</method>
                <billing_account/>
                <first_name>Jason</first_name>
                <last_name>Tempestini</last_name>
                <email>jason@tempestinis.com</email>
                <phone>925-895-4468</phone>
                <company>Curly Media</company>
                <address>1107 Fountain Street</address>
                <address2/>
                <city>Alameda</city>
                <state>CA</state>
                <zip>94501</zip>
                <country>US</country>
                <tpb_company/>
                <tpb_address/>
                <tpb_city/>
                <tpb_state/>
                <tpb_zip/>
                <tpb_phone/>
            </Shipments>
        </Order>    
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