<?php

/**
 * User Model
 *
 * @copyright     Copyright (c) Dreaming Mind (http://dreamingmind.com)
 * @package       app.Model
 */
App::uses('AppModel', 'Model');
App::uses('CakeSession', 'Model/Datasource');
App::uses('CustomerEntity', 'Model/Entity');
App::uses('CustomerCollection', 'Model/Entity');

/**
 * User Model
 *
 * @package	app.Controller
 * @property User $ParentUser
 * @property Time $Time
 * @property User $ChildUser
 */
class User extends AppModel {

// <editor-fold defaultstate="collapsed" desc="Validation">
	/**
	 * Validation rules
	 *
	 * @var array
	     */
	public $validate = [
		'username' => [
			'required' => [
				'rule' => ['notBlank'],
				'message' => 'A username is required'
			],
			'unique' => [
				'rule' => ['isUnique'],
				'message' => 'That username is taken. Try another.'
			]
		],
		'role' => [
			'valid' => [
				'rule' => ['checkListHash', 'User', 'role'],
				'message' => 'Please enter a valid role',
				'allowEmpty' => true
			]
		],
		'parent_id' => [
			'valid' => [
				'rule' => ['checkListHash', 'User', 'parent_id'],
				'message' => 'Please enter a valid parent',
				'allowEmpty' => true
			]
		]
	]; // </editor-fold>

// <editor-fold defaultstate="collapsed" desc="Associations">
	/**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = [
        'ParentUser' => [
            'className' => 'User',
            'foreignKey' => 'parent_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ]
    ];

    /**
     * hasOne associations
     *
     * @var array
     */
    public $hasOne = [
        'Customer' => [
            'className' => 'Customer',
            'foreignKey' => 'user_id',
			'dependent' => true,
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ]
    ];

    /**
     * hasMany associations
     *
     * @var array
     */
    public $hasMany = [
        'Invoice' => [
            'className' => 'Invoice',
            'foreignKey' => 'customer_id',
            'dependent' => false,
        ],
        'Time' => [
            'className' => 'Time',
            'foreignKey' => 'user_id',
            'dependent' => false,
            'conditions' => '',
        ],
        'ChildUser' => [
            'className' => 'User',
            'foreignKey' => 'parent_id',
            'dependent' => true,
        ],
        'UserObserver' => [
            'className' => 'Observer',
            'foreignKey' => 'user_observer_id',
            'dependent' => true,
            'fields' => [
				'UserObserver.id', 
				'UserObserver.user_id', 
				'UserObserver.user_name', 
				'UserObserver.type'],
        ],
        'Observer' => [
            'className' => 'Observer',
            'foreignKey' => 'user_id',
            'dependent' => true,
            'fields' => ['Observer.id', 
				'Observer.user_observer_id', 
				'Observer.observer_name', 
				'Observer.type'],
            'order' => 'Observer.type',
        ],
        'Address' => [
            'className' => 'Address',
            'foreignKey' => 'user_id',
            'dependent' => true,
        ],
        'Budget' => [
            'className' => 'Budget',
            'foreignKey' => 'user_id',
            'dependent' => true,
            'conditions' => ['Budget.current' => true],
        ],
        'Replenishment' => [
            'className' => 'Replenishment',
            'foreignKey' => 'user_id',
            'dependent' => false,
        ],
        'Order' => [
            'className' => 'Order',
            'foreignKey' => 'user_id',
            'dependent' => false,
        ],
        'OwnedCatalogs' => [
            'className' => 'Catalog',
            'foreignKey' => 'customer_user_id',
            'dependent' => false,
        ]
    ];

    /**
     * hasAndBelongsToMany associations
     *
     * @var array
     */
    public $hasAndBelongsToMany = [
        'Catalog' => [
            'className' => 'Catalog',
            'joinTable' => 'catalogs_users',
            'foreignKey' => 'user_id',
            'associationForeignKey' => 'catalog_id',
            'unique' => 'keepExisting',
        ],
        'UserManager' => [
            'className' => 'User',
            'joinTable' => 'users_users',
            'foreignKey' => 'user_managed_id',
            'associationForeignKey' => 'user_manager_id',
            'unique' => 'keepExisting',
			'dependent' => true,
        ],
        'UserManaged' => [
            'className' => 'User',
            'joinTable' => 'users_users',
            'foreignKey' => 'user_manager_id',
            'associationForeignKey' => 'user_managed_id',
            'unique' => 'keepExisting',
        ]
    ]; // </editor-fold>
	
// <editor-fold defaultstate="collapsed" desc="Properties">
	public $actsAs = [
		'ThinTree'
	];

