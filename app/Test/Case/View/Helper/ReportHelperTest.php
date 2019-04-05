<?php
App::uses('Controller', 'Controller');
App::uses('View', 'View');
App::uses('ReportHelper', 'View/Helper');
App::uses('FgHtmlHelper', 'View/Helper');
App::uses('HtmlHelper', 'View/Helper');
App::uses('ReportOrder', 'Model/Entity');

/**
 * Description of ReportHelperTest
 *
 * @author dondrake
 */
class ReportHelperTest extends CakeTestCase
{
	
	public $ReportHelper;
	
	public $orderId;
	
	public $order;

	public function setUp()
	{
		
        $Controller = new Controller();
        $View = new View($Controller);
        $this->ReportHelper = new ReportHelper($View);
		
		$this->orderId = '5c5dc72e-3f18-4929-a61e-2e0fc0a8651f';

		$this->order = array(
			'Order' => array(
				'id' => '5c5dc72e-3f18-4929-a61e-2e0fc0a8651f',
				'order_seed' => '1600',
				'order_number' => '1902-AEKE',
				'status' => 'Submitted',
				'budget_id' => null,
				'first_name' => 'Jason',
				'last_name' => 'Tempestini',
				'user_id' => '4',
				'email' => 'jason@curlymedia.com',
				'phone' => '925-895-4468',
				'billing_company' => 'Curly Media',
				'billing_address' => '1107 Fountain Street',
				'billing_address2' => '',
				'billing_city' => 'Alameda',
				'billing_zip' => '94501',
				'billing_state' => 'CA',
				'billing_country' => 'US',
				'weight' => '0.00',
				'order_item_count' => '1',
				'subtotal' => '0.00',
				'tax' => '0.00',
				'shipping' => '0.00',
				'total' => '0.00',
				'order_type' => 'creditcard',
				'authorization' => null,
				'transaction' => null,
				'ip_address' => '',
				'created' => '2019-02-08 10:15:10',
				'modified' => '2019-02-08 10:15:12',
				'user_customer_id' => '25',
				'backorder_id' => '',
				'taxable' => false,
				'handling' => '0.00',
				'note' => '',
				'order_reference' => '',
				'ship_date' => null,
				'exclude' => false
			),
			'User' => array(
				'password' => '*****',
				'id' => '4',
				'email' => null,
				'first_name' => 'Jason',
				'last_name' => 'Tempestini',
				'active' => true,
				'username' => 'jason@curlymedia.com',
				'role' => 'Admins Manager',
				'created' => '2013-12-05 10:17:51',
				'modified' => '2019-03-01 13:18:40',
				'parent_id' => '11',
				'ancestor_list' => ',1,11,',
				'lock' => '0',
				'sequence' => '2',
				'folder' => false,
				'session_change' => false,
				'verified' => false,
				'logged_in' => '0',
				'cart_session' => null,
				'use_budget' => false,
				'budget' => null,
				'use_item_budget' => false,
				'item_budget' => null,
				'rollover_item_budget' => false,
				'rollover_budget' => false,
				'use_item_limit_budget' => false,
				'name' => 'Jason Tempestini'
			),
			'UserCustomer' => array(
				'password' => '*****',
				'id' => '25',
				'email' => null,
				'first_name' => null,
				'last_name' => null,
				'active' => true,
				'username' => 'Curly Media',
				'role' => 'Clients Guest',
				'created' => '2014-03-18 13:38:04',
				'modified' => '2019-03-01 13:12:33',
				'parent_id' => '1',
				'ancestor_list' => ',1,',
				'lock' => '0',
				'sequence' => '9',
				'folder' => true,
				'session_change' => false,
				'verified' => false,
				'logged_in' => '0',
				'cart_session' => null,
				'use_budget' => false,
				'budget' => null,
				'use_item_budget' => false,
				'item_budget' => null,
				'rollover_item_budget' => false,
				'rollover_budget' => false,
				'use_item_limit_budget' => false,
				'name' => null,
				'Customer' => array(
					'id' => '5',
					'allow_backorder' => true
				)
			),
			'Budget' => array(
				'id' => null,
				'user_id' => null,
				'use_budget' => null,
				'budget' => null,
				'remaining_budget' => null,
				'use_item_budget' => null,
				'item_budget' => null,
				'remaining_item_budget' => null,
				'budget_month' => null,
				'current' => null,
				'created' => null,
				'modified' => null
			),
			'OrderItem' => array(
				0 => array(
					'id' => '5c5dc72e-360c-44a2-bd38-2e0fc0a8651f',
					'order_id' => '5c5dc72e-3f18-4929-a61e-2e0fc0a8651f',
					'item_id' => '45',
					'name' => 'first item',
					'quantity' => '1',
					'sell_quantity' => '1',
					'each_quantity' => '1',
					'sell_unit' => 'ea',
					'weight' => '0.00',
					'price' => '0.00',
					'subtotal' => '0.00',
					'created' => '2019-02-08 10:15:10',
					'modified' => '2019-02-08 10:15:10',
					'weight_total' => null,
					'pulled' => false,
					'catalog_id' => '52',
					'type' => '4',
					'catalog_type' => '4',
					'sequence' => null,
					'Item' => array(
						'id' => '45',
						'available_qty' => '-50.0'
					),
					'Catalog' => array(
						'id' => '52',
						'created' => '2014-03-18 13:38:48',
						'modified' => '2019-03-30 21:56:07',
						'item_id' => '45',
						'name' => 'first item',
						'parent_id' => '51',
						'ancestor_list' => ',1,51,',
						'item_count' => null,
						'lock' => '0',
						'sequence' => '1',
						'active' => true,
						'customer_id' => null,
						'customer_user_id' => '25',
						'sell_quantity' => '1',
						'sell_unit' => 'ea',
						'max_quantity' => null,
						'price' => '0.00',
						'description' => '',
						'type' => '4',
						'item_code' => '',
						'customer_item_code' => '',
						'comment' => null,
						'folder' => '0',
						'kit' => '0',
						'product_test' => '4',
						'ParentCatalog' => array(
							'id' => '51',
							'created' => '2014-03-18 13:38:05',
							'modified' => '2019-03-30 23:46:03',
							'item_id' => null,
							'name' => 'Curly Media',
							'parent_id' => '1',
							'ancestor_list' => ',1,',
							'item_count' => null,
							'lock' => '0',
							'sequence' => '5',
							'active' => true,
							'customer_id' => '5',
							'customer_user_id' => '25',
							'sell_quantity' => null,
							'sell_unit' => null,
							'max_quantity' => null,
							'price' => '0.00',
							'description' => null,
							'type' => '2',
							'item_code' => '',
							'customer_item_code' => null,
							'comment' => null,
							'folder' => '2',
							'kit' => '0',
							'product_test' => '0',
							'Item' => array()
						),
						'available_qty' => (float) -50
					)
				)
			),
			'Shipment' => array(
				0 => array(
					'id' => '5c5dc72e-f3b8-4560-81b9-2e0fc0a8651f',
					'order_id' => '5c5dc72e-3f18-4929-a61e-2e0fc0a8651f',
					'status' => null,
					'carrier' => 'Other',
					'method' => 'WillCall',
					'first_name' => 'Jason',
					'last_name' => 'Tempestini',
					'email' => 'jason@tempestinis.com, julie@ampprinting.com',
					'phone' => '925-555-9999',
					'company' => 'Curly Media',
					'address' => '2540 25th Ave.',
					'address2' => '',
					'city' => 'Oakland',
					'zip' => '94601',
					'state' => 'CA',
					'country' => 'US',
					'weight' => '0.00',
					'length' => null,
					'width' => null,
					'height' => null,
					'ship_ref1' => '',
					'ship_ref2' => null,
					'ship_ref3' => null,
					'packaging' => null,
					'billing_account' => '',
					'carrier_notes' => null,
					'tracking' => null,
					'shipment_cost' => '0.00',
					'created' => '2019-02-08 10:15:10',
					'modified' => '2019-02-08 10:15:11',
					'tax_jurisdiction' => 'EX',
					'tax_rate_id' => null,
					'tax_percent' => '0.0875',
					'tpb_company' => '',
					'tpb_address' => '',
					'tpb_city' => '',
					'tpb_state' => '',
					'tpb_zip' => '',
					'tpb_phone' => '',
					'billing' => 'Sender',
					'shipment_code' => '1902-AEKE',
					'residence' => false,
					'ups_email1' => 'jason@tempestinis.com',
					'ups_email2' => 'julie@ampprinting.com',
					'ups_email3' => '',
					'ups_flag1' => 'y',
					'ups_flag2' => 'y',
					'ups_flag3' => 'n'
				)
			),
			'Document' => array()
		);

		parent::setUp();
	}

