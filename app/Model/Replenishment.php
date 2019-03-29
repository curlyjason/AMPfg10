<?php

App::uses('AppModel', 'Model');

/**
 * Replenishment Model
 *
 * @property User $User
 * @property Vendor $Vendor
 * @property ReplenishmentItem $ReplenishmentItem
 */
class Replenishment extends AppModel {
	
	/**
	 * provide xml array assembly with an index translation for OrderItems
	 * 
	 * getOrderForPrint() result array is the basis for xml response arrays
	 * but it was a bit clumsy in index construction for OrderItem
	 * data points and their labels. This provides a work-around.
	 *
	 * @var array
	 */
	protected $itemLabel = array(
		1 => 'quantity', 
		2 => 'code',
		3 => 'name'
	);

// <editor-fold defaultstate="collapsed" desc="Validation">
	/**
	 * Validation rules
	 *
	 * @var array
	 */
	public $validate = array(
		'status' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			)
		)
	);
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Associations">
	/**
	 * belongsTo associations
	 *
	 * @var array
	 */
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Vendor' => array(
			'className' => 'Address',
			'foreignKey' => 'vendor_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	/**
	 * hasMany associations
	 *
	 * @var array
	 */
	public $hasMany = array(
		'ReplenishmentItem' => array(
			'className' => 'ReplenishmentItem',
			'foreignKey' => 'replenishment_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);
	// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Properties">
	public $statusOutputReplenishment = array();
	public $queryResult = array();
	public $userQueryResult = array();

// </editor-fold>


	public function getReplenishments($conditions = array()) {
		$default = array('status' => 'open');
		if (!empty($conditions)) {
			$conditions = array_merge($conditions, $defaut);
		} else {
			$conditions = $default;
		}
		$raw = $this->find('all', array('conditions' => $conditions));
		return $raw;
	}

	/**
	 * Return a Replenishment number for record $id
	 * 
	 * Given two values from the Order record
	 * return a unique order number
	 * YYMM-xxxx
	 * YY = last two year digits
	 * MM = two digit month
	 * xxxx = a base 19 number
	 * Base 19 has custom digit set, all caps
	 * 
	 * @param string $id Id of the record to make a number for
	 * @return mixed String = the number (R-YYMM-xxxx), FALSE = failure
	 */
	public function getReplenishmentNumber($id) {
		//setup variables
		$seed = $this->field('order_seed');
		$created = $this->field('created');
		if (!$seed || !$created) {
			return false;
		}
		return 'PO-' . $this->getCodedNumber($seed, $created);
	}

	public function fetchReplenishmentList() {
		$replenishmentList = $this->find('all', array(
			'conditions' => array(
				'Replenishment.status' => array('Placed', 'Completed')
			),
			'contain' => array(
				'User',
				'ReplenishmentItem' => array(
					'Item' => array(
						'Location',
						'Image',
						'Catalog' => array(
							'fields' => array(
								'Catalog.id',
								'Catalog.id AS catalog_id',
								'Catalog.name',
								'Catalog.item_id',
								'Catalog.sell_quantity AS po_quantity',
								'Catalog.sell_unit AS po_unit',
								'Catalog.sell_quantity',
								'Catalog.sell_unit',
								'Catalog.type'
							),
							'ParentCatalog' => array(
								'Item'
							)
						)
					)
		))));
		$this->groupReplenishmentOrders($replenishmentList);
		return $this->statusOutputOrder;
	}


	/**
	 * Add components and availability data to Replenishment products that are kits
	 * 
	 * This is performed for the warehouse status page data
	 * 
	 * @param array $order The replenishment to examine for Kits
	 * @return array The revised order
	 */
	private function injectKitData($order) {
		foreach ($order['ReplenishmentItem'] as $index => $replenItem) {
			foreach ($replenItem['Item']['Catalog'] as $key => $product) {
				if ($product['type'] & KIT) {

					// Found a kit, setup its pending based upon its item and its components
					$this->ReplenishmentItem->Item->Catalog->fetchMaxPendingKit($product['id']);
					$order['ReplenishmentItem'][$index]['Item']['Catalog'][$key]['pending_qty'] = $order['ReplenishmentItem'][$index]['Item']['pending_qty'] + $this->ReplenishmentItem->Item->Catalog->maxPendingKit;


					// Now load up its components, just in case
					$order['ReplenishmentItem'][$index]['Item']['Catalog'][$key]['Components'] = $this->ReplenishmentItem->Item->Catalog->fetchComponents($product['id']);
				
			} else if ($product['type'] & COMPONENT) {

					//Found a component, setup its pending based upon its item and its KIT parent
					$order['ReplenishmentItem'][$index]['Item']['Catalog'][$key]['pending_qty'] =

							($order['ReplenishmentItem'][$index]['Item']['pending_qty'] / $product['sell_quantity']) +

							($product['ParentCatalog']['Item']['pending_qty'] * $product['sell_quantity']);
				} else {

					//Found a product, setup its pending based upon its item
					$order['ReplenishmentItem'][$index]['Item']['Catalog'][$key]['pending_qty'] = $order['ReplenishmentItem'][$index]['Item']['pending_qty'] / $product['sell_quantity'];
				}
			}
		}		return $order;
	}

	public function groupReplenishmentOrders($replenishmentList) {
		//setup default array to force status sort order
		foreach ($replenishmentList as $index => $record) {
			
			// TODO
			// Not sure what this needs to do yet
			$this->injectKitData($record);
			
			//use the property statusOutputOrder which provides a preset array
			$this->statusOutputOrder[$record['Replenishment']['status']][$record['Replenishment']['id']] = $record;
		}
	}

	public function fetchReplenishmentsForStatus() {
		$replenishmentList = $this->find('all', array(
			'conditions' => array(
				'OR' => array(
					'Replenishment.status' => 'Placed',
					'AND' => array(
						'Replenishment.status' => 'Completed',
						'Replenishment.modified >= DATE_ADD(CURDATE(), INTERVAL -14 DAY)'
					),
					'NOT' => array('Replenishment.status' => 'Archived')
				)),
			'contain' => array(
				'User',
				'ReplenishmentItem' => array(
					'Item' => array(
						'Location',
						'Image',
						'Catalog' => array(
							'fields' => array(
								'Catalog.id',
								'Catalog.id AS catalog_id',
								'Catalog.name',
								'Catalog.item_id',
								'Catalog.sell_quantity AS po_quantity',
								'Catalog.sell_unit AS po_unit',
								'Catalog.sell_quantity',
								'Catalog.sell_unit',
								'Catalog.type'
							),
							'ParentCatalog' => array(
								'Item'
						)
					)
		)))));
		$replenishments = $this->statusOutputOrder;
		foreach ($replenishmentList as $replenishment) {
			$replenishments[$replenishment['Replenishment']['status']][$replenishment['Replenishment']['id']] = $replenishment;
		}
		return (!empty($replenishments)) ? $replenishments : array();
	}

//============================================================
// REPLENISHMENT QUERY METHODS for SEARCH
//============================================================

	/**
	 * Find all orders associated with the user's query
	 * 
	 * @param string $query The search string
	 * @param array $archived Does the user want archived orders (TRUE) or not (FALSE)
	 * @return array
	 */
	public function queryReplenishments($query, $archived) {
		if (!$this->User->accessibleUserInList) {
			$this->User->getAccessibleUserInList;
		}
		if ($archived) {
			$statusComparator = '=';
		} else {
			$statusComparator = '!=';
		}

		$baseReplenishmentInList = $this->find('list', array(
			'conditions' => array(
				'Replenishment.status ' . $statusComparator => 'Archived',
				'OR' => array(
					'Replenishment.user_id' => $this->User->accessibleUserInList,
				)
		)));

		//get Replenishments belonging to Users that were found by $query (if any)
		if (!empty($this->User->userQueryReplenishmentInList)) {
			$this->userQueryReplenishments($this->User->userQueryReplenishmentInList);
		}

		//perform find
		$this->queryResult = $this->find('all', array(
			'conditions' => array(
				'Replenishment.id' => $baseReplenishmentInList,
				'OR' => array(
					'Replenishment.order_number LIKE' => "%{$query}%",
					'Replenishment.vendor_company LIKE' => "%{$query}%"
				)
			),
			'contain' => array(
				'User',
				'ReplenishmentItem' => array(
					'Item' => array(
						'Location',
						'Image',
						'Catalog' => array(
							'fields' => array(
								'Catalog.id',
								'Catalog.id AS catalog_id',
								'Catalog.name',
								'Catalog.item_id',
								'Catalog.sell_quantity AS po_quantity',
								'Catalog.sell_unit AS po_unit',
								'Catalog.sell_quantity',
								'Catalog.sell_unit',
								'Catalog.type'
							),
							'ParentCatalog' => array(
								'Item'
						)
					)
		)))));
		//Merge userQueryReplenishments and directly found Replenishments
		$this->queryResult = array_merge($this->userQueryResult, $this->queryResult);

		//Setup array to use standard status page grain
		$this->sortQueryReplenishments();

		//return final sorted orders
		return $this->statusOutputOrder;
	}

	/**
	 * Find all orders belonging to users in the inList
	 * 
	 * @param array $inList The list of user IDs
	 */
	private function userQueryReplenishments($inList) {
		$this->userQueryResult = $this->find('all', array(
			'conditions' => array(
				'Replenishment.id' => $inList
			),
			'contain' => array(
				'User',
				'ReplenishmentItem' => array(
					'Item' => array(
						'Location',
						'Image',
						'Catalog' => array(
							'fields' => array(
								'Catalog.id',
								'Catalog.id AS catalog_id',
								'Catalog.name',
								'Catalog.item_id',
								'Catalog.sell_quantity AS po_quantity',
								'Catalog.sell_unit AS po_unit',
								'Catalog.sell_quantity',
								'Catalog.sell_unit',
								'Catalog.type'
							),
							'ParentCatalog' => array(
								'Item'
						)
					)
		)))));
	}

	/**
	 * Sort function to setup queried orders to use the standard status grain
	 * 
	 */
	private function sortQueryReplenishments() {
		foreach ($this->queryResult as $index => $replenishment) {
			$this->statusOutputOrder[$replenishment['Replenishment']['status']][$replenishment['Replenishment']['id']] = $replenishment;
		}
	}
	
	public function getReplenishmentForPrint($id) {
		// get the replenishment data
		$order = $this->find('first', array(
			'conditions' => array('Replenishment.id' => $id),
			'fields' => array('created', 'order_number', 'total', 'vendor_company',
				'vendor_address', 'vendor_address2', 'vendor_city',
				'vendor_zip', 'vendor_state', 'vendor_country'),
			'contain' => array(
				'User' => array(
					'fields' => array('first_name', 'last_name', 'username')
				),
				'ReplenishmentItem' => array(
					'fields' => array('quantity', 'name',),
					'Item' => array(
						'fields' => array('item_code'),
						'Location'
					)
				)
			)
		));
		
		if(empty($order)){
			return FALSE;
		}
		
		$items = $this->assemblePrintLineItems($order['ReplenishmentItem']);
		$firstPageLines = 27;
		$pg1 = array_slice($items, 0, $firstPageLines);
		if (count($items) > count($pg1)) {
			$chunk = array_chunk(array_slice($items, $firstPageLines, count($items)), 37);
		} else {
			$chunk = array();
		}

		// page the line item arrays
		// first
		$orderedBy = $this->User->discoverName($order['User']['id']);
		$data = array(
			'reference' => array(
				'labels' => array('Date', 'Replenishment'),
				'data' => array(date('m/d/y', time()), $order['Replenishment']['order_number'])
			),
			'items' => $pg1,
			'summary' => array(
				'labels' => array('Placed By', 'Item Count', 'Total'),
				'data' => array(
					$orderedBy, // Ordered By
					count($order['ReplenishmentItem']),		 // Item Count
					$order['Replenishment']['total'])			 // Total
			),
			'headerRow' => array('#', 'Qty', 'Code', 'Name'),
			'chunk' => $chunk,
			'shipping' => array(),
			'billing' => array(
				$order['Replenishment']['vendor_company'],
				$order['Replenishment']['vendor_address'],
				$order['Replenishment']['vendor_address2'],
				"{$order['Replenishment']['vendor_city']} {$order['Replenishment']['vendor_state']} {$order['Replenishment']['vendor_zip']} {$order['Replenishment']['vendor_country']}"
			)
		);
		return $data;
	}
	
}