	/**
	 *
	 * @var array User roles and the roles they can assign
	     */
	public $roles = [
		'Admins Manager' => [
			'Admins Manager',
			'Staff Manager',
			'Clients Manager',
			'Staff Buyer',
			'Clients Buyer',
			'Staff Guest',
			'Clients Guest',
			'Warehouses Manager'],
		'Staff Manager' => [
			'Staff Manager',
			'Clients Manager',
			'Staff Buyer',
			'Clients Buyer',
			'Staff Guest',
			'Clients Guest',
			'Warehouses Manager'],
		'Clients Manager' => [
			'Clients Manager', 
			'Clients Buyer', 
			'Clients Guest'],
		'Staff Buyer' => [
			'Staff Buyer', 
			'Clients Buyer', 
			'Staff Guest', 
			'Clients Guest'],
		'Clients Buyer' => [
			'Clients Buyer', 
			'Clients Guest'],
		'Staff Guest' => [
			'Staff Guest', 
			'Clients Guest'],
		'Clients Guest' => ['Clients Guest'],
		'Warehouses Manager' => ['Warehouses Manager']
	];
	public $displayField = 'username';

	/**
	 * Flag to indicate User has access to new node
	 *
	 * @var boolean
	     */
	public $refreshPermissions = false;

	/**
	 * The logged in user's ID passed from User beforeFilter
	 *
	 * @var string Id of the logged in user
	     */
	public $userId = '';

	/**
	 * IN list to limit user searches
	 *
	 * @var array
	 */
	public $accessibleUserInList = false;

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
	 *
	 * @var int id of the absolute tree root
	     */
	public $ultimateRoot = 1;

	/**
	 * Session.Auth.User.UserRoots sent from controller
	 *
	 * @var array Accessible user nodes
	     */
	public $userRoots = [];
	
	public $query = '';
	
	/**
	 * InList of Orders attached to Users/Catalogs that were found by the 'search' feature
	 *
	 * @var array
	 */
	public $userQueryOrderInList = false;
	
	/**
	 * InList of Replenishments attached to Users/Catalogs that were found by the 'search' feature
	 *
	 * @var array
	 */
	public $userQueryReplenishmentInList = false;
	
	public $customers = [];
// </editor-fold>


	/**
     *
     * @param type $id
     * @param type $table
     * @param type $ds
     */
    function __construct($id = false, $table = null, $ds = null) {
        parent::__construct($id, $table, $ds);
//        $this->virtualFields['name'] = $this->discoverName($id);
        $this->virtualFields['name'] = sprintf('CONCAT(%s.first_name, " ", %s.last_name)', $this->alias, $this->alias);
    }

    /**
     * beforeSave
     *
     * Hash the password before putting it in the db
     * Call for ThinTree Behavior processing
     *
     * @todo Creating new non-parent User means the Creator has access to the created (HABTM)
     * @param type $options
     * @return boolean
     */
    public function beforeSave($options = []) {
        parent::beforeSave($options);
        // make sure new root level trees actually start from our 1 root
        if (isset($this->data[$this->alias]['parent_id']) && $this->data[$this->alias]['parent_id'] == '') {
            $this->data[$this->alias]['parent_id'] = $this->ultimateRoot;
            $this->data[$this->alias]['ancestor_list'] = $this->establishAncestorList($this->ultimateRoot);
            $this->data[$this->alias]['folder'] = 1;
            $maxSequence = $this->getMaxSequence($this->data[$this->alias]['parent_id']);
            $this->data[$this->alias]['sequence'] = $maxSequence[0]['max_sequence_number'] + 1;
        }

        if (isset($this->data[$this->alias]['password'])) {
            $this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
        }
        return true;
    }