	public function testReportOrder()
	{
		$actual = $this->ReportHelper->reportOrder($this->orderId, (new ReportOrder($this->order)));
		$expected = '<tr><th>Order #</th> <th>Ordered By</th> <th>Date</th> <th>Status</th></tr><tr><td class="Order">1902-AEKE</td> <td>Jason Tempestini</td> <td>2019-02-08</td> <td>Submitted</td></tr>
<tr><td class="spacer"></td> <td colspan="3"><table><tr><th>Item</th> <th>Qty</th> <th>Unit</th> <th>Price</th> <th>Subtotal</th></tr><tr><td class="orderItem">first item</td> <td>1</td> <td>ea</td> <td>0.00</td> <td>0.00</td></tr></table></td></tr>';
		$this->assertTrue($actual === $expected);
	}
	
	public function testReportOrderShippedStatus()
	{
		$this->order['Order']['status'] = 'Shipped';
		$this->order['Shipment'][0]['tracking'] = 'tracking_number';
		
		$actual = $this->ReportHelper->reportOrder($this->orderId, (new ReportOrder($this->order)));
		$expected = '<tr><th>Order #</th> <th>Ordered By</th> <th>Date</th> <th>Status</th></tr><tr><td class="Order">1902-AEKE</td> <td>Jason Tempestini</td> <td>2019-02-08</td> <td>Shipped: tracking_number</td></tr>
<tr><td class="spacer"></td> <td colspan="3"><table><tr><th>Item</th> <th>Qty</th> <th>Unit</th> <th>Price</th> <th>Subtotal</th></tr><tr><td class="orderItem">first item</td> <td>1</td> <td>ea</td> <td>0.00</td> <td>0.00</td></tr></table></td></tr>';
		$this->assertTrue($actual === $expected);
	}
}
