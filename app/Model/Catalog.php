<?php

/**
 * Catalog Model
 *
 * @copyright     Copyright (c) Dreaming Mind (http://dreamingmind.com)
 * @package       app.Model
 */
App::uses('AppModel', 'Model');

/**
 * Catalog Model
 *
 * @package	app.Controller
 * @property Item $Item
 * @property Catalog $ParentCatalog
 * @property Catalog $ChildCatalog
 */
class Catalog extends AppModel {

// <editor-fold defaultstate="collapsed" desc="Associations">
	/**
	 * belongsTo associations
	 *
	 * @var array
	     */
	public $belongsTo = array(
		'Item' => array(
			'className' => 'Item',
			'foreignKey' => 'item_id',
			'counterCache' => true,
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'ParentCatalog' => array(
			'className' => 'Catalog',
			'foreignKey' => 'parent_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'CustomerUser' => array(
			'className' => 'User',
			'foreignKey' => 'customer_user_id',
			'primaryKey' => 'id',
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
		'ChildCatalog' => array(
			'className' => 'Catalog',
			'foreignKey' => 'parent_id',
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

	/**
	 * hasAndBelongsToMany associations
	 *
	 * @var array
	     */
	public $hasAndBelongsToMany = array(
		'User' => array(
			'className' => 'User',
			'joinTable' => 'catalogs_users',
			'foreignKey' => 'catalog_id',
			'associationForeignKey' => 'user_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);
// </editor-fold>
	
// <editor-fold defaultstate="collapsed" desc="Properties">
	public $actsAs = array(
		'ThinTree'
	);

	/**
	 * The logged in user's ID passed from Catalog beforeFilter
	 *
	 * @var string Id of the logged in user
	     */
	public $userId = '';

	/**
	 * UlitimateRoot access indicator
	 * 
	 * Users that have ultimate root access don't need permission
	 * granted for every 'rootless' node they create. This flag
	 * facilitates special handling in afterSave()
	 *
	 * @var boolean Does the logged in user have access to the ultimateRoot
	     */
	public $rootOwner = false;


	/**
	 * The full set of accessible catalogs for a user
	 *
	 * @var array
	 */
	public $accessibleCatalogInList = false;


	/**
	 *
	 * @var int id of the absolute tree root
	     */
	public $ultimateRoot = 1;
    public $validate =
        [
            'parent_id' => [
                'valid' => [
                    'rule' => ['checkListHash', 'Catalog', 'parent_id'],
                    'message' => 'Please enter a valid parent',
                    'allowEmpty' => true
                ]
            ],
            'customer_item_code' => [
                'valid' => [
                    'rule' => ['ensureUniqueCustomerItemCode'],
                    'message' => 'Only unique customer item codes are allowed.',
                    'allowEmpty' => true
                ]
            ]
        ];

	/**
	 * Flag to indicate User has access to new node
	 * 
	 * @var boolean
	     */
	public $refreshPermissions = false; 
	
	public $kit = array();
	public $components = array();
	public $maxKitUp = '';
	public $maxPendingKit = '';
	public $type = array(
		1 => 'Kit',
		2 => 'Folder',
		4 => 'Product',
		8 => 'Component'
	);

// </editor-fold>
	
    function __construct($id = false, $table = null, $ds = null) {
        parent::__construct($id, $table, $ds);		
        $this->virtualFields['folder'] = sprintf('%s.type & %s', $this->alias, FOLDER);
        $this->virtualFields['kit'] = sprintf('%s.type & %s', $this->alias, KIT);
        $this->virtualFields['product_test'] = sprintf('%s.type & %s', $this->alias, PRODUCT);
    }

	/**
     * beforeSave
     * 
     * Call for ThinTree behavior
     * Set Catalog.Name to Item.name if it has no other value
     * 
     * @todo Make the 'name' field setter smart enough to handle changes too
     * @param type $options
     */
    function beforeSave($options = array()) {
        parent::beforeSave($options);
        // make sure new root level trees actually start from our 1 root
        if (isset($this->data[$this->alias]['parent_id']) && $this->data[$this->alias]['parent_id'] == '') {
            $this->data[$this->alias]['parent_id'] = $this->ultimateRoot;
            $this->data[$this->alias]['ancestor_list'] = $this->establishAncestorList($this->ultimateRoot);
            $this->data[$this->alias]['type'] = FOLDER;
	        $maxSequence = $this->getMaxSequence($this->data[$this->alias]['parent_id']);
            $this->data[$this->alias]['sequence'] = $maxSequence[0]['max_sequence_number']+1;
        }
        return true;
    }

    /**
     * If a new root catalog was created give this user permission
	 * 
	 * adjust Item.active based on its parent Catalog.active states
     * 
     * @param type $created
     */
    function afterSave($created, $options = []) {
        parent::afterSave($created, $options);
		// handle permissions for new customer catalogs
        if ($created && $this->data['Catalog']['parent_id'] == $this->ultimateRoot && !$this->rootOwner) {
            $permission = array(
                'User' => array('id' => $this->userId),
            );
            if ($this->save($permission, false)) {
                $this->refreshPermissions = true;
            }
        }
		
		// handle inventory logging for newly created items
		if (isset($this->data['Item']['id']) && $this->data['Item']['id'] == '') {
			$this->Item->logNewItem($this->data);
		}

		if (isset($this->data['Catalog']['item_id']) && isset($this->data['Catalog']['active'])) {
			$this->Item->setActiveSate($this->data['Catalog']['item_id']);
		}
    }

    public function ensureUniqueCustomerItemCode($check)
    {
        if(!isset($this->data['Catalog']['id']) || $this->data['Catalog']['id'] == ''){
            $result = $this->checkNewItemHasUniqueCustomerItemCode($check);
        } else {
            $result = $this->checkExistingItemHasUniqueCustomerItemCode($check);
        }
        return $result;
    }

    private function checkNewItemHasUniqueCustomerItemCode($check)
    {
        $allItems = $this->findAllItemsWith($check['customer_item_code']);
        return empty($allItems);
    }

    private function checkExistingItemHasUniqueCustomerItemCode($check)
    {
        $allItems = $this->findAllItemsWith($check['customer_item_code']);
        if(empty($allItems)){
            $result = true;
        } elseif($allItems['Catalog']['id'] == $this->data['Catalog']['id']){
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }

    private function findAllItemsWith($customerItemCode)
    {
        return $this->find('first', [
            'conditions' => [
                'Catalog.customer_user_id' => $this->data['Catalog']['customer_user_id'],
                'Catalog.customer_item_code' => $customerItemCode
            ]
        ]);

    }

    /**
     * What was this
     * 
     * @param type $id
     */
    function retrieveUserStores($id){
	$conditions = array('Catalog.folder' => 1);
	$stores = $this->getFullNode(1, true, $conditions);
	die;
    }
    
	/**
     * Get an array of catalog ids to use as a query IN condition
     * 
     * If no array of starting nodes is provided, use the accessible Catalog nodes
     * 
     * @param array $nodes An array of user nodes that are the source for the IN list collection
     * @return array The IN list of user ids to use as a query condition
     */
    public function getAccessibleCatalogInList($nodes = false){
	if (!$nodes) {
	    $nodes = CakeSession::read('Auth.User.CatalogRoots');
	}
	$this->accessibleCatalogInList = array();
	if($nodes && !empty($nodes)) {
	    $catalogRoots = array_keys($nodes);
	    foreach ($catalogRoots as $id){
			$this->accessibleCatalogInList[$id] = $id;
	    }
	    $options = array(
			'fields' => array(
				'Catalog.id'
			),
			'contain' => false
	    );
	    $catalogSet = array();
	    foreach($catalogRoots as $root){
			$catalogSet = array_merge($catalogSet, $this->getDecendents($root, false, array(), $options));
	    }
	    foreach($catalogSet as $index => $one){
			$this->accessibleCatalogInList[$one['Catalog']['id']] = $one['Catalog']['id'];
	    }
	}
	return $this->accessibleCatalogInList;
    }

	public function getAccessibleCatalogNodes($rootNodes, $conditions = array()) {
        if (empty($rootNodes)) {
            return array();
        }
        // Pull all the allowed catalog records from all allowed nodes as a flat array
        $assembleFlatNodes = array();
        foreach ($rootNodes as $key => $value) {
            $assembleFlatNodes = array_merge($assembleFlatNodes, ($this->getFullNode($value['id'], false, $conditions)));
        }
        return $assembleFlatNodes;
    }

	/**
	 * Do a content search on Catalog entries
	 * 
	 * @param string $query The search string
	 * @papam boolean $active Find active or inactive catalog items
	 * @return array The found records
	 */
	public function queryCatalog($query, $active){
		if (!$this->accessibleCatalogInList) {
			$this->getAccessibleCatalogInList();
		}
		//perform find
		$catalogs = $this->find('all', array(
			'conditions' => array(
				'Catalog.active' => $active,
				'Catalog.id' => $this->accessibleCatalogInList,
				'Catalog.type NOT' => FOLDER,
				'OR' => array(
					'Item.name LIKE' => "%{$query}%",
					'Item.description LIKE' => "%{$query}%",
					'Catalog.name LIKE' => "%{$query}%",
					'Catalog.description LIKE' => "%{$query}%"
				)
			),
			'order' => 'Catalog.sequence ASC',
			'contain' => array(
				'Item' => array(
					'Image',
					'OrderItem',
					'ReplenishmentItem'
				),
				'ParentCatalog'
			)
		));
		foreach ($catalogs as $index => $catalog) {
			if (!empty($catalog['Item']['OrderItem'])){
				foreach ($catalog['Item']['OrderItem'] as $i => $item) {
					$this->User->userQueryOrderInList[$item['order_id']] = $item['order_id'];
				}
			} elseif(!empty($catalog['Item']['ReplenishmentItem'])){
				foreach ($catalog['Item']['ReplenishmentItem'] as $i => $item) {
					$this->User->userQueryReplenishmentInList[$item['replenishment_id']] = $item['replenishment_id'];
				}
			}			
		}
		return $catalogs;
	}
	
	/**
	 * Give a Catalog array node and attached Item, return available based on Catalog.type
	 * 
	 * @param array $catalog A catalog array node with an attached Item
	 * @return boolean|int the avaialble qty for the kit or component or product
	 */
	public function deriveKitOrComponentAvailability($catalog) {
		$available_qty = false;
		
		if ($catalog['type'] & KIT) {
			// Found a kit. Store its 'available' quantity
			$this->fetchMaxKitUp($catalog['id']);
			$available_qty = $catalog['Item']['available_qty'] + $this->maxKitUp;
			
		} elseif ($catalog['type'] & COMPONENT) {
			// found a component.
			// Read its parent Kit and caluculate the Break-kit value
			$parent =  $this->find('first', array(
				'conditions' => array(
					'Catalog.id' => $catalog['parent_id'],
					'Catalog.active' => 1
				),
				'fields' => array(
					'id'
				),
				'contain' => array(
					'Item' => array(
						'fields' => '*'
					)
				)
			));	
		
			$available_qty = ($catalog['Item']['available_qty']/$catalog['sell_quantity']) + ($parent['Item']['available_qty']);
			
		} elseif ($catalog['type'] & PRODUCT) {
			// or it's a product
			$available_qty = ($catalog['Item']['available_qty']/$catalog['sell_quantity']);
		}
		
		return $available_qty;
	}

	/**
	 * Fetch the kit record itself
	 * 
	 * set $this->kit
	 * @param string $kitId
	 * @return array
	 */
	public function fetchKit($kitId) {
		$this->kit = $this->find('first', array(
			'conditions' => array(
				'Catalog.id' => $kitId,
				'Catalog.active' => 1,
				'Catalog.type & 1'
			),
			'contain' => array(
				'Item'
			)
		));
		
		return $this->kit;		
	}
	
	/**
	 * Fetch the childern of the provided kit
	 * Set to the 'components' property
	 * 
	 * @param string $kitId
	 */
	public function fetchComponents($kitId) {
		$this->components = $this->find('all', array(
			'conditions' => array(
				'Catalog.parent_id' => $kitId,
				'Catalog.active' => 1
			),
			'fields' => array(
				'*',
				'`Item`.`available_qty` / `Catalog`.`sell_quantity` AS `available_qty`',
				'`Item`.`pending_qty` / `Catalog`.`sell_quantity` AS `pending_qty`'
			),
			'contain' => array(
				'Item' => array(
					'fields' => array(
						'*'						
					),
					'Image' 
				),
				'ParentCatalog' => array(
					'Item'
				)
			)
		));	
		
		foreach ($this->components as $index => $catalog) {
			$this->components[$index]['Catalog']['available_qty'] = $catalog[0]['available_qty'] + ($catalog['ParentCatalog']['Item']['available_qty']);
			$this->components[$index]['Catalog']['pending_qty'] = $catalog[0]['pending_qty'] + ($catalog['ParentCatalog']['Item']['pending_qty'] * $catalog['Catalog']['sell_quantity']);
			unset($this->components[$index][0]);
		}
		
		return $this->components;
	}
	
	/**
	 * Discover the Max number of kits that can be made from the available components
	 * 
	 * The Max number of kits will be limited by the component with the least inventory
	 * 
	 * @param string $kitId
     * @return null|string
	 */
	public function fetchMaxKitUp($kitId) {
		$min = $this->find('all', [
			'conditions' => [
				'Catalog.parent_id' => $kitId,
				'Catalog.active' => 1
			],
			'contain' => ['Item'],
			'group' => ['Item.id'],
			'fields' => [
				'MIN(`Item`.`available_qty` / `Catalog`.`sell_quantity`) AS `min`'
			]
		]);
		$this->maxKitUp = empty($min[0][0]['min']) ? 0 : $min[0][0]['min'];
		
		return $this->maxKitUp;
	}
	
	/**
	 * Discover the Max number of kits that are pending based upon the pending of the kits components
	 * 
	 * The Max number of kits will be limited by the component with the least inventory
	 * 
	 * @param string $kitId
	 */
	public function fetchMaxPendingKit($kitId) {
		$min = $this->find('all', array(
			'conditions' => array(
				'Catalog.parent_id' => $kitId,
				'Catalog.active' => 1
			),
			'contain' => array(
				'Item'
			),
			'fields' => array(
				'MIN(`Item`.`pending_qty` / `Catalog`.`sell_quantity`) AS `min`'
			)
		));
		$this->maxPendingKit = ($min[0][0]['min'] === NULL) ? 0 : $min[0][0]['min'];
		
		return $this->maxPendingKit;
	}
	
	/**
	 * Fetch the customer user id based upon any catalog id
	 * 
	 * @param string $catalogId
	 */
	public function fetchCustomerUserId($catalogId) {
		if(!$this->exists($catalogId)){
			return FALSE;
		}
		$ancestorList = $this->field('ancestor_list', array('id' => $catalogId));
		$ancestorListArray = explode(',', $ancestorList);
		$customerCatalogId = ($ancestorListArray[2] == '') ? $catalogId : $ancestorListArray[2];
		return $this->field('customer_user_id', array('id' => $customerCatalogId));
	}
	
	/**
	 * Add Customer name data to array of catalogs
	 * 
	 * array = {n}.Catalog.fields
	 * requires {n}.Catalog.ancestor_list with at least 2 ancestors
	 * 
	 * @param array $catalogs
	 */
	public function injectCustomerName($catalogs = array()) {
		if (empty($catalogs)) {
			return $catalogs;
		}
		
		$history = array();
		$limit = count($catalogs);
		$i = 0;
		
		while ($i < $limit) {
			$cat = true;
			$a = explode(',', $catalogs[$i]['Catalog']['ancestor_list']);
			$parent = $a[2];
			if (!isset($history[$a[2]])) {
				$cat = $this->find('first', array(
					'conditions' => array('Catalog.id' => $a[2]),
					'fields' => array('Catalog.id', 'Catalog.customer_user_id'),
					'contain' => array(
						'CustomerUser' => array(
							'fields' => array('CustomerUser.username')
						)
					)
				));
				$history[$a[2]] = $cat['CustomerUser']['username'];
			}			
			
			if ($cat) {
				$catalogs[$i]['Catalog']['customer_name'] = $history[$a[2]];
			}
			
			$i++;
		}
		return $catalogs;
	}


	/**
	 * Assemble Item data for a customer
	 * 
	 * This is used for the inventory report
	 * 
	 * @param string $id user id (of a customer user)
	 * @return mixed False or items array
	 */
	public function allItemsForCustomer($id) {		
		$items = $this->find('all', array(
			'conditions' => array(
				'Catalog.customer_user_id' => $id,
				'Catalog.type !=' => FOLDER
			),
			'fields' => array('Catalog.id', 'Catalog.type'),
			'contain' => array(
				'Item' => array('fields' => '*')
			)
		));
		
		if (empty($items)) {
			return $items;
		}
		
		$reportItems = array();
		foreach ($items as $item) {
			$reportItems[$item['Item']['id']] = $item['Item'];
			$reportItems[$item['Item']['id']]['Type'][] = $this->type[$item['Catalog']['type'] & (FOLDER | KIT | COMPONENT | PRODUCT)];
		}
		return ($reportItems);
	}
	
	/**
	 * Add component grain and availability data to any kits on this shopping page
	 * 
	 * @param array $products The product array for a shopping page
	 * @return array The shopping page data with components added
	 */
	public function gatherComponentGrain($products) {
				
		foreach ($products as $index => $product) {
			if ($product['Catalog']['type'] & KIT) {

				// Found a kit. First store its 'available' quantity
				$this->fetchMaxKitUp($product['Catalog']['id']);
				$products[$index]['Catalog']['available_qty'] = $product['Item']['available_qty'] + $this->maxKitUp;
				
				// Now load up its components for display in store grain
				// $ids is no an IN list of one or more user ids
				$products[$index]['Catalog']['Components'] = $this->fetchComponents($product['Catalog']['id']);
			}
		}
		
		return $products;
	}

    /**
     * Fetch a list of all catalogs associated with a catalog node
     *
     * This will get a list of all catalogs associated with a provided
     * catalog node and all its descendents
     * The list is filtered by catalog types.
     * For catalogs, you can order:
     * PRODUCT (4), KIT (1), ORDER_COMPONENT (16)
     *
     * @param string $rootNode
     * @param boolean $allNodes
     * @return array inlist of catalog ids
     */
    public function fetchCatalogList($rootNode, $allNodes = FALSE) {
        $catalogs = $this->find('list', array(
            'fields' => array(
                'id','id'
            ),
            'conditions' => array(
                'Catalog.ancestor_list LIKE' => "%,{$rootNode},%",
                'OR' => array(
                    'Catalog.type & ' . PRODUCT,
                    'Catalog.type & ' . KIT,
                    'Catalog.type & ' . ORDER_COMPONENT
                )
            )
        ));
        return $catalogs;
    }

    /**
     * Fetch a catalog id based upon a customer's root node and
     * the customer item id
     *
     * @param string $rootNode
     * @param string $customer_item_code
     * @return string
     */
    public function fetchCatalogId($rootNode, $customer_item_code) {
        $id = $this->find('first', array(
            'fields' => array(
                'id','id'
            ),
            'conditions' => array(
                'Catalog.ancestor_list LIKE' => "%,{$rootNode},%",
                'Catalog.customer_item_code' => $customer_item_code
                )
            )
        );
        return Hash::get($id,'Catalog.id');
    }

	/**
	 * Fetch a list of all items associated with a catalog node
	 * 
	 * This will get a list of all items associated with a provided
	 * catalog node and all its descendents
	 * The list is filtered by catalog types.
	 * For replenishments, you can replenish:
	 * PRODUCT (4), KIT (1), ORDER_COMPONENT (16), COMPONENT (8)
	 * 
	 * @param string $rootNode
	 * @param boolean $allNodes
	 * @return array inlist of item ids
	 */
	public function fetchItemList($rootNode, $allNodes = FALSE) {
		$items = $this->find('list', array(
			'fields' => array(
				'item_id','item_id'
			),
			'conditions' => array(
				'Catalog.ancestor_list LIKE' => "%,{$rootNode},%",
				'OR' => array(
					'Catalog.type & ' . PRODUCT,
					'Catalog.type & ' . KIT,
					'Catalog.type & ' . ORDER_COMPONENT,
					'Catalog.type & ' . COMPONENT 
					)
			)
		));
		return $items;
	}
	
	/**
	 * Fetch the full catalog record with no containment
	 * 
	 * @param string $id
	 * @return array
	 */
	public function fetchOnIdNoContainment($id) {
		$d = $this->find('first', array(
					'conditions' => array(
						'Catalog.id' => $id
					),
					'contain' => FALSE
				));
		if($d == array()){
			throw new NotFoundException("Catalog $id was not found.");
		} else {
			return $d;
		}
	}
	
	public function updateCatalogCustomer() {
		$roots = $this->find('list', array(
			'conditions' => array(
				'ancestor_list' => ',1,'
			),
			'fields' => array('id', 'customer_user_id')
		));
		$prods = $this->find('all', array(
			'conditions' => array(
				'customer_user_id is NULL'
			),
			'recursive' => -1,
			'fields' => array('id', 'customer_user_id', 'ancestor_list')
		));
		foreach ($prods as $index => $record) {
			$a = explode(',', $record['Catalog']['ancestor_list']);
			$prods[$index]['Catalog']['customer_user_id'] = $roots[$a[2]];
		}
		$this->saveAll($prods);
	}
	
	public function updateItemVendor() {
		$roots = $this->find('all', array(
			'recursive' => -1,
			'conditions' => array(
				'Catalog.ancestor_list' => ',1,'
			)));
		
		foreach ($roots as $index => $catalog) {
			$vendor = $this->Item->Vendor->find('first', array(
				'conditions' => array(
					'customer_id' => $catalog['Catalog']['customer_id']
				),
				'fields' => 'id'
			));
			$vendor_id = $vendor['Vendor']['id'];
			$children = $this->getDecendents($catalog['Catalog']['id'], FALSE, array('type <>' => 2), array('contain' => array('Item')));
			foreach ($children as $child) {
			    $data = [
			        'id' => $child['Item']['id'],
                    'vendor_id' => $vendor_id
                ];
				$this->Item->save($data);
			}
		}
	}
	
	/**
	 * Given a Catalog id, find its default Vendor
	 * 
	 * The default vendor is the customer that owning that prodcut as a vendor
	 * 
	 * @param string $catalog_id
	 * @return string
	 */
	public function fetchItemVendor($catalog_id) {
	    $ancestorList = $this->field('ancestor_list', array('Catalog.id' => $catalog_id));
	    if($ancestorList === ',1,'){
	        $rootParentId = $catalog_id;
        } else {
            $rootParentId = explode(',', trim($ancestorList, ','))[1];
        }
		$custUser = $this->field('customer_id', array('Catalog.id' => $rootParentId));
		$Vendor = ClassRegistry::init('Address');
		$vendor_id = $Vendor->field('Address.id', array('Address.customer_id' => $custUser, 'type' => 'vendor'));

		return $vendor_id;
	}

    public function findItemIdByCustomerItemCode($customerItemCode, $parent_id)
    {
        $parent_id = "%," . explode('/', $parent_id)[0] . ",%";
        return $this->field('Catalog.item_id', [
            'Catalog.customer_item_code' => $customerItemCode,
            'Catalog.ancestor_list LIKE' => $parent_id
        ]);
    }
	
}