    /**
     * If a new root user was created give this user permission
     *
     * @param type $created
     */
    function afterSave($created, $options = []) {
        parent::afterSave($created, $options);
        if ($created && $this->data['User']['parent_id'] == $this->ultimateRoot && !$this->rootOwner) {
            $permission = [
                'UserManager' => ['id' => $this->userId],
            ];
            if ($this->save($permission, false)) {
                $this->refreshPermissions = true;
            }
        }
    }

    //============================================================
    // GENERATE LISTS TO SUPPORT FORM INPUTS
    //============================================================

    /**
     * Get a list of roles for an input drop-down
     *
     * @todo This will need to filter the roles based on the User's role
     * @return type
     */
    public function getRoleList($role) {
        foreach ($this->roles[$role] as $permissible) {
            $secureList[$permissible . '/' . $this->secureHash($permissible)] = $permissible;
        }
        return $secureList;
    }

    /**
     * Get a list of roles for an input drop-down
     *
     * @todo This will need to filter the roles based on the User's role
     * @return type
     */
    public function getMyAddressesList($accessKey) {
        $raw = $this->find('first', ['conditions' => ['User.id' => $accessKey]]);
        $myAddresses = [];
        foreach ($raw['Address'] as $index => $address) {
            $myAddresses[$address['id']] = $address['name'];
        }
        $sorted = asort($myAddresses);
        return $myAddresses;
    }

    /**
     * Get a list of roles for an input drop-down
     *
     * @todo This will need to filter the roles based on the User's role
     * @return type
     */
    public function getConnectedAddressesList($accessKey) {
        $raw = $this->getAccessibleUserNodes($this->getOwnedUserRoots($accessKey));
        foreach ($raw as $index => $user) {
            if(count($user['Address'])>0) {
                $name = $this->discoverName ($user['User']['id']);
                $otherAddresses[$name] = [];
                foreach ($user['Address'] as $index => $address) {
                    $otherAddresses[$name][$address['id']] = $address['name'];
                }
            }
        }
        ksort($otherAddresses);
        foreach ($otherAddresses as $name => $addresses) {
            asort($addresses);
            $otherAddresses[$name] = $addresses;
        }
        return $otherAddresses;
    }

    public function getThirdPartyBillingAddressesList($accessKey) {
		$address = [];
		$users = $this->getAccessibleUserInList();

		$insecureAddresses = $this->Address->find('list', [
			'fields' => ['Address.id', 'Address.name'],
			'conditions' => ['Address.user_id' => $users, [
				'OR' => [
					'Address.ups_acct > ' => 0,
					'Address.fedex_acct > ' => 0
				]
			] ],
			'recursive' => 1
		]);
		$transAddresses = array_flip($insecureAddresses);
		foreach ($transAddresses as $key => $value) {
			$address[$key] = $value . '/' . $this->secureHash($value);
		}
		return array_flip($address);
    }

	/**
	 * Get all users observed by this user at this observation type
	 * 
	 * @param string $type The type of observation
	 * @param string $id The user doing the observing
	 * @return array The users being observed
	 */
	public function getObservationList($type, $id = false) {
		if (!$id) {
			$id = $this->userId;
		}
		$seedNodes = $this->UserObserver->find('list',[
			'fields' => ['user_id', 'user_id'],
			'conditions' => [
				'user_observer_id' => $id,
				'type' => $type
			]
		]);
		return $this->getInListFrom($seedNodes);
	}
	
    /**
     * Get a list of CatalogNodes for an input drop-down
     *
     * @todo add filter to allow organizing node (defined how) to be the only result
     * @todo This will need to filter the roles based on the User's role
     * @return type
     */
    public function getCatalogList($nodeList, $conditions = []) {
        if (empty($nodeList)) {
            return [];
        }
        $raw = $this->getAccessibleCatalogNodes($nodeList, $conditions);
        $secureList = [];
        foreach ($raw as $key => $node) {
            $id = $node['Catalog']['id'];
			$count = substr_count($node['Catalog']['ancestor_list'], ',', 1);
            $secureList['list'][$id . '/' . $this->secureHash($id)] = $node['Catalog']['name'];
			$secureList['ancestors'][$id . '/' . $this->secureHash($id)] = $count;
        }
        return $secureList;
    }

