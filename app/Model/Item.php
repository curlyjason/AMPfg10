<?php

App::uses('AppModel', 'Model');
App::uses('Available', 'Lib');
App::uses('CakeEvent', 'Event');
App::uses('InventoryEvent', 'Lib');
App::uses('AvailableEntries', 'Lib');
App::uses('CakeSession', 'Model/Datasource');
App::uses('OrderItem', 'Model');

/**
 * Item Model
 *
 * @property Catalog $Catalog
 * @property OrderItem $OrderItem
 */
class Item extends AppModel implements CakeEventListener {

// <editor-fold defaultstate="collapsed" desc="Associations">
	/**
	 * hasMany associations
	 *
	 * @var array
	 */
	public $hasMany = array(
		'Catalog' => array(
			'className' => 'Catalog',
			'foreignKey' => 'item_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Image' => array(
			'className' => 'Image',
			'foreignKey' => 'item_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => array('Image.modified DESC'),
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'OrderItem' => array(
			'className' => 'OrderItem',
			'foreignKey' => 'item_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'ReplenishmentItem' => array(
			'className' => 'ReplenishmentItem',
			'foreignKey' => 'item_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Location' => array(
			'className' => 'Location',
			'foreignKey' => 'item_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Cart' => array(
			'className' => 'Cart',
			'foreignKey' => 'item_id',
			'dependent' => false,
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
	public $belongsTo = array(
		'Vendor' => array(
			'className' => 'Address',
			'foreignKey' => 'vendor_id',
			'conditions' => array(
				'Vendor.type' => 'vendor'
			),
			'fields' => '',
			'order' => ''
		)
	);
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Properties">
	
	/**
	 * data to update 'available' information lines on pages
	 *
	 * @var array
	 */
	public $available = array();

	/**
	 * data to update 'pending' information lines on pages
	 *
	 * @var array
	 */
	public $pending = array();
	
	public $AvailableEntries;

// </editor-fold>
	
	public function __construct() {
		parent::__construct();
		$this->AvailableEntries = new AvailableEntries();
		$this->getEventManager()->attach(new InventoryEvent());
	}


	public function implementedEvents() {
        return array(
            'Model.ReplenishmentItem.quantityChange' => 'pendingChange'
        );
    }

	public function afterSave($created, $options = []) {
		parent::afterSave($created, $options);
		if($created){
		}
	}

	public function logNewItem($data) {
		if ($data['Item']['id'] == '') {
			// assume newly created
			$id = $this->id;
		} else {
			$id = $data['Item']['id'];
		}
		$User = ClassRegistry::init('User');
		$this->Log = ClassRegistry::init('Log');
		$this->Log->create('inventory');
		$this->Log
			->set('event', 'New Item')
			->set('customer', $this->discoverCustomerUserId($id))
			->set('id', $id)
			->set('name', $data['Item']['name'])
			->set('from', '0')
			->set('to', $data['Item']['quantity'])
			->set('change', $data['Item']['quantity'])
			->set('number', 'CREATION')
			->set('by', $User->discoverName(CakeSession::read("Auth.User.id")));
		$this->Log->toString();
		CakeLog::write('inventory', $this->Log->logLineOut);

	}
	/**
	 * Return the building => building list
	 * 
	 * File saved in webroot/files/locations.txt
	 * "\n" is line delimeter
	 * 
	 * @return array suitable for select list
	 */
	function getLocations() {
		$file = new File(WWW_ROOT . 'files/locations.txt');

		$buildingList = $this->parseBuildingFile($file);

		return $buildingList;
	}

	/**
	 * Parse the found building list into a select list
	 * 
	 * Optionally, if no file is provided, return an empty string
	 * 
	 * @param string $file The file found
	 * @return array The select list of buildings
	 */
	private function parseBuildingFile($file) {
		if ($file->exists()) {
			$content = explode("\n", $file->read());

			foreach ($content as $entry) {
				$parsedList[$entry] = $entry;
			}
		} else {
			$parsedList = '';
		}
		return $parsedList;
	}

	public function manageUncommittedSeries($list = array()) {
		if (empty($list) || !is_array($list)) {
			return;
		}
		foreach ($list as $id) {
			$this->manageUncommitted($id);
		}
		return $this->available;
	}

	/**
	 * Calculate the uncommited quantity of an item
	 * 
	 * @param string $id The item to opperate on
	 * @return array The item id and uncommited quantity
	 */
	public function manageUncommitted($id) {

        // <editor-fold defaultstate="collapsed" desc="Initial Query">
		// Get commitments for this item
		$item = $this->find('first', [
			'conditions' => ['Item.id' => $id],
			'fields' => [
				'id',
				'quantity',
				'available_qty'
			],
            'contain' => [
				'Cart' => [
					'fields' => [
						'SUM(each_quantity)'
					]],
				'OrderItem' => [
					'fields' => ['SUM(each_quantity)'],
					'conditions' => ['pulled' => 0],
				],
				'Catalog' => [
					'fields' => [
						'Catalog.id',
						'Catalog.item_id',
						'Catalog.sell_unit',
						'Catalog.sell_quantity',
						'Catalog.type'
					],
					'Item',
					'ParentCatalog' => [
						'fields' => [
							'ParentCatalog.id',
							'ParentCatalog.item_id',
							'ParentCatalog.type',
							'ParentCatalog.sell_unit',
							'ParentCatalog.sell_quantity'
						],
						'Item'
					],
					'ChildCatalog' => [
						'fields' => [
							'ChildCatalog.id',
							'ChildCatalog.item_id',
							'ChildCatalog.sell_unit',
							'ChildCatalog.sell_quantity',
							'ChildCatalog.type'
						],
						'Item'
					]
				]
			]
		]);
        // </editor-fold>

		// Get commitmetns that are on backorders
		$backorder = $this->OrderItem->Order->find('all', array(
			'conditions' => array(
				'status' => 'Backordered',
                'id' => $id
			),
			'fields' => ['id'],
			'contain' => [
				'OrderItem' => [
//					'conditions' => [
//						'item_id' => $id
//					],
//					'group' => ['id'],
					'fields' => [
						'order_id',
						'SUM(each_quantity)'
					]
				]
			])
		);
		$cartCommit = $orderCommit = $backordered = 0;

		// extract the quantity on Cart
		if (isset($item['Cart'][0]['Cart'][0]['SUM(each_quantity)'])) {
			$cartCommit = $item['Cart'][0]['Cart'][0]['SUM(each_quantity)'];
		}

		// extract the quantity on Orders
		if (isset($item['OrderItem'][0]['OrderItem'][0]['SUM(each_quantity)'])) {
			$orderCommit = $item['OrderItem'][0]['OrderItem'][0]['SUM(each_quantity)'];
		}

		// extract the quantity on Backorders
		if (isset($backorder[0]['OrderItem'])) {
			foreach ($backorder as $order) {
				if (isset($order['OrderItem'][0]['OrderItem'][0]['SUM(each_quantity)'])) {
					$backordered += $order['OrderItem'][0]['OrderItem'][0]['SUM(each_quantity)'];
				}
			}
		}
	
		// calculate the avaialbe amount
		$available = $item['Item']['quantity'] - $cartCommit - $orderCommit + $backordered;
		$this->create();
		$data = ['Item' => [
                'id' => $id,
                'available_qty' => $available
            ]];
		$saveResult = $this->save($data);
		foreach ($item['Catalog'] as $product) {
			//switch on Catalog type 
			if($product['type'] & COMPONENT){
				$this->addComponentAvailableEntry($product, $available);
			} elseif ($product['type'] & KIT){
				$this->addKitAvailableEntry($product, $available);
			} else {
				$this->addAvailableEntry($product, $available);
			}
		}

        return ($this->available);
	}
	
	/**
	 * Create two `Item`.`available` entries for a component type item
	 * 
	 * Create available entry for the component and its parent kit
	 * 
	 * @param array $product
	 */
	private function addComponentAvailableEntry($product, $available) {
		//set component availability
		$product['Item']['available_qty'] = $available;
		$available = $this->Catalog->deriveKitOrComponentAvailability($product);
		$this->addAvailableEntry($product, $available);

		
		//set kit availability
		$available = $this->Catalog->deriveKitOrComponentAvailability($product['ParentCatalog']);
		$this->addAvailableEntry($product['ParentCatalog'], $available);
	}

	/**
	 * Create many available entries for a kit type item
	 * 
	 * Create available entry for the kit and all its child components
	 * 
	 * @param array $product
	 */
	private function addKitAvailableEntry($product, $available) {
		$product['Item']['available_qty'] = $available;
		//set kit availability
		$available = $this->Catalog->deriveKitOrComponentAvailability($product);
		$this->addAvailableEntry($product, $available/$product['sell_quantity']);
		
		//set each kit availability
		foreach ($product['ChildCatalog'] as $key => $component) {
			$available = $this->Catalog->deriveKitOrComponentAvailability($component);
			$this->addAvailableEntry($component, $available);
		}
	}

	/**
	 * Create a single entry in the available array
	 * 
	 * Saves data to the $this->available property
	 * 
	 * @param array $product
	 */
	private function addAvailableEntry($product, $available) {
		// need an accumulation of these Avaialable objects (an object full of objects)
		// so a new accum class must be written to do the job
		$a = new Available($product, $available);
		
		// this builds the backward-compatible array so our code doesn't break
		// it replaces the REM'd code at the end of this method
		$this->available['Available'][$a->productKey()] = $a->domVals();
		
		$this->AvailableEntries->append($a);
	}

	/**
	 * Event handler to keep Item.pending_qty up to date
	 * 
	 * @param type $subject
	 * @return array 
	 */
	public function pendingChange($subject) {
		foreach ($subject->data as $id) {
			$this->managePendingQty($id);
		}
		return $this->pending;
	}

	/**
	 * Calculate the on_order qty (on-hand plus replenishment/po) of an item
	 * 
	 * pending = Item.qty + sum( (each(ReplenIt.qty * Replen.po_qty) / Item.inv_qty) )
	 * 
	 * @param string $id The item to opperate on
	 * @return array The item id and calc'd on_order quantity
	 */
	public function managePendingQty($id) {

		if (!$id || !isset($id) || !$this->exists($id)) {
			return array('available' => false, 'availableItem' => $id);
			;
		}

		// Get commitments for this item
		$item = $this->find('first', array(
			'conditions' => array(
				'Item.id' => $id),
			'fields' => array(
				'id',
				'quantity',
				'pending_qty'
			),
			'contain' => array(
				'Catalog',
				'ReplenishmentItem' => array(
					'fields' => array(
						'id',
						'SUM(quantity * po_quantity)'
					),
					'conditions' => array(
						'pulled' => 0
					),
				)
			)
		));
		// we always have at least the items in stock
		$pending = 0;

		// add to the inventory count, the extracted quantity in active ReplenishmentItems
		if (isset($item['ReplenishmentItem'][0]['ReplenishmentItem'][0]['SUM(quantity * po_quantity)'])) {
			$pending += $item['ReplenishmentItem'][0]['ReplenishmentItem'][0]['SUM(quantity * po_quantity)'];
		}
		$pending += $item['Item']['quantity'];

		// Save the calculated the pending amount
		$this->save(array(
			'Item' => array(
				'id' => $id,
				'pending_qty' => $pending
			)
		));
		
		// Now build the array that can guide update of the page
		// 
		// Build the Item level entry which is always 1 each
		$this->pending['Pending'][] = array(
			"-I{$item['Item']['id']}-C-",
			$pending,
			1,
			'ea'
		);
			
		foreach ($item['Catalog'] as $product) {
			
			if ($product['type'] & KIT) {
				// read in the COMPONENTS and process them
			}
			
			if ($product['type'] & COMPONENT) {
				// read in the KIT and process it
			}			
			
			$this->makeOnePendingEntry($pending, $product);
		}

		return $this->pending;
	}
	
	/**
	 * Make an array entry that will allow js updates of pending items
	 * 
	 * @param int $pending
	 * @param array $product
	 */
	private function makeOnePendingEntry($pending, $product) {
		$this->pending['Pending'][] = array(
			"-I{$product['item_id']}-C{$product['id']}-",
			$pending / $product['sell_quantity'],
			$product['sell_quantity'],
			$product['sell_unit'],
			$product['name']
		);
	}

	/**
	 * Get items at, below, or near reorder levels
	 * 
	 * $within boosts reorder trigger level to return
	 * Items near reorder point by this user tunable factor
	 * 
	 * @param float $within increase the reorder level by this amount
	 * @param boolean $group Group result by vendor
	 */
	public function needsReorder($within = 0, $group = TRUE) {
		$percent = $within / 100;

		$items = $this->find('all', array(
			'conditions' => array(
				'`Item`.`pending_qty` + `Item`.`available_qty` <= `Item`.`reorder_level` + (`Item`.`reorder_level` * ' . $within . ')',
				'Item.active' => 1
			),
			'contain' => array(
				'Vendor'
			),
			'order' => array(
				'Item.vendor_id ASC',
				'Item.name')
		));

		$vendors = $this->Vendor->find('all', array(
			'conditions' => array('Vendor.type' => 'vendor')
		));

		$existing = $otherVendors = array();

		if ($group) {
			$count = 0;
			foreach ($items as $index => $item) {
				$vendor = empty($item['Vendor']['name']) ? 'X' : $item['Vendor']['name'];

				$existing[$item['Vendor']['id']] = $item['Vendor']['id']; //to filter the full vendo list

				$item['Item']['index'] = $item['Item']['id'];
				$lowStock[$vendor][] = $item;
				$itemData[$item['Item']['id']] = $item;
			}
		}

		foreach ($vendors as $index => $vendor) {
			$vendorAccess[$vendor['Vendor']['id']] = $vendor['Vendor'];
			if (!isset($existing[$vendor['Vendor']['id']])) {
				$otherVendors[] = $vendor;
			}
		}
		$itemData['vendorAccess'] = $vendorAccess;
		return compact('lowStock', 'itemData', 'otherVendors');
	}
	
	public function findItemsByQuery($query) {
		return $this->find('all', array(
			'conditions' => array(
				'Item.active' => 1,
				'OR' => array(
					'Item.name LIKE' => "%{$query}%",
					'Item.description LIKE' => "%{$query}%"
				)
			),
			'contain' => FALSE
		));
	}
	
	/**
	 * Given itemId, find Customer User Id.
	 * 
	 * @param string $itemId
	 * @return string customer User Id or error statements for bad finds
	 */
	public function discoverCustomerUserId($itemId) {
		$ancestors = $this->Catalog->field('ancestor_list', array('Catalog.item_id' => $itemId));
		if(!$ancestors){
			return 'NoAncestor';
		}
		$a = explode(',', trim($ancestors, ','));
		$customerUserId = $this->Catalog->field('customer_user_id', array('Catalog.id' => $a[1]));
		if(!$customerUserId){
			return 'NoCUid';
		}
		return $customerUserId;
	}
	
	public function customerInventorySnapshot($customerUserId){
		$customerRootNode = $this->Catalog->find('first', array(
			'conditions' => array(
				'Catalog.customer_user_id' => $customerUserId
			)
		));
		$customerNodeId = $customerRootNode['Catalog']['id'];
		$nodes = $this->Catalog->getFullNode($customerNodeId, FALSE);
		$items = array();
		$templateItem = array(
			'id' => NULL,
			'customer_item_code' => NULL,
			'name' => 'Eucalyptus, small grove',
			'quantity' => 0,
			'available_qty' => 0,
			'pending_qty' => 0
		);
		foreach ($nodes as $key => $node) {
			if ($node['Item']['id'] != NULL) {
				$trimNode = array_intersect_key($node['Item'], $templateItem);
				$items[$node['Item']['id']] = $trimNode;
			}			
		}
		$inventory = array(
			'Items' => array(
				'Item' => array(
				)));
		foreach ($items as $key => $item) {
			$inventory['Items']['Item'][] = $item;
		}
		return $inventory;
	}

	/**
	 * Set Item to active/inactive based on its owner Catalogs states
	 * 
	 * If this process fails, I'm considering it a not-fatal data state 
	 * so I'm not halting with Exceptions or returning indicator values
	 * 
	 * @param int $id
	 */
	public function setActiveSate($id) {
		$i = $this->find('first', array(
			'conditions' => array('Item.id' => $id),
			'fields' => array('Item.id', 'Item.active'),
			'contain' => array(
				'Catalog' => array(
					'fields' => array('Catalog.active'),
					'conditions' => array('Catalog.active' => TRUE)
				)
			)
		));
		if ($i) {
			if (empty($i['Catalog'])) {
				unset($i['Catalog']);
				$i['Item']['active'] = 0;
			} else {
				unset($i['Catalog']);
				$i['Item']['active'] = 1;
			}
			$this->save($i);
		}
	}
	
	/**
	 * Fetch item and related records for item history
	 * 
	 * Function also merges appropriate order information into order items
	 * 
	 * @param int $id the item id
	 * @return array
	 */
	public function fetchItemHistory($id) {
		$item = $this->find('first', array(
			'conditions' => array(
				'Item.id' => $id
			),
			'fields' => array(
				'Item.id',
				'Item.item_code',
				'Item.customer_item_code',
				'Item.name',
				'Item.description',
				'Item.quantity',
				'Item.reorder_qty',
				'Item.available_qty',
				'Item.pending_qty',
				'Item.reorder_level',
				'Item.minimum',
				'Item.active'
			),
			'contain' => array(
				'Catalog' => array(
					'fields' => array(
						'Catalog.id',
						'Catalog.item_id',
						'Catalog.name',
						'Catalog.active',
						'Catalog.customer_user_id',
						'Catalog.sell_quantity',
						'Catalog.sell_unit',
						'Catalog.description',
						'Catalog.type',
						'Catalog.item_code',
						'Catalog.customer_item_code'
					)
				),
				'OrderItem' => array(
					'fields' => array(
						'OrderItem.id',
						'OrderItem.item_id',
						'OrderItem.order_id',
						'OrderItem.quantity',
						'OrderItem.sell_quantity',
						'OrderItem.each_quantity',
						'OrderItem.sell_unit'
						),
					'Order' => array(
						'fields' => array(
							'Order.id',
							'Order.order_number',
							'Order.created',
							'Order.status'
						),
					'conditions' => array(
						'Order.status IN' => array('Submitted', 'Released', 'Approved')
						),
					)
				),
				'ReplenishmentItem' => array(
					'fields' => array(
						'ReplenishmentItem.id',
						'ReplenishmentItem.item_id',
						'ReplenishmentItem.replenishment_id',
						'ReplenishmentItem.quantity'
						),
					'Replenishment' => array(
						'fields' => array(
							'Replenishment.id',
							'Replenishment.order_number',
							'Replenishment.created',
							'Replenishment.status'
						),
						'conditions' => array(
							'Replenishment.status IN' => array('Open', 'Placed')
						)
					)
				),
				'Cart' => array(
					'fields' => array(
						'Cart.id',
						'Cart.item_id',
						'Cart.catalog_id',
						'Cart.created',
						'Cart.quantity',
						'Cart.sell_quantity',
						'Cart.each_quantity',
						'Cart.sell_unit',
						'Cart.user_id'
					)
				)
			)
		));
		$i = $item['OrderItem'];
		foreach ($i as $index => $oI) {
			if($oI['Order'] == array()){
				unset($item['OrderItem'][$index]);
			} else {
			$item['OrderItem'][$index]['created'] = $oI['Order']['created'];
			$item['OrderItem'][$index]['order_number'] = $oI['Order']['order_number'];
			$item['OrderItem'][$index]['status'] = $oI['Order']['status'];
			unset($item['OrderItem'][$index]['Order']);
			}
		}
		$r = $item['ReplenishmentItem'];
		foreach ($r as $index => $rI) {
			if($rI['Replenishment'] == array()){
				unset($item['ReplenishmentItem'][$index]);
			} else {
			$item['ReplenishmentItem'][$index]['created'] = $rI['Replenishment']['created'];
			$item['ReplenishmentItem'][$index]['order_number'] = $rI['Replenishment']['order_number'];
			$item['ReplenishmentItem'][$index]['status'] = $rI['Replenishment']['status'];
			unset($item['ReplenishmentItem'][$index]['Replenishment']);
			}
		}
	return $item;
	}
	
	/**
	 * Return an array of items for a single vendor_id
	 * 
	 * @param int $vendor_id
	 * @param string $status the active status (active, inactive or all)
	 * @return array
	 */
	public function findItemsByVendorId($vendor_id, $status = 'active') {
		//set search conditions based upon desired item state
		if($status == 'active'){
			$conditions = array(
				'Item.vendor_id' => $vendor_id,
				'Item.active' => 1
			);
		} elseif ($status == 'inactive') {
			$conditions = array(
				'Item.vendor_id' => $vendor_id,
				'Item.active' => 0
			);
		} elseif ($status == 'all') {
			$conditions = array(
				'Item.vendor_id' => $vendor_id
			);
		}
		//find the requested items
		$items = $this->find('all', array(
			'conditions' => $conditions,
			'contain' => array(
				'Vendor'
			),
			'order' => array(
				'Item.name')
		));
		
		//grouping and setup from old needs reorder
		$vendors = $this->Vendor->find('all', array(
			'conditions' => array('Vendor.type' => 'vendor')
		));

		$existing = $otherVendors = array();

		$count = 0;
		foreach ($items as $index => $item) {
			$vendor = empty($item['Vendor']['name']) ? 'X' : $item['Vendor']['name'];

			$existing[$item['Vendor']['id']] = $item['Vendor']['id']; //to filter the full vendo list

			$item['Item']['index'] = $item['Item']['id'];
			$lowStock[$vendor][] = $item;
			$itemData[$item['Item']['id']] = $item;
		}

		foreach ($vendors as $index => $vendor) {
			$vendorAccess[$vendor['Vendor']['id']] = $vendor['Vendor'];
			if (!isset($existing[$vendor['Vendor']['id']])) {
				$otherVendors[] = $vendor;
			}
		}
		$itemData['vendorAccess'] = $vendorAccess;
		return compact('lowStock', 'itemData', 'otherVendors');
	}
}