<?php
 //dev
$url = "http://dev.ampfg.com/robotOrders/input/json";

 //prod
//   $url = "https://fg.ampprinting.com/robotOrders/input/json";


$post_data = ['
{
	"Credentials":
		{
			"company":"IfOnly Testing",
			"token":"76be72caa9a4a550ac4593d872f38e0d20618a4a"
		}
	,
	"Orders":
	[
		{
			"billing_company":"If Only",
			"first_name":"Celia",
			"last_name":"Peachey",
			"phone":"518-256-3396",
			"billing_address":"244 Jackson Street, 4th Floor",
			"billing_address2":"",
			"billing_city":"San Francisco",
			"billing_state":"CA",
			"billing_zip":"94111",
			"billing_country":"US",
			"order_reference":"order1233452",
			"note":"This is a note for this shipment. It really could be quite a long note.\n It might even have carriage returns.",
			"OrderItem":
			[
				{
					"catalog_id":"",
					"customer_item_code":"1602",
					"name":"Test Item #1",
					"quantity":"1"
				},
				{
					"catalog_id":"",
					"customer_item_code":"TestItem2",
					"name":"Test Item #2",
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
			"billing_company":"If Only",
			"first_name":"Celia",
			"last_name":"Peachey",
			"phone":"518-256-3396",
			"billing_address":"244 Jackson Street, 4th Floor",
			"billing_address2":"",
			"billing_city":"San Francisco",
			"billing_state":"CA",
			"billing_zip":"94111",
			"billing_country":"US",
			"order_reference":"order1233462",
			"note":"This is a note for this shipment. It really could be quite a long note.\n It might even have carriage returns.",
			"OrderItem":
			[
				{
					"catalog_id":"1602",
					"customer_item_code":"",
					"name":"Test Item #1",
					"quantity":"10"
				},
				{
					"catalog_id":"",
					"customer_item_code":"TestItem2",
					"name":"Test Item #2",
					"quantity":"50"
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