    /**
     * Get a list of User nodes to act as parents
     *
     * @todo add filter to allow organizing node (defined how) to be the only result
     * @param int $id Id of the logged in user
     * @return array a secured drop list of Users
     */
    public function getParentList($rootNodes, $conditions = []) {
        if (empty($rootNodes)) {
            return [];
        }
		$returnArray = [];
		$loopCount = 1;
		foreach ($rootNodes as $key => $id) {
			$returnArray[$loopCount++] = $this->getFullNode($id, true, $conditions);
		}
		return $returnArray;
		return $this->getFullNode(1, true, $conditions);
    }

    /**
     * returns list of permitted users
     * 
     * $access key can take either an id or an array
     * It expects an array like:
     * array(
     *   3 => array(...fields...),
     *   5 => array(...fields...)
     *   ...
     * 
     * the array can be either keyed numerically or by id
     * 
     * @param string|array $accessKey
     * @param array $conditions
     * @return array
     */
    public function getPermittedList($accessKey, $type) {
        if (empty($accessKey)) {
            return [];
        }
        if (!is_array($accessKey)) {
            if ($type == 'user') {
                $accessKey = $this->getOwnedUserRoots($accessKey);
            } elseif ($type == 'catalog') {
                $accessKey = $this->getOwnedCatalogRoots($accessKey);
            } else {
                throw new BadMethodCallException('Unknown node type chosen: ' . $type . ' user or catalog expected.');
            }
        }
        $secureList = [];
        foreach ($accessKey as $index => $fields) {
            $secureList[] = $fields['id'] . '/' . $this->secureHash($fields['id']);
        }
        return $secureList;
    }

    /**
     * Return one a a variety of secure lists
     *
     * @param int $accessKey The users id or an array of permissible nodes
     * @param string $type What type of list to return
     * @param boolean $filter filter the list to only folder nodes, not grain nodes
     * @return type A secure list for drop-downs
     */
    public function getSecureList($accessKey, $type) {
        switch ($type) {
            case 'parent':
                $conditions = ['User.active' => 1, 'User.folder' => 1];
                return $this->getParentList($accessKey, $conditions);
                break;
            case 'catalog':
                $conditions = ['Catalog.active' => 1, 'Catalog.folder' => 1];
                return $this->getCatalogList($accessKey, $conditions);
                break;
            case 'role':
                return $this->getRoleList($accessKey);
                break;
            case 'permittedUsers':
                //returns array of the user nodes this user has permission to access
                return $this->getPermittedList($accessKey, 'user');
                break;
            case 'permittedCatalogs':
                //returns array of the catalog nodes this user has permission to access
                return $this->getPermittedList($accessKey, 'catalog');
                break;
            case 'myAddresses':
                //returns array of addresses connected directly to this user
                return $this->getMyAddressesList($accessKey);
                break;
            case 'connectedAddresses':
                //returns array of addresses accessible by this user, but not directly connected
                return $this->getConnectedAddressesList($accessKey);
                break;
            case 'permittedCustomers':
                //returns array of users which are customers
                return $this->getPermittedCustomers($accessKey);
                break;
            case 'thirdParty':
                //returns array of users which are customers
                return $this->getThirdPartyBillingAddressesList($accessKey);
                break;
            default:
                break;
        }
        return $this->secureList($insecureList);
    }

    /**
     * Take a normal id => name list and make it id/hash => name
     *
     * @param array $list The insecure list
     * @return array The secure list
     */
    public function secureList($list) {
        foreach ($list as $id => $displayValue) {
            $secureList[$id . '/' . $this->secureHash($id)] = $displayValue;
        }
        return $secureList;
    }

    //============================================================
    // PROCESSES TO ACCESS PERMITTED USER NODES
    //============================================================

    /**
     * Get the records to use when making a user parent list
     *
     * @todo integrate the filter parameter into the system
     * @param int $id The logged in users id
     * @param boolean $filter true = only organizational node, false = all
     * @return array|false The raw array or node records or false if no ownership
     */
    public function getAccessibleUserNodes($rootNodes, $conditions = []) {
        if (empty($rootNodes)) {
            return [];
        }
        // Pull all the allowed catalog records from all allowed nodes as a flat array
        $assembleFlatNodes = [];
        foreach ($rootNodes as $key => $value) {
            $assembleFlatNodes = array_merge($assembleFlatNodes, ($this->getFullNode($value['id'], false, $conditions)));
        }
        return $assembleFlatNodes;
    }

