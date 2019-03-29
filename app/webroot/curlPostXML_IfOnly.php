<?php
 $url = "https://fg.ampprinting.com/robotOrders/input/xml";
 
$post_data = array (
    '0' => '<?xml version="1.0" encoding="UTF-8"?>
<Body>
    <Credentials>
        <company>IFOnly American Express City Blitz</company>
        <token>270afe2adbee28b5ffcd87287c5707d66f292d46</token>
    </Credentials>
    <Orders>
        <Order>
            <billing_company>If Only</billing_company>
            <first_name>Celia</first_name>
            <last_name>Peachey</last_name>
            <phone>(518) 256-3396</phone>
            <billing_address>244 Jackson Street, 4th Floor</billing_address>
            <billing_address2/>
            <billing_city>San Francisco</billing_city>
            <billing_state>CA</billing_state>
            <billing_zip>94111</billing_zip>
            <billing_country>US</billing_country>
            <order_reference>order123345</order_reference>
            <note>This is a note for this shipment. It really could be quite a long note.
                It might even have carriage returns.</note>
            <OrderItems>
                <OrderItem>
                    <catalog_id>1602</catalog_id>
                    <customer_item_code/>
                    <name>Test Item #1</name>
                    <quantity>1</quantity>
                </OrderItem>
                <OrderItem>
                    <catalog_id/>
                    <customer_item_code>TestItem2</customer_item_code>
                    <name>Test Item #2</name>
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
            <billing_company>If Only</billing_company>
            <first_name>Celia</first_name>
            <last_name>Peachey</last_name>
            <phone>(518) 256-3396</phone>
            <billing_address>244 Jackson Street, 4th Floor</billing_address>
            <billing_address2/>
            <billing_city>San Francisco</billing_city>
            <billing_state>CA</billing_state>
            <billing_zip>94111</billing_zip>
            <billing_country>US</billing_country>
            <order_reference>order123346</order_reference>
            <note>This is a note for this shipment. It really could be quite a long note.
                It might even have carriage returns.</note>
            <OrderItems>
                <OrderItem>
                    <catalog_id>1602</catalog_id>
                    <customer_item_code/>
                    <name>Test Item #1</name>
                    <quantity>10</quantity>
                </OrderItem>
                <OrderItem>
                    <catalog_id/>
                    <customer_item_code>TestItem2</customer_item_code>
                    <name>Test Item #2</name>
                    <quantity>50</quantity>
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

$ch = curl_init();

curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
 
curl_setopt($ch, CURLOPT_URL, $url);
 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($ch, CURLOPT_POST, 1);

curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
 
$output = curl_exec($ch);
 
curl_close($ch);
 
echo $output;
?>