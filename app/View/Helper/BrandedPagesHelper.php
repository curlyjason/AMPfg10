<?php
/**
 * CakePHP BrandedPagesHelper
 * @author dondrake
 */
class BrandedPagesHelper extends AppHelper
{

	public function __construct(View $View, $settings = array())
	{
		parent::__construct($View, $settings);
	}

	public function header($variant)
	{
		return $this->_View->element('BrandedPages/header_brand', ['variant' => $variant]);
	}
	
	public function brand()
	{
		return 'AMP FG System';
	}
	
	public function logo()
	{
		return 'AMP_PrintResp_logo_300.png';
	}
	
//	array(
//	'Config' => array(
//		'userAgent' => '1e2bdab7acd6269b6aa942f70415d682',
//		'time' => (int) 1555039416,
//		'countdown' => (int) 10
//	),
//	'Auth' => array(
//		'User' => array(
//			'id' => '3',
//			'email' => null,
//			'first_name' => 'Don',
//			'last_name' => 'Drake',
//			'active' => true,
//			'username' => 'ddrake@dreamingmind.com',
//			'role' => 'Admins Manager',
//			'created' => '2013-12-05 10:17:29',
//			'modified' => '2019-04-11 16:21:56',
//			'parent_id' => '11',
//			'ancestor_list' => ',1,11,',
//			'lock' => '0',
//			'sequence' => '3',
//			'folder' => false,
//			'session_change' => false,
//			'verified' => false,
//			'logged_in' => '0',
//			'cart_session' => null,
//			'use_budget' => true,
//			'budget' => '1500',
//			'use_item_budget' => true,
//			'item_budget' => '15',
//			'rollover_item_budget' => false,
//			'rollover_budget' => false,
//			'use_item_limit_budget' => true,
//			'name' => 'Don Drake',
//			'ParentUser' => array(
//				'password' => '*****',
//				'id' => '11',
//				'email' => null,
//				'first_name' => '',
//				'last_name' => '',
//				'active' => true,
//				'username' => 'Developer Staff',
//				'role' => 'Admins Manager',
//				'created' => '2014-01-27 14:50:22',
//				'modified' => '2015-04-13 10:43:07',
//				'parent_id' => '1',
//				'ancestor_list' => ',1,',
//				'lock' => '0',
//				'sequence' => '5',
//				'folder' => true,
//				'session_change' => false,
//				'verified' => false,
//				'logged_in' => '0',
//				'cart_session' => null,
//				'use_budget' => false,
//				'budget' => null,
//				'use_item_budget' => false,
//				'item_budget' => null,
//				'rollover_item_budget' => false,
//				'rollover_budget' => false,
//				'use_item_limit_budget' => false,
//				'name' => ''
//			),
//			'Customer' => array(
//				'id' => null,
//				'user_id' => null,
//				'created' => null,
//				'modified' => null,
//				'customer_code' => null,
//				'order_contact' => null,
//				'billing_contact' => null,
//				'allow_backorder' => null,
//				'allow_direct_pay' => null,
//				'address_id' => null,
//				'release_hold' => null,
//				'taxable' => null,
//				'rent_qty' => null,
//				'rent_unit' => null,
//				'rent_price' => null,
//				'item_pull_charge' => null,
//				'order_pull_charge' => null,
//				'token' => null,
//				'customer_type' => null,
//				'name' => 'ddrake@dreamingmind.com',
//				'role' => 'Admins Manager'
//			),
//			'group' => 'Admins',
//			'access' => 'Manager',
//			'CatalogRoots' => array(
//				(int) 1 => array(
//					'id' => '1',
//					'created' => null,
//					'modified' => '2014-10-16 08:17:40',
//					'item_id' => null,
//					'name' => 'root',
//					'parent_id' => '-1',
//					'ancestor_list' => ',',
//					'item_count' => null,
//					'lock' => '0',
//					'sequence' => '1',
//					'active' => true,
//					'customer_id' => null,
//					'customer_user_id' => null,
//					'sell_quantity' => null,
//					'sell_unit' => null,
//					'max_quantity' => null,
//					'price' => null,
//					'description' => null,
//					'type' => '2',
//					'item_code' => '',
//					'customer_item_code' => null,
//					'comment' => null,
//					'folder' => '2',
//					'kit' => '0',
//					'product_test' => '0',
//					'CatalogsUser' => array(
//						'id' => '6',
//						'created' => null,
//						'modified' => '0000-00-00 00:00:00',
//						'catalog_id' => '1',
//						'user_id' => '3'
//					)
//				)
//			),
//			'UserRoots' => array(
//				(int) 1 => array(
//					'password' => '*****',
//					'id' => '1',
//					'email' => null,
//					'first_name' => null,
//					'last_name' => null,
//					'active' => true,
//					'username' => 'root',
//					'role' => 'Clients Guest',
//					'created' => null,
//					'modified' => '2015-04-13 13:40:04',
//					'parent_id' => '-1',
//					'ancestor_list' => ',',
//					'lock' => '0',
//					'sequence' => '1',
//					'folder' => true,
//					'session_change' => false,
//					'verified' => false,
//					'logged_in' => '0',
//					'cart_session' => null,
//					'use_budget' => false,
//					'budget' => null,
//					'use_item_budget' => false,
//					'item_budget' => null,
//					'rollover_item_budget' => false,
//					'rollover_budget' => false,
//					'use_item_limit_budget' => false,
//					'name' => null,
//					'UsersUser' => array(
//						'id' => '2',
//						'created' => null,
//						'modified' => null,
//						'user_managed_id' => '1',
//						'user_manager_id' => '3'
//					)
//				)
//			),
//			'edit' => array(
//				'mode' => false,
//				'model' => null,
//				'id' => null
//			),
//			'budget_id' => '27'
//		)
//	),
//	'Prefs' => array(
//		'Catalog' => array(
//			'paginationLimit' => '25'
//		),
//		'id' => '52eab79d-5d0c-4bc3-9cb9-05544b77dfb4',
//		'ship' => array(
//			(int) 25 => array(
//				'customer' => array(
//					'carrier' => 'UPS',
//					'method' => 'GND',
//					'billing' => 'Sender'
//				)
//			)
//		),
//		'Search' => array(
//			(int) 0 => 'user',
//			(int) 1 => 'catalog',
//			(int) 2 => 'order',
//			(int) 3 => 'active'
//		)
//	)
//)
	
}
