<?php

App::uses('AppModel', 'Model');
App::uses('Observer', 'Model');

/**
 * Order Model
 *
 * @property OrderItem $OrderItem
 * @property Shipment $Shipment
 */
class Order extends AppModel {

// <editor-fold defaultstate="collapsed" desc="Validation">
    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = array(
        'first_name' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
            ),
        ),
        'status' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
            ),
        ),
        'billing_address' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
            ),
        ),
        'billing_city' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
            ),
        ),
        'billing_zip' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
            ),
        ),
        'billing_state' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
            ),
        ),
        'address' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
            ),
        ),
        'city' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
            ),
        ),
        'zip' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
            ),
        ),
        'state' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
            ),
        ),
        'order_item_count' => array(
            'numeric' => array(
                'rule' => array('numeric'),
            ),
        ),
        'order_type' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
            ),
        )
    ); // </editor-fold>
    //The Associations below have been created with all possible keys, those that are not needed can be removed
// <editor-fold defaultstate="collapsed" desc="Associations">
    /**
     * hasMany associations
     *
     * @var array
     */
    public $hasMany = array(
        'OrderItem' => array(
            'className' => 'OrderItem',
            'foreignKey' => 'order_id',
            'dependent' => true,
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'exclusive' => '',
            'finderQuery' => '',
            'counterQuery' => ''
        ),
        'Shipment' => array(
            'className' => 'Shipment',
            'foreignKey' => 'order_id',
            'dependent' => true,
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'exclusive' => '',
            'finderQuery' => '',
            'counterQuery' => ''
        ),
        'OrderCharges' => array(
            'className' => 'InvoiceItem',
            'foreignKey' => 'order_id'
        ),
        'Document' => array(
            'className' => 'Document',
            'foreignKey' => 'order_id'
        )
    );
    public $belongsTo = array(
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',
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
        'UserCustomer' => array(
            'className' => 'User',
            'foreignKey' => 'user_customer_id',
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
        'Customer' => array(
            'className' => 'Customer',
            'foreignKey' => false,
            'dependent' => false,
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'exclusive' => '',
            'finderQuery' => 'SELECT Customer.* from customers AS Customer WHERE `Customer`.`user_id` = `orders`.`user_customer_id`',
            'counterQuery' => ''
        ),
        'Budget' => array(
            'className' => 'Budget',
            'foreignKey' => 'budget_id',
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
        'Backorder' => array(
            'className' => 'Order',
            'foreignKey' => 'backorder_id',
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
    ); // </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Properties">
    /**
     * An array of orders, user/status sorted
     *
     * Structure as defined in fetchOrders()
     *
     * @var array An array of orders
     */
    public $orderSet = array();

    /**
     * An array of orders, status sorted
     *
     * @var array An array of watched orders
     */
    public $watchedOrders = array();

    /**
     * An array of orders that are not being watched
     *
     * User/status sorted
     * Structure as defined in fetchOrders()
     *
     * @var array An array of orders
     */
    public $unWatchedOrders = array();

    /**
     * An array of orders at approval status
     *
     * Company/User sorted
     *
     * @var array An array of orders
     */
    public $approvedOrders = array();

    /**
     * What order status to pull for each eyes-on/observer type
     *
     * @var array The eyes-on => status pattern
     */
    public $watchPattern = array(
        'Approve' => array('Submitted'),
        'Notify' => array('Approved', 'Shipped'),
        'Watch' => array('Approved', 'Backorder', 'Pulled', 'Shipped', 'Invoiced')
    );
    public $carrier = array(
        'UPS' => 'UPS',
        'FedEx' => 'FedEx',
        'Other' => 'Other'
    );
    public $method = array(
        'UPS' => array(
            '1DM' => 'Next Day Air Early AM',
            '1DA' => 'Next Day Air',
            '1DP' => 'Next Day Air Saver',
            '2DM' => '2nd Day Air AM',
            '2DA' => '2nd Day Air',
            '3DS' => '3 Day Select',
            'GND' => 'Ground Service',
            'EP' => 'Express Plus',
            'ES' => 'Express',
            'SV' => 'Express Saver',
            'EX' => 'Expedited',
            'ST' => 'Standard'
        ),
        'FedEx' => array(
            'FO' => 'First Overnight',
            'PO' => 'Priority Overnight',
            'SO' => 'Standard Overnight',
            'DA2' => '2 Day Air',
            'ES' => 'ExpressSaver - 3 Day',
            'GND' => 'Ground',
            'HM' => 'Ground - Home',
            'SP' => 'Ground - SmartPost',
            'IG' => 'Int Ground',
            'IE' => 'Int Economy',
            'IP' => 'Int Priority'
        ),
        'Other' => array(
            'OT' => 'Other Transport',
            'WillCall' => 'Will Call'
        )
    );

    /**
     * An array of possible packaging type to use in shipments
     *
     * @var array
     */
    public $packaging = array(
        'Letter' => 'Letter',
        'Pack' => 'Pack',
        'Box' => 'Box',
        'OwnPackaging' => 'OwnPackaging'
    );
    public $newShipment = array();
    public $backorderRecord = array();
    public $newBackorder = array();

    /**
     * The order for backup array
     *
     * This is array is created in setCreateBackupOrderVars and starts as the
     * data packet passed either by
     * OrdersController::backorderSweep or ::backorderItem
     *
     * @var array
     */
    public $ofb = array();
    public $newBackorderId = '';
    public $backorderOrderId = '';
    /**
     * Transfer property for the Order query methods
     *
     * @var array the result of the query methods
     */
    public $queryResult = array();

    /**
     * Transfer property for the Order query methods
     *
     * @var array the inlist of a users orders in a query
     */
    public $orderInList = array();

    /**
     * Transfer property for the Order query methods
     *
     * @var type the result of the users orders query
     */
    public $userQueryResult = array();

    protected $ItemSubtotalSum ;
    protected $ItemWeightSum;
    protected $ShipmentSum;
    protected $Taxable;
//	private $Subtotal;
//	private $Tax;
//	private $Weight;
//	private $Count;
    protected $ShipmentTaxPercent;
    protected $ItemCount;

// </editor-fold>

//============================================================
// ORDER SETUP
//============================================================

    /**
     * When create()ing Orders, reset Sum properties too
     *
     * Since these properties are used to populate fields from joined data
     * the properties must be managed when new records are created
     *
     * @param type $data
     * @param boolean $filterKey
     * @param type $resetSums
     */
    public function create($data = array(), $filterKey = FALSE, $resetSums = TRUE) {
        parent::create($data, $filterKey);
        if ($resetSums) {
            $this->set('ItemCount', 0);
            $this->set('ItemSubtotalSum', 0);
            $this->set('ItemWeightSum', 0);
            $this->set('ShipmentSum', 0);
            $this->set('ShipmentTaxPercent', 0);

            $this->set('Taxable', $this->get('Order.taxable'));
        }
        return $this->data;
    }

    /**
     * Return an order number
     *
     * Given two values from the Order record
     * return a unique order number
     * YYMM-xxxx
     * YY = last two year digits
     * MM = two digit month
     * xxxx = a base 19 number
     * Base 19 has custom digit set, all caps
     *
     * @param string $id The id of the created order
     * @return string order number: YYMM-xxxx
     */
    public function getOrderNumber($id) {
        //setup variables
        $this->id = $id;
        $seed = $this->field('order_seed');
        $created = $this->field('created');
        if (!$seed || !$created) {
            return false;
        }
        $codedNumber = $this->getCodedNumber($seed, $created);
        return $codedNumber;
    }

    public function withItemAndShipSums($id) {
        return $this->find('first', array(
            'conditions' => array(
                'Order.id' => $id),
            'fields' => array(
                'id',
                'user_id',
                'status',
                'billing_company',
                'weight',
                'order_item_count',
                'subtotal',
                'tax',
                'shipping',
                'total',
                'budget_id',
                'user_customer_id',
                'backorder_id',
                'taxable'
            ),
            'contain' => array(
                'OrderItem' => array(
                    'fields' => array(
                        'SUM(weight)',
                        'SUM(subtotal)',
                        'COUNT(id)',
                        'SUM(quantity)'
                    )
                ),
                'Shipment' => array(
                    'fields' => array(
                        'SUM(shipment_cost)',
                        'tax_jurisdiction',
                        'id',
                        'order_id',
                        'tax_rate_id',
                        'tax_percent'
                    )
                )
            )
        ));
    }

    /**
     * Set calc'd dollar and qty vals for properties and $this->data
     *
     * Requires properties:
     *	ItemSubtotalSum
     *	ShipmentTaxPercent
     *	Taxable		(taxable)
     *	ShipmentSum
     *	ItemWeightSum
     *	ItemCount
     *
     * Sets $this->data array vals:
     *	subtotal
     *	tax
     *	total
     *	weight
     *	order_item_count
     *
     * @return array the 5 new array data points index'd by field name
     */
    public function createTotalsFromSums() {
        $subtotal = $this->ItemSubtotalSum;
        $this->data['Order']['subtotal'] = $subtotal;

        $tax = sprintf('%0.2f', $subtotal * $this->ShipmentTaxPercent * $this->Taxable);
        $this->data['Order']['tax'] = $tax;

        $this->data['Order']['total'] = $subtotal + $tax + $this->ShipmentSum;

        $this->data['Order']['order_item_count'] = $this->ItemCount;

        $this->data['Order']['weight'] = $this->ItemWeightSum;

        return array_intersect_key(
            $this->data['Order'],
            array(
                'subtotal' => NULL,
                'tax' => NULL,
                'order_item_count' => NULL,
                'weight' => NULL,
                'total' => NULL,
            )
        );
    }

//============================================================
// ORDER / BACKORDER FETCH METHODS for USER STATUS
//============================================================

    /**
     * Get the User/status grouped orders for a user or users
     *
     * <pre>
     * array(
     * 16 => array(
     *   'User' => 'fields' => 'values'
     *   'submitted' => array(
     * 	    0 => array(
     * 	      'Order' => 'fields' => 'values'
     * 	      'User' => 'fields' => 'values'
     * 	      'OrderItem' => array(
     * 	          0 => 'fields' => 'values'
     * 	          1 => 'fields' => 'values'
     * 	      'Shipment' => 'fields' => 'values'
     * 2 => array(
     *   'User' => 'fields' => 'values'
     *   'submitted' => array(
     * 	    0 => array()
     * 	    1 => array()
     *   'approved' => array()
     *   'backorder' => array()
     * </pre>
     *
     * @param int|array $id A user_id or IN list of user_ids
     * @param boolean $omit Whether to omit the logged in user
     * @param boolean $watched Break-out orders using my eyes-on settings
     * @return type
     */
    public function fetchOrders($id, $omit = false, $watched = false, $conditions = array()) {
        if (!is_array($id)) {
            $id = array($id);
        }
        if(empty($conditions)){
            $conditions = [
                'OR' => [
                    'Order.user_id' => $id,
                    'Order.user_customer_id' => $id,
                ],
                'NOT' => [
                    ['Order.status' => 'Archived'],
                    ['Order.status' => 'Invoiced'],
                    ['Order.status' => 'Shipped']
                ]
            ];
        }

        // $ids is no an IN list of one or more user ids
        $myOrders = $this->find('all', array(
            'conditions' => $conditions,
            'contain' => array(
                'User',
                'UserCustomer' => array(
                    'Customer' => array(
                        'fields' => array(
                            'id',
                            'allow_backorder'
                        )
                    )
                ),
                'Budget',
                'OrderItem' => array(
                    'Item' => array(
                        'fields' => array('id', 'available_qty')
                    ),
                    'Catalog' => array(
                        'ParentCatalog' => array(
                            'Item'
                        )
                    )
                ),
                'Shipment',
                'Document' => array(
                    'fields' => array(
                        'id'
                    )
                )
            )));

        $this->groupByStatus($myOrders, $omit);
        $keys = array_keys($this->orderSet);
        if ($watched) {
            $this->breakOutWatched();
        }
        return $this->orderSet;
    }

    /**
     * Construct the grouped order array
     *
     * See fetchOrders for structure details
     *
     * @param array $orders
     */
    private function groupByStatus($orders, $omit = false) {
        //set id var if we're including the logged in user
        $id = ($omit) ? $this->User->userId : false;
        //setup result array and parse provided array
        $this->orderSet = array();
        foreach ($orders as $index => $order) {

            $order = $this->injectKitData($order);

            if ($order['Order']['status'] == 'Approved') {
                $this->approvedOrders['Approved'][$order['Order']['id']] = $order;
            } else {
                if ($id && $id == $order['Order']['user_id']) {
                    continue;
                }

                // create proper order for status output
                if (!isset($this->orderSet[$order['Order']['user_id']])) {
                    $this->orderSet[$order['Order']['user_id']] = $this->statusOutputOrder;
                }
                $this->orderSet[$order['Order']['user_id']]['User'] = $order['User'];
                $orderStatus = ($order['Order']['status'] == 'Shipping') ? 'Shipped' : $order['Order']['status'];
                $this->orderSet[$order['Order']['user_id']][$orderStatus][$order['Order']['id']] = $order;
            }
        }
    }

    /**
     * Add components and availability data to Ordered products that are kits
     *
     * This is performed for the status page data
     *
     * @param array $order The order to examine for Kits
     * @return array The components
     * @todo add cake logging
     */
    public function injectKitData($order) {
        $loop = $order;
        if (isset($loop['OrderItem'])) {
            foreach ($loop['OrderItem'] as $index => $product) {
                if ($product['Catalog']['type'] & KIT) {
                    $this->OrderItem->Catalog->fetchMaxKitUp($product['Catalog']['id']);
                    $order['OrderItem'][$index]['Catalog']['available_qty'] = $product['Item']['available_qty'] + $this->OrderItem->Catalog->maxKitUp;
                    // $ids is no an IN list of one or more user ids
                    $order['OrderItem'][$index]['Catalog']['Components'] = $this->OrderItem->Catalog->fetchComponents($product['Catalog']['id']);
                } else if ($product['Catalog']['type'] & COMPONENT) {
                    $order['OrderItem'][$index]['Catalog']['available_qty'] = ($product['Item']['available_qty'] / $product['Catalog']['sell_quantity']) + $product['Catalog']['ParentCatalog']['Item']['available_qty'];
                } else if ($product['Catalog']['type'] & PRODUCT) {
                    $order['OrderItem'][$index]['Catalog']['available_qty'] = $product['Item']['available_qty'] / $product['Catalog']['sell_quantity'];
                } else {
                    CakeLog::warning("unexpected type {$product['Catalog']['type']} encountered in Order/injectKitData");
                }
            }
        } else {
            //add cake logging
        }
        return $order;
    }

    /**
     * Extract Watched orders from $this->orderSet
     *
     * Sends the watched orders to $this->watchedOrders, status grouped
     * $this->unWatchedOrders will be $this->orderSet minus watched orders
     */
    private function breakOutWatched() {
        $this->unWatchedOrders = $this->orderSet;
        if (empty($this->orderSet)) {
            return; // no orders, don't bother
        }
        $this->Observer = ClassRegistry::init('Observer');
        $eyesOn = $this->Observer->find('list', array(
            'fields' => array('Observer.user_id', 'Observer.user_id', 'Observer.type'),
            'conditions' => array('Observer.user_observer_id' => $this->User->userId)));

        if (empty($eyesOn)) {
            return; // not watching anything, scram
        }
        foreach ($eyesOn as $group => $list) {
            $result[$group] = $this->User->getInListFrom($list);
        }
        $eyesOn = $result;

        // walk our eyes on list
        foreach ($eyesOn as $type => $users) {

            // at each entry look through the watchPattern types
            foreach ($this->watchPattern as $type => $statuses) {

                // and look at each status to watch for this type
                foreach ($statuses as $status) {

                    // and for each user watched for this type
                    foreach ($users as $user) {

                        // see if they have orders at this status
                        if (isset($this->unWatchedOrders[$user][$status])) {

                            // add the watched order, it may be the first of its kind
                            if (isset($this->watchedOrders[$status])) {
                                $this->watchedOrders[$status] = array_merge($this->watchedOrders[$status], $this->unWatchedOrders[$user][$status]);
                            } else {
                                $this->watchedOrders[$status] = $this->unWatchedOrders[$user][$status];
                            }

                            // remove the watched order
                            unset($this->unWatchedOrders[$user][$status]);

                            // user has no more orders, all are watched
                            if (count($this->unWatchedOrders[$user]) == 1) {
                                unset($this->unWatchedOrders[$user]);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Fetch the data necessary for the backorder
     *
     * @param type $id
     * @return array or string The data may say 'disallowed'
     */
    function fetchDataForBackorder($id) {
        $data = $this->find('first', array(
            'conditions' => array('Order.id' => $id),
            'contain' => array(
                'User',
                'UserCustomer' => array(
                    'Customer' => array(
                        'fields' => array(
                            'id',
                            'allow_backorder'
                        )
                    )
                ),
                'OrderItem' => array(
                    'Item' => array(
                        'fields' => 'available_qty'
                    ),
                    'Order' => array(
                        'fields' => array(
                            'id',
                            'backorder_id',
                            'status'
                        )
                    )
                ),
                'Shipment',
                'Backorder' => array(
                    'OrderItem' => array(
                        'Item' => array(
                            'fields' => 'available_qty'
                        )
                    ),
                    'Shipment'
                )
            )));

        // if backorders aren't allowed for the customer, set data to 'disallowed'
        if (!$data['UserCustomer']['Customer']['allow_backorder']) {
            $data = 'disallowed';
        }
        return $data;
    }

    /**
     * Fetch the data necessary for the backorder item
     *
     * @param type $id
     * @return array or string The data may say 'disallowed'
     */
    function fetchDataForBackorderItem($id) {
        $data = $this->OrderItem->find('first', array(
            'conditions' => array('OrderItem.id' => $id),
            'contain' => array(
                'Item' => array(
                    'fields' => array(
                        'available_qty'
                    )
                ),
                'Order' => array(
                    'Shipment',
                    'UserCustomer' => array(
                        'Customer' => array(
                            'fields' => array(
                                'id',
                                'allow_backorder'
                            )
                        )
                    ),
                    'Backorder' => array(
                        'OrderItem'
                    )
                )
            )));
        // if backorders aren't allowed for the customer, set data to 'disallowed'
        if (!$data['Order']['UserCustomer']['Customer']['allow_backorder']) {
            $data = 'disallowed';
        }
        return $data;
    }

//============================================================
// ORDER / BACKORDER FETCH METHODS for WAREHOUSE STATUS
//============================================================

    /**
     * Pull all orders for the warehouse status page
     *
     * Filter orders to those only in Released or Pulled status
     *
     * Method sets property
     * @property statusOutputOrder
     * @return none
     */
    public function fetchWarehouseOrders() {
        $pullList = $this->find('all', array(
            'conditions' => array(
                'Order.status' => array('Released', 'Pulled')
            ),
            'contain' => array(
                'User',
                'UserCustomer' => array(
                    'Customer' => array(
                        'fields' => array(
                            'id',
                            'allow_backorder'
                        )
                    )
                ),
                'Budget',
                'OrderItem' => array(
                    'Catalog' => array(
                        'ParentCatalog' => array(
                            'Item'
                        )
                    ),
                    'Item' => array(
                        'Image',
                        'Location'
                    )
                ),
                'Shipment',
                'Document' => array(
                    'fields' => array(
                        'id'
                    )
                )
            )));
        $this->groupWarehouseOrders($pullList);
        return $this->statusOutputOrder;
    }

    /**
     * Setup the proper array structure based upon the found orders
     *
     * Method sets property
     * @property statusOutputOrder
     * @param array $pullList
     */
    public function groupWarehouseOrders($pullList) {
        //setup default array to force status sort order
        foreach ($pullList as $index => $record) {
            $record = $this->injectKitData($record);
            //use the property statusOutputOrder which provides a preset array
            $orderStatus = ($record['Order']['status'] == 'Shipping') ? 'Shipped' : $record['Order']['status'];
            $this->statusOutputOrder[$orderStatus][$record['Order']['id']] = $record;
        }
    }

//============================================================
// ORDER / BACKORDER QUERY METHODS for SEARCH
//============================================================

    /**
     * Find all orders associated with the user's query
     *
     * @param string $query The search string
     * @param array $archived Does the user want archived orders (TRUE) or not (FALSE)
     * @return array
     */
    public function queryOrders($query, $archived) {
        if( !$this->User->accessibleUserInList ) {
            $this->User->getAccessibleUserInList;
        }
        if($archived){
            $statusComparator = '=';
        } else {
            $statusComparator = '!=';
        }

        $baseOrderInList = $this->find('list', array(
            'conditions' => array(
                'Order.status '.$statusComparator => 'Archived',
                'OR' => array(
                    'Order.user_id' => $this->User->accessibleUserInList,
                    'Order.user_customer_id' => $this->User->accessibleUserInList
                )
            )));

        //get Orders belonging to Users that were found by $query (if any)
        if(!empty($this->User->userQueryOrderInList)){
            $this->userQueryOrders($this->User->userQueryOrderInList);
        }

        //perform find
        $this->queryResult = $this->find('all', array(
            'conditions' => array(
                'Order.id' => $baseOrderInList,
                'OR' => array(
                    'Order.order_number LIKE' => "%{$query}%",
                    'Order.billing_company LIKE' => "%{$query}%",
                    'Order.order_reference LIKE' => "%{$query}%"
                )
            ),
            'contain' => array(
                'User',
                'UserCustomer' => array(
                    'Customer' => array(
                        'fields' => array('id', 'allow_backorder')
                    )
                ),
                'Budget',
                'OrderItem' => array(
                    'Item',
                    'Catalog' => array(
                        'ParentCatalog' => array(
                            'Item'
                        )
                    )
                ),
                'Shipment',
            )
        ));
        //Merge userQueryOrders and directly found Orders
        $this->queryResult = array_merge($this->userQueryResult, $this->queryResult);

        //Setup array to use standard status page grain
        $this->sortQueryOrders();

        //return final sorted orders
        return $this->statusOutputOrder;
    }

    /**
     * Find all orders belonging to users in the inList
     *
     * @param array $inList The list of user IDs
     */
    private function userQueryOrders($inList) {
        $this->userQueryResult = $this->find('all', array(
            'conditions' => array(
                'Order.id' => $inList
            ),
            'contain' => array(
                'User',
                'UserCustomer' => array(
                    'Customer' => array(
                        'fields' => array('id', 'allow_backorder')
                    )
                ),
                'Budget',
                'OrderItem' => array(
                    'Item',
                    'Catalog' => array(
                        'ParentCatalog' => array(
                            'Item'
                        )
                    )
                ),
                'Shipment',
            )
        ));
    }

    /**
     * Sort function to setup queried orders to use the standard status grain
     *
     */
    private function sortQueryOrders() {
        foreach ($this->queryResult as $index => $order) {
            $order = $this->injectKitData($order);
            $this->statusOutputOrder[$order['Order']['status']][$order['Order']['id']] = $order;
        }
    }

//============================================================
// BACKORDER CREATION / MANAGEMENT
//============================================================

    /**
     * Create a backorder record, if necessary
     *
     *
     * @param array $order
     * @return array
     */
    function setupBackorder($order) {
        //set base variables - This returns $this->ofb
        $this->setBackorderVars($order);

        // If there is no attached backorder, make one and attach it
        // and get the order_id of the backorder
        if (empty($this->ofb['Order']['backorder_id'])) {
            //create a backorder
            $this->newBackorderId = $this->createBackorder();
            //update the ofb & order in the database
            $this->updateBackorderOrder();
            //setup shipment in ofb and in the database
            $this->updateBackorderShipment();
        } else {
            $this->newBackorderId = $this->ofb['Order']['backorder_id'];
        }

        // setup the OrderItemIndex array
        $this->setupOrderItemIndex();

        return $this->ofb;
    }

    /**
     * Setup base arrays for createBackorder
     *
     * This method sets the following properties
     * $this->ofb; set to $order
     * $this->newShipmnet; set to the Shipment element of order
     * $this->backorderRecord; set to the Backorder element of order
     *
     * @param array $order
     */
    private function setBackorderVars($order) {
        $this->ofb = $order;
        $this->newShipment['Shipment'] = (isset($this->ofb['Shipment'])) ? $this->ofb['Shipment'][0] : $this->ofb['Order']['Shipment'][0];
        $this->backorderRecord['Order'] = (isset($this->ofb['Backorder'])) ? $this->ofb['Backorder'] : $this->ofb['Order']['Backorder'];
    }

    /**
     * Create the backorder record
     *
     * Based upon the ofb property, method creates & saves the backorder status record
     *
     * Method sets a property
     * @property $this->newBackorder
     * @param property $this->ofb The order array
     * @return none
     */
    private function createBackorder() {
        //set new backorder record to match the order record
        $this->newBackorder['Order'] = $this->ofb['Order'];
        //remove the order record's id
        unset($this->newBackorder['Order']['id']);
        unset($this->newBackorder['Order']['order_seed']);
        unset($this->newBackorder['Order']['created']);
        //set the backorder record's status
        $this->newBackorder['Order']['status'] = 'Backordered';
        $this->newBackorder['OrderItem'] = array();
        //create and save the backorder record
        $this->create();
        $this->save($this->newBackorder, false);
        $order_number = $this->getOrderNumber($this->id);
        if ($order_number) {
            $this->saveField('order_number', $order_number);
            return $this->id;
        } else {
            $this->removeOrder($this->id);
        }
    }

    private function updateBackorderOrder() {
        $this->ofb['Order']['backorder_id'] = $this->newBackorderId;
        $this->ofb['Backorder'] = $this->newBackorder['Order'];
        $this->ofb['Backorder']['id'] = $this->newBackorderId;

        //save the backorder_id field into the original order
        $this->id = $this->ofb['Order']['id'];
        $this->saveField('backorder_id', $this->ofb['Order']['backorder_id']);
    }

    private function updateBackorderShipment() {
        //working from the property set in setBackorderVars
        unset($this->newShipment['Shipment']['id']);
        $this->newShipment['Shipment']['order_id'] = $this->ofb['Backorder']['id'];
        $this->Shipment->save($this->newShipment, false);

        //update the $this->ofb array to reflect the new shipment
        $this->ofb['Backorder']['Shipment'][0] = $this->newShipment['Shipment'];
        $this->ofb['Backorder']['Shipment'][0]['id'] = $this->Shipment->id;
        $this->ofb['Backorder']['Shipment'][0]['order_id'] = $this->ofb['Backorder']['id'];
    }

    /**
     * Setup the OrderItemIndex array on the ofb property
     *
     * Requires:
     * $this->backorderRecord
     * $this->ofb
     *
     * called by Order->setupBackorder
     *
     */
    private function setupOrderItemIndex() {
        //works from the properties set in setBackorderVars
        if (!empty($this->backorderRecord['Order']['OrderItem'])) {
            foreach ($this->backorderRecord['Order']['OrderItem'] as $boItem) {
                $this->ofb['OrderItemIndex'][$boItem['item_id']] = $boItem;
            }
        } else {
            $this->ofb['OrderItemIndex'] = array();
        }
    }

//============================================================
// ORDER REMOVAL
//============================================================

    public function removeOrder($id) {
        $items = $this->OrderItem->find('all', array(
            'conditions' => array(
                'OrderItem.order_id' => $id
            ),
            'fields' => array('OrderItem.id', 'OrderItem.item_id')
        ));
        if (!empty($items)) {
            $this->Item = ClassRegistry::init('Item');
            foreach ($items as $item) {
                $this->OrderItem->delete($item['OrderItem']['id']);
                $this->Item->manageUncommitted($item['OrderItem']['item_id']);
            }
        }
        $backorder = $this->find('first', array(
            'conditions' => array(
                'Order.backorder_id' => $id
            )
        ));
        if (!empty($backorder)) {
            $this->id = $backorder['Order']['id'];
            $this->saveField('backorder_id', '');
        }
        $this->delete($id);
    }

//============================================================
// ORDER PRINT
//============================================================

    /**
     * Get an order and create the summary for a printed version
     *
     * This array is the basis for other response summaries
     * like the XML response. But they require further transformations
     *
     * @param type $id
     * @return type
     */
    public function getOrderForPrint($id) {

        // get the order data
        $order = $this->find('first', array(
            'conditions' => array('Order.id' => $id),
            'fields' => array('created', 'order_number', 'order_reference', 'total', 'billing_company',
                'billing_address', 'billing_address2', 'billing_city',
                'billing_zip', 'billing_state', 'billing_country', 'ship_date', 'note', 'user_customer_id'),
            'contain' => array(
                'User' => array(
                    'fields' => array('first_name', 'last_name', 'username')
                ),
                'Shipment' => array(
                    'fields' => array(
                        'first_name', 'last_name', 'company',
                        'email', 'phone',
                        'address', 'address2',
                        'city', 'zip', 'state', 'country',
                        'carrier', 'method', 'billing'
                    )
                ),
                'OrderItem' => array(
                    'fields' => array('quantity', 'name',),
                    'Item' => array(
                        'fields' => array('item_code'),
                        'Location'
                    )
                )
            )
        ));
        $customer_type = ClassRegistry::init('Customer')->field('customer_type', array('Customer.user_id' => $order['Order']['user_customer_id'] ));
        $items = $this->assemblePrintLineItems($order['OrderItem']);
        $firstPageLines = 500;
        $pg1 = array_slice($items, 0, $firstPageLines);
        if (count($items) > count($pg1)) {
            $chunk = array_chunk(array_slice($items, $firstPageLines, count($items)), 37);
        } else {
            $chunk = array();
        }

        // page the line item arrays
        // first
        $orderedBy = $this->User->discoverName($order['User']['id']);
        if(!empty($order['Order']['ship_date'])){
            $t = strtotime($order['Order']['ship_date']);
        } else {
            $t = time();
        }
        $data = array(
            'reference' => array(
                'labels' => array('Date', 'Order'),
                'data' => array(date('m/d/y', $t), $order['Order']['order_number'])
            ),
            'items' => $pg1,
            'summary' => array(
                'labels' => array('Ordered By', 'Reference', 'Carrier', 'Method', 'Billing'),
                'data' => array(
                    $orderedBy, // Ordered By
                    $order['Order']['order_reference'],		 // Reference
                    $order['Shipment'][0]['carrier'],		 // Carrier
                    $order['Shipment'][0]['method'],	 // Method
                    $order['Shipment'][0]['billing'],	 // Billing
//					$order['Order']['total']			 // Total
                )
            ),
            'note' => $order['Order']['note'],
            'headerRow' => array('#', 'Qty', 'Code', 'Name'),
            'customer_type' => $customer_type,
            'chunk' => $chunk,
            'shipping' => array(
                "{$order['Shipment'][0]['first_name']} {$order['Shipment'][0]['last_name']}",
                $order['Shipment'][0]['company'],
                $order['Shipment'][0]['address'],
                $order['Shipment'][0]['address2'],
                "{$order['Shipment'][0]['city']} {$order['Shipment'][0]['state']} {$order['Shipment'][0]['zip']} {$order['Shipment'][0]['country']}"
            ),
            'billing' => array(
                $order['Order']['billing_company'],
                $order['Order']['billing_address'],
                $order['Order']['billing_address2'],
                "{$order['Order']['billing_city']} {$order['Order']['billing_state']} {$order['Order']['billing_zip']} {$order['Order']['billing_country']}"
            )
        );
        return $data;
    }

    /**
     *
     * @param type $locations
     * @return type
     */
    private function stringLoc($locations) {
        $b = '';
        foreach ($locations as $index => $location) {
            $b .= substr(preg_replace('/[aeiouy ]*/', '', $location['building']), 0, 3)
                . ".r{$location['row']}b{$location['bin']}/";
        }
        return trim($b, '/');
    }

    /**
     * Fetch an inlist of 'Shipped' status orders based on provided customer id
     *
     * @param string $id, the customer id
     * @return array
     */
    public function fetchShippedOrderInList($id) {
        $list = $this->find('list', array(
            'conditions' => array(
                'Order.user_customer_id' => $id,
                'Order.status' => 'Shipped'
            ),
            'fields' => array('order_number', 'id')
        ));
        if (!empty($list)){
            $stringList = implode('\',\'', $list);
            $stringList = '\'' . $stringList . '\'';
            $updateQuery = "UPDATE `orders` SET `orders`.`transaction` = 1 WHERE `orders`.`id` IN ($stringList)";
            $this->query($updateQuery);
        }
        return $list;
    }


    /**
     * Return an inlist of orders currently in the invoicing process
     * We have used `Order`.`transaction` set to 1 during the initial pull by
     * fetchShippedOrderInList, above, to setup the orders on a working invoice
     *
     * @param string $id the customer user id
     * @return in list
     */
    public function fetchInvoicingOrderInList($id) {
        $list = $this->find('list', array(
            'conditions' => array(
                'Order.user_customer_id' => $id,
                'Order.transaction' => 1,
                'Order.status' => 'Shipped'
            ),
            'fields' => array('order_number', 'id')
        ));
        return $list;
    }

    /**
     * Return an inlist of orders ready to archive
     *
     * @return in list
     */
    public function fetchArchivingOrderInList() {
        $date = date('Y-m-d', (time() - MONTH));
        $list = $this->find('list', array(
            'conditions' => array(
                'Order.status' => 'Invoiced',
                'Order.modified <' => $date
            ),
            'fields' => array('order_number', 'id')
        ));
        $logData = implode(',', $list);
        return $list;
    }

    /**
     * Fetch an array of order header data for use in invoicing
     *
     * @param array $inList
     * @return array
     */
    public function fetchInvoicingOrderHeader($inList) {
        if(empty($inList)){
            return array();
        }
        $b = $this->find('all', array(
            'conditions' => array(
                'Order.id' => $inList
            ),
            'fields' => array(
                'Order.id',
                'Order.order_number',
                'Order.order_reference',
                'Order.created as order_date',
                'Order.ship_date',
                'Order.exclude'
            ),
            'contain' => array('Shipment' => array('fields' => array(
                'Shipment.first_name',
                'Shipment.last_name',
                'Shipment.company',
                'Shipment.address',
                'Shipment.address2',
                'Shipment.city',
                'Shipment.state',
                'Shipment.zip',
                'Shipment.country'
            )))
        ));
        $r = array();
        foreach ($b as $record) {
            $r[$record['Order']['id']] = array_merge($record['Order'], $record['Shipment'][0]);
        }
        return $r;
    }

    /**
     * Save the array built from an automated XML order submission
     *
     * @return boolean
     */
    public function saveXMLOrder() {
        return false;
    }

    /**
     * Pull all orders in the status provided
     *
     * List find, with the order id as both index and value
     * Filter to only those records modified after the provided date
     *
     * @param string $status
     * @param date $date
     * @return \ArrayIterator
     */
    public function fetchShippingOrders($status, $date) {
        $f = $this->find('list', array(
            'conditions' => array ('status' => $status, 'modified > ' => $date),
            'fields' => array('id', 'id')
        ));
        return new ArrayIterator($f);
    }

    /**
     * Fetch all shipments associated with the supplied orderInList
     * Return a list of subtotal indexed by id
     *
     * @param array $orderInList
     * @return list array
     */
    public function fetchOrderCharges($orderInList) {
        $orders = $this->find('list', array(
            'conditions' => array(
                'Order.id' => $orderInList
            ),
            'fields' => array('id', 'subtotal')
        ));
        return $orders;
    }

    /**
     * Based on the order id, fetch the complete fat customer
     *
     * @param string $order_id
     * @return array the complete customer record
     */
    public function fetchCustomer($order_id) {
        $cust_id = $this->field('user_customer_id', array('id' => $order_id));
        return $this->Customer->fetchCustomer($cust_id);
    }


}