    /**
     * Get the records to use when making a catalog parent list
     *
     * @todo integrate the filter parameter into the system
     * @param int $id The logged in users id
     * @param boolean $filter true = only records with no item_id, false = all
     * @return array|false The raw array or node records or false if no ownership
     */
    public function getAccessibleCatalogNodes($rootNodes, $conditions = []) {

        // Pull all the allowed catalog records from all allowed nodes as a flat array
        $assembleFlatNodes = [];
			foreach ($rootNodes as $key => $value) {
				$assembleFlatNodes = array_merge($assembleFlatNodes, ($this->Catalog->getFullNode($value['id'], false, $conditions)));
			}
        return $assembleFlatNodes;
    }

    /**
     * Get the catalog root nodes a user has permission to access
     *
     * @param int $id logged in users id
     * @return array|false The catalog nodes the user has permission to access or false
     */
    public function getOwnedCatalogRoots($id) {
        // Find the catalog nodes this user can see
        $catalogs = $this->find('all', [
            'conditions' => [$this->escapeField() => $id],
            'contain' => ['Catalog']
        ]);
        $result = $this->filterChildNodes($catalogs[0]['Catalog']);
        $roots = (!empty($result)) ? $result : [];
        return $this->idIndexNodes($roots);
    }

    /**
     * Get the user root nodes a logged in user has permission to access
     *
     * @param type $id The logged in users id
     * @return array|false The permissible User nodes indexed by id
     */
    public function getOwnedUserRoots($id) {
        // Find the catalog nodes this user can see
        $users = $this->UserManager->find('all', [
            'conditions' => ['UserManager.id' => $id],
            'contain' => ['UserManaged']
        ]);
        $result = $this->filterChildNodes($users[0]['UserManaged']);
        $roots = (!empty($result)) ? $result : [];
        return $this->idIndexNodes($roots);
    }

    public function getPermittedCustomers($id , $hash = TRUE, $active = 1) {
		// Find the catalog nodes this user can see
		$users = $this->getAccessibleUserInList();
		
		// Setup conditions based upon $active
		$conditions = ['Customer.user_id' => $users];
		if($active == 1){
			$conditions['User.active'] = 1;
		}

		$insecureCustomers = $this->Customer->find('list', [
			'fields' => ['Customer.user_id', 'Customer.name'],
			'conditions' => $conditions,
			'order' => ['Customer.name'],
			'recursive' => 1
		]);
		$transCustomers = array_flip($insecureCustomers);
		foreach ($transCustomers as $key => $value) {
			$customers[$key] = ($hash) ? $value . '/' . $this->secureHash($value) : $value;
		}
		
		return array_flip($customers);
	}

    /**
     * Filter out any nodes who's parents are also in the array
     * 
     * @param array $data
     * @return array
     */
    public function filterChildNodes($data) {
        if (empty($data)) {
            return [];
        }
        $result = $data2 = $data;
        foreach ($data as $index => $node) {
            foreach ($data2 as $index2 => $node2) {
                if (preg_match("/,{$node['id']},/", $node2['ancestor_list'])) {
                    unset($result[$index2]);
                }
            }
        }
        return $result;
    }

    /**
     * Make to permissble node array id indexed for easy access
     *
     * These arrays are stored in the Auth session after indexing
     *
     * @param type $nodes A set of permissible root nodes
     * @return array The nodes array now indexed by id
     */
    public function idIndexNodes($nodes) {
        if ($nodes) {
            foreach ($nodes as $node) {
                $result[$node['id']] = $node;
            }
            return $result;
        }
        return $nodes;
    }

    /**
     * Return the best available Name for user = id
     * 
     * @param type $id
     * @return boolean
     */
    public function discoverName($id = null) {
        if ($id == null) {
            return false;
        }
        $names = $this->find('first', [
            'conditions' => [$this->escapeField() => $id],
            'fields' => [
                'username',
                'name'
            ],
            'contain' => false
        ]);
        if (preg_match('/[A-za-z0-9]+/', $names['User']['name']) > 0) {
            return $names['User']['name'];
        } else {
           return $names['User']['username'];
        }
        return false;
    }

