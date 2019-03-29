<?php
$url = "http://localhost/amp-fg/notices/input";
// $url = "http://localhost/ampfg/xmlOrders/input";
 $a = array (
	'Processed' => array(
		'joesixpackcom' => array(
			'Action' => array(
				'abcd-1407' => array(
					'message' => array(
						0 => array(
							'var_for_element' => 'value for var',
							'var2_for_element' => 'value for var'
						),
						1 => array(
							'var_for_element' => 'value for var',
							'var2_for_element' => 'value for var'
						),
					)
				),
				'abce-1407' => array()
			),
			'Status' => array(),
			'LowInventory' => array()
		)
	)
);

$b = 		array('jackspratt.com' => array(
			'LowInventory' => array(
				'I99' => array(
					'blah blah blah'
				)
			)
		));
$post_data = array(serialize($b));

// $post_data = array (
//     0 => '');
 
$ch = curl_init();

curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
 
curl_setopt($ch, CURLOPT_URL, $url);

curl_setopt($ch, CURLOPT_HEADER, 'multipart/form-data');
 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// we are doing a POST request
curl_setopt($ch, CURLOPT_POST, 1);
// adding the post variables to the request
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
 
$output = curl_exec($ch);
 
curl_close($ch);
 
echo $output;
?>