<?php
//dev
//$url = "http://dev.ampfg.com/robotOrders/input/json";

//external
 $url = "https://fg.ampprinting.com/robotOrders/input/json";
 $token_dev = "146567403f8aadb4bbd468b9aa7879742704c2ca";
 $token_prod = "ac62001e66caaa8614610284b07d50c1a7f487b1";
 
$post_data = ['
{
	"Credentials":
		{
			"company":"Curly Media",
			"token":"ac62001e66caaa8614610284b07d50c1a7f487b1"
		}
	,
	"Orders":
	[
		{
			"billing_company":"Sad New Vistas in Testing",
			"first_name":"Jason",
			"last_name":"Tempestini",
			"phone":"925-895-4468",
			"billing_address":"1107 Fountain Street",
			"billing_address2":"",
			"billing_city":"Alameda",
			"billing_state":"CA",
			"billing_zip":"94501",
			"billing_country":"US",
			"order_reference":"order176",
			"note":"This is a note for this shipment. It really could be quite a long note.\n It might even have carriage returns.",
			"OrderItem":
			[
				{
					"catalog_id":"52",
					"customer_item_code":"",
					"name":"Eucalyptus",
					"quantity":"1"
				},
				{
					"catalog_id":"",
					"customer_item_code":"bc1",
					"name":"Ball Cap",
					"quantity":"1"
				},
				{
					"catalog_id":"100",
					"customer_item_code":"",
					"name":"Bag o Rocks",
					"quantity":"5"
				}
			],
			"Shipment":
				{
					"billing":"Sender",
					"carrier":"UPS",
					"method":"1DA",
					"billing_account":"",
					"first_name":"Jason",
					"last_name":"Tempestini",
					"email":"jason@tempestinis.com",
					"phone":"925-895-4468",
					"company":"Curly Media",
					"address":"1107 Fountain Street",
					"address2":"",
					"city":"Alameda",
					"state":"CA",
					"zip":"94501",
					"country":"US",
					"tpb_company":"",
					"tpb_address":"",
					"tpb_city":"",
					"tpb_state":"",
					"tpb_zip":"",
					"tpb_phone":""
				}
		},

		{
			"billing_company":"Sad New Vistas in Testing",
			"first_name":"Jason",
			"last_name":"Tempestini",
			"phone":"925-895-4468",
			"billing_address":"1107 Fountain Street",
			"billing_address2":"",
			"billing_city":"Alameda",
			"billing_state":"CA",
			"billing_zip":"94501",
			"billing_country":"US",
			"order_reference":"order135",
			"note":"This is a note for this shipment. It really could be quite a long note.\n It might even have carriage returns.",
			"OrderItem":
			[
				{
					"catalog_id":"52",
					"customer_item_code":"",
					"name":"Eucalyptus",
					"quantity":"10"
				},
				{
					"catalog_id":"",
					"customer_item_code":"bc1",
					"name":"Ball Cap",
					"quantity":"10"
				}
			],
			"Shipment":
				{
					"billing":"Sender",
					"carrier":"UPS",
					"method":"1DA",
					"billing_account":"",
					"first_name":"Jason",
					"last_name":"Tempestini",
					"email":"jason@tempestinis.com",
					"phone":"925-895-4468",
					"company":"Curly Media",
					"address":"1107 Fountain Street",
					"address2":"",
					"city":"Alameda",
					"state":"CA",
					"zip":"94501",
					"country":"US",
					"tpb_company":"",
					"tpb_address":"",
					"tpb_city":"",
					"tpb_state":"",
					"tpb_zip":"",
					"tpb_phone":""
				}
		}

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
 
$output = curl_exec($ch);
 
curl_close($ch);
 
echo $output;
?>