    /**
     * Get the full array of accessible user ids to use as a query IN condition
     * 
     * If no array of starting nodes is provided, use the accessible User nodes
     * 
     * @param type $nodes An array of user nodes that are the source for the IN list collection
     * @return array The IN list of user ids to use as a query condition
     */
    public function getAccessibleUserInList(){
	$nodes = CakeSession::read('Auth.User.UserRoots');
	
	$this->accessibleUserInList = $this->getInListFrom($nodes);
	return $this->accessibleUserInList;
    }

    /**
     * Get an array of user ids to use as a query IN condition
     * 
     * Given a list of seed nodes, make a list of all branch nodes
     * 
     * @param type $nodes An array of user nodes that are the source for the IN list collection
     * @return array The IN list of user ids to use as a query condition
     */
    public function getInListFrom($nodes){
		$in = [];
		if($nodes && !empty($nodes)) {
			$userRoots = array_keys($nodes);
			foreach ($userRoots as $id){
			$in[$id] = $id;
			}
			$options = [
			'fields' => [
				'User.id'
			],
			'contain' => false
			];
			$userSet = [];
			foreach($userRoots as $root){
			$userSet = array_merge($userSet, $this->getDecendents($root, false, [], $options));
			}
			foreach($userSet as $index => $one){
			$in[$one['User']['id']] = $one['User']['id'];
			}
		}
		return $in;
    }
    
    /**
     * Return the ancestor list as a query-compatible IN list
     * 
     * @todo This could be a part of ThinTree
     * @param string $id Look upstream from this leaf
     * @param boolean $inclusive Include this final leaf record in list?
     * @return array The IN list ($this->accessibleUserInList[ID] => ID)
     */
    public function getAncestorInList($id, $inclusive = TRUE) {
	$in = [];
	$group = false;
	$ancestors = $this->getAncestors($id, $group);
	if ($ancestors) {
	    foreach ($ancestors as $index => $record) {
		$in[$record['User']['id']] = $record['User']['id'];
	    }
	}
	if ($inclusive) {
	    $in[$id] = $id;
	}
	return $in;
    }
	
	/**
	 * Search the User name fields
	 * 
	 * Within the set of Users this user can access,
	 * find User records that have names values like $query
	 * 
	 * @param string $query The search value
	 * @return array The found records
	 */
	public function queryUsers($query) {
		// insure the inlist property is set for proper result filtering
		if (!$this->accessibleUserInList) {
			$this->getAccessibleUserInList();
		}
		//perform find
		$this->userQuery = $this->find('all', [
			'conditions' => [
				'User.id' => $this->accessibleUserInList,
				'OR' => [
					'User.first_name LIKE' => "%{$query}%",
					'User.last_name LIKE' => "%{$query}%",
					'User.username LIKE' => "%{$query}%",
					'User.name LIKE' => "%{$query}%",
				]
			],
			'fields' => [
				'id', 'first_name', 'last_name', 'name', 'username'
			],
			'contain' => [
				'Order' => [
					'fields' => 'Order.id'
				],
				'Replenishment' => [
					'fields' => 'Replenishment.id'
				]
			]
		]);
		
		foreach ($this->userQuery as $index => $user) {
			if(!empty($user['Order'])){
				foreach ($user['Order'] as $i => $order) {
					$this->userQueryOrderInList[$order['id']] = $order['id'];
				}
			}
			if(!empty($user['Replenishment'])){
				foreach ($user['Replenishment'] as $i => $replenishment) {
					$this->userQueryReplenishmentInList[$replenishment['id']] = $replenishment['id'];
				}
			}
		}

		return $this->userQuery;
	}
	
	/**
	 * Produce a collection of customer objects from a provided list of customer ids
	 * 
	 * @param array $ids (customer ids)
	 * @return object
	 */
	public function inventoryReportCustomers($ids, $sortBy) {
		$this->data = $this->find('all', [
			'conditions' => [
				'User.id' => $ids
			],
			'contain' => FALSE,
			'order' => 'User.username'
		]);
		
		return new CustomerCollection($this->data, ['path' => '{n}.User', 'sortBy' => $sortBy]);
	}
	

}
