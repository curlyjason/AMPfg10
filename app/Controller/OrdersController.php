<?php
App::uses('AppController', 'Controller');
App::uses('CakeEvent', 'Event');
App::uses('OrderStatusEvent', 'Lib');
App::uses('NotificationFileHandler', 'Lib/Notifiers');
App::uses('FileExtension', 'Lib');

/**
 * Orders Controller
 *
 * @property Order $Order
 */
class OrdersController extends AppController {

// <editor-fold defaultstate="collapsed" desc="Properties">
    public $uses = array('Order', 'User', 'Price');
    public $helpers = array('Accumulator', 'Report', 'BrandedPages');

    /**
     * The order record
     *
     * Provides data throughout the status change process
     *
     * @var array
     */
    public $order = false;


    /**
     * The replenishment record
     *
     * Provides data throughout the status change process
     *
     * @var array
     */
    public $replenishment = false;

    public $user = false;

    public $customer = false;

    public $budget = false;

    public $items = false;

    public $userId = false;

    public $customerId = false;

    public $customerUserId = false;

    public $inStock = true;

    public $alias = false;


    /**
     * The status the Order/Replen/Whatever ended on
     *
     * This is what we will take action on
     * for  those that Observe'
     *
     * @var string
     */
    public $currentStatus = '';

    /**
     * The status the Order/Replen/Whatever started on
     *
     * This is what we will take action on
     * for  those that Observe'
     *
     * @var string
     */
    public $startStatus = '';

    public $orderId = '';

    public $destination = array();

// </editor-fold>

    //============================================================
    //
    //============================================================

    public function beforeFilter() {
        parent::beforeFilter();
        //establish access patterns
        $this->accessPattern['Manager'] = array ('all');
        $this->accessPattern['Buyer'] = array(
            'index',
            'shop',
            'listOrders',
            'listReleased',
            'listPendingApprovals',
            'listBackorders',
            'statusChange',
            'updateOrder',
            'updateOrderItem');
        $this->accessPattern['Guest'] = array ('listOrders');
    }

    public function isAuthorized($user) {
        return $this->authCheck($user, $this->accessPattern);
    }

    public function realIndex() {
        $this->index();
        $this->render('index');
    }

    public function index() {
        $this->Order->recursive = -1;
        $this->set('orders', $this->paginate());
    }

    /**
     * view method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function view($id = null) {
        if (!$this->Order->exists($id)) {
            throw new NotFoundException(__('Invalid order'));
        }
        $options = array('conditions' => array('Order.' . $this->Order->primaryKey => $id));
        $this->set('order', $this->Order->find('first', $options));
    }

    /**
     * add method
     *
     * @return void
     */
    public function add() {
        if ($this->request->is('post')) {
            $this->Order->create();
            if ($this->Order->save($this->request->data)) {
                $this->Flash->set(__('The order has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Flash->set(__('The order could not be saved. Please, try again.'));
            }
        }
    }

    /**
     * edit method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function edit($id = null) {
        if (!$this->Order->exists($id)) {
            throw new NotFoundException(__('Invalid order'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Order->save($this->request->data)) {
                $this->Flash->set(__('The order has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Flash->set(__('The order could not be saved. Please, try again.'));
            }
        } else {
            $options = array('conditions' => array('Order.' . $this->Order->primaryKey => $id));
            $this->request->data = $this->Order->find('first', $options);
            $carrier = $this->Order->carrier;
            $method = $this->Order->method;
            $this->set(compact('carrier', 'method'));
        }
    }

    /**
     * delete method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function delete($id = null) {
        $this->Order->id = $id;
        if (!$this->Order->exists()) {
            throw new NotFoundException(__('Invalid order'));
        }
        $this->request->onlyAllow('post', 'delete');
        if ($this->Order->delete()) {
            $this->Flash->set(__('Order deleted'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Flash->set(__('Order was not deleted'));
        $this->redirect(array('action' => 'index'));
    }

    public function listOrders() {
        $this->index();
        $this->render('index');
    }

    public function listReleased() {
        $this->index();
        $this->render('index');
    }

    public function listPendingApprovals() {
        $this->index();
        $this->render('index');
    }

    public function listBackorders() {
        $this->index();
        $this->render('index');
    }

    public function shop() {

    }

    public function printOrder($id) {
        if(isset($this->request->params['ext']) && $this->request->params['ext'] == 'pdf'){
            $this->layout = 'default';
        } else {
            $this->layout = 'print_accumulator';
        }
        // Configure::write('debug', 0);
        $data = $this->Order->getOrderForPrint($id);

        $type = 'order';
        $headerRow = $data['headerRow'];
        $chunk = $data['chunk'];
        $this->set(compact('data', 'chunk', 'type', 'headerRow'));
    }

    //============================================================
    // STATUS CHANGE PROCESS
    //============================================================

    /**
     * Perfom an appropriate status change/cascade
     *
     * For the order and the status to change to, find
     * the next appropriate status to settle into
     *
     * @param type $id The id of the order
     * @param string $status The status process chosen by the user
     */
    public function statusChange($id, $status, $returnUrl = false) {
        // derive destination
        $this->destination = $this->statusChangeDestination($returnUrl);
        $return = NULL;

        switch ($status) {
            case 'Submit' :
                $this->orderId = $id;
                $this->alias = 'Order';
                $this->startStatus = $status;
                $this->setOrderStatusChangeProperties();
                $this->currentStatus = $this->statusSumbit($id, $status);
                break;
            case 'Backorder' :
                $this->alias = 'Order';
                $this->orderId = $id;
                $this->startStatus = $status;
                $this->currentStatus = $this->statusBackorder($id, $status);
                break;
            case 'Approve':
                $this->alias = 'Order';
                $this->orderId = $id;
                $this->startStatus = $status;
                $this->setOrderStatusChangeProperties();
                $this->currentStatus = $this->statusApprove($id, $status);
                break;
            case 'Release':
                $this->alias = 'Order';
                $this->orderId = $id;
                $this->startStatus = $status;
                $this->currentStatus = $this->statusRelease($id, $status);
                break;
            case 'Pull':
                $this->alias = 'Order';
                $this->orderId = $id;
                $this->startStatus = $status;
                $this->currentStatus = $this->statusPull($id, $status);
                break;
            case 'Shipping':
            case 'Ship':
                $this->alias = 'Order';
                $this->orderId = $id;
                $this->startStatus = $status;
                $this->currentStatus = $this->statusShip($id, $status);
                break;
            case 'Invoice':
                $this->alias = 'Order';
                $this->orderId = $id;
                $this->startStatus = $status;
                $this->currentStatus = $this->statusInvoice($id, $status);
                break;
            case 'Archive':
                if($this->Order->exists($id)){
                    $this->alias = 'Order';
                    $this->orderId = $id;
                } else {
                    $this->Replenishment = ClassRegistry::init('Replenishment');
                    if($this->Replenishment->exists($id)){
                        $this->alias = 'Replenishment';
                        $this->replenishmentId = $id;
                    } else {
                        //throw an unknown record exception
                        break;
                    }
                }
                $this->startStatus = $status;
                $this->currentStatus = $this->statusArchive($id, $status);
                $return = "Order $id archived.";
                break;
            case 'Place':
                // second stage of Replenishment
                $this->alias = 'Replenishment';
                $this->replenishmentId = $id;
                $this->startStatus = $status;
                $this->currentStatus = $this->statusPlaced();
                break;
            case 'Complete':
                // third stage of Replenishment
                $this->alias = 'Replenishment';
                $this->replenishmentId = $id;
                $this->startStatus = $status;
                $this->currentStatus = $this->statusCompleted();
                break;
            default:
                //throw an unknown status exception
                break;
        }

        // an odd way of determining record type...
        if ($this->alias === 'Order' ) {
            $this->Order->id = $this->orderId;
            $order_number = $this->Order->field('order_number');
            $save = $this->Order->saveField('status', $this->currentStatus);
        } else {
            //this is a Replenishment
            $this->Replenishment = ClassRegistry::init('Replenishment');
            $this->Replenishment->id = $this->replenishmentId;
            $order_number = $this->Replenishment->field('order_number');
            $save = $this->Replenishment->saveField('status', $this->currentStatus);
        }

        // flash a message to the view
        $existingMessage = $this->Session->read('Message.flash.message');
        if ($save) {
            $message = 'Change';
            $flashMessage = $existingMessage . ' | Job: ' . $order_number . ' | '  . $this->currentStatus;
            $messageType = 'flash_success';
        } else {
            $flashMessage = $existingMessag . ' | Job: ' . $order_number . ' | Save Failure';
            $messageType = 'flash_error';
        }

        $this->Session->setFlash(__($flashMessage), $messageType);

        // if the save was successful
        if ($save) {
            // now send notifications for orders
            if ($this->alias === 'Order') {

                //register listener
                $this->OrderStatusEvent = new OrderStatusEvent;
                $this->getEventManager()->attach($this->OrderStatusEvent);

                //send the event
                $event = new CakeEvent('Order.Status', $this->prepareEventData());
                $this->getEventManager()->dispatch($event);

                // log the status event and return to the caller
                $this->logStatusChange($id, $message, $this->startStatus, $this->currentStatus);
            }
        }
        if (isset($this->request->params['named']['robot']) && $this->request->params['named']['robot']) {
            if($return != NULL){
                return $return;
            } else {
                return true;
            }
        } else {
            $this->redirect($this->destination);
        }

    }

    /**
     * Gather an array that will serve as the Event context data for status change events
     *
     * @return array
     */
    private function prepareEventData() {
        // some status changes didn't require an order
        if (empty($this->order)) {
            $o = $this->Order->find('first', array('conditions' => array('id' => $this->orderId), 'contain' => FALSE));
            $this->order = $o['Order'];
            $this->userId = $this->order['user_id'];
            $this->customerUserId = $this->order['user_customer_id'];
        } else {
            // the order data never gets updated with the final status. So we'll let the event know
            $this->order['status'] = $this->currentStatus;
        }

        // only the 'order' node is used I think
        return array(
            'orderId' => $this->orderId,
            'orderNumber' => $this->order['order_number'],
            'userId' => $this->userId,
            'customerUserId' => $this->customerUserId,
            'currentStatus' => $this->currentStatus,
            'order' => $this->order
        );

    }

    /**
     * Establish the properties we'll need for status change process
     */
    private function setOrderStatusChangeProperties() {
        $totalFind = $this->Order->find('first', array(
            'conditions' => array(
                'Order.id' => $this->orderId
            ),
            'contain' => array(
                'OrderItem' => array(
                    'Item',
                    'Catalog'
                ),
                'User' => array(
                    'Budget'
                )
            )
        ));
        $customer = $this->User->Customer->find('first', array(
            'conditions' => array(
                'Customer.user_id' => $totalFind['Order']['user_customer_id']
            ),
            'contain' => array('User')
        ));
        $customer['Customer']['User'] = $customer['User'];
        $totalFind['Customer'] = $customer['Customer'];
        if(!empty($totalFind)){
            $this->order = $totalFind['Order'];
            $this->user = $totalFind['User'];
            $this->userId = $this->user['id'];
            if(isset($totalFind['User']['Budget'][0])){
                $this->budget['Budget'] = $totalFind['User']['Budget'][0];
            } else {
                $this->budget = array();
            }
            $this->customer = $totalFind['Customer'];
            $this->customerUserId = $this->customer['user_id'];
            $this->customerId = $this->customer['id'];
            $this->items = $totalFind['OrderItem'];
        } else {
        }
    }
    /**
     * Derive the proper redirect destination for statusChange
     *
     * @param string $returnUrl
     * @return array
     */
    private function statusChangeDestination($returnUrl) {
        if(is_array($returnUrl) || strpos($returnUrl, 'http') === 0){
            return $returnUrl;
        } else if ($returnUrl) {
            $stuff = explode('/', $returnUrl);
        } else {
            $stuff = explode('/', $this->referer());
        }
        $p = 0;
        if (strlen($stuff[count($stuff) - 1]) == 36) {
            $p = 1;
        }
        $destination = array(
            'controller' => $stuff[count($stuff) - 2 - $p],
            'action' => $stuff[count($stuff) - 1 - $p],
            $this->orderId
        );
        return $destination;
    }

    /**
     * Get all possible user IDs that may be watched on an order
     *
     * This Order may be watched because:
     *		- the User that ordered it is directly watched
     *		- the Comapny it's ordered for is directly watched
     *		- Either is downstream of a watched user
     * Using order id, get the ordering users id and its ancestors,
     * get the user customer id for the order and its ancestors.
     * Watch lists cascade, so when combined, if any user in this
     * list is watched, then this order is watched
     *
     * @param string $id The order id
     * @return array The user IDs ($list[id] => $id)
     * @todo need not found exception
     */
    public function allPossibleWatchPoints() {
        // read the ancestor list for the user that made this order
        // and the ancestor list for the company/user
        // and make an IN list of the self/ancestor IDs
        if(!($this->orderId)){
            //throw notFound Exception
            //redirect
        }
        if(!$this->userId){
            $this->userId = $this->Order->field('user_id', array($this->Order->escapeField() => $this->orderId));
        }
        if (!$this->customerUserId) {
            $this->customerUserId = $this->Order->field('user_customer_id', array($this->Order->escapeField() => $this->orderId));
        }
        $inclusive = true;
        $userAncestors = $this->Order->User->getAncestorInList($this->userId, $inclusive);
        $customerAncestors = $this->Order->User->getAncestorInList($this->customerUserId, $inclusive);
        foreach ($customerAncestors as $id) {
            $userAncestors[$id] = $id;
        }
        return $userAncestors;
    }

    /**
     * For the Pulled order, determine its new current status
     *
     * @param type $id The order id
     * @return string The final landing status
     */
    private function statusBackorder($id) {
        return 'Backordered';
    }

    /**
     * For the Submitted order, determine its new current status
     *
     * @param type $id The order id
     * @return string The final landing status
     */
    private function statusSumbit($id) {
        $this->handleBackorderUpdates($id);
        if (!$this->submitEvaluation($id)) {
            return 'Submitted';
        }
        return $this->statusApprove($id);
    }

    /**
     * For the Approved order, determine its new current status
     *
     * https://github.com/dreamingmind/amp-fg/wiki/Status-Change-Business-Logic#approved-shows-release
     *
     * @param type $id The order id
     * @return string The final landing status
     */
    private function statusApprove($id) {

        $inStock = TRUE;
        $itemLimit = FALSE;
        if ($this->customer['release_hold'] ||  is_string($this->inStockCheck($inStock, $itemLimit))) {
            return 'Approved';
        }
        return $this->statusRelease($id);
    }

    /**
     * For the Released order, determine its new current status
     *
     * @param type $id The order id
     * @return string The final landing status
     */
    private function statusRelease($id) {
        //find the order
        $data = $this->Order->find('first', array(
            'conditions' => array(
                'Order.id' => $id
            ),
            'contain' => array(
                'OrderItem' => array(
                    'Item',
                    'Catalog'
                )
            )
        ));

        $data = $this->Order->injectKitData($data);

        //determine if we're short inventory on any items
        foreach ($data['OrderItem'] as $index => $item) {
            if (($item['catalog_type'] & PRODUCT) && $item['Item']['available_qty'] < 0) {
                $this->Flash->set('You cannot release items with unavailable inventory');
                return 'Approved';
            }
            if(!($item['catalog_type'] & PRODUCT)  && ($item['Catalog']['available_qty'] < 0)){
                $this->Flash->set('You cannot release items with unavailable kit or component inventory');
                return 'Approved';
            }
        }
        // all good, go to released
        return 'Released';
    }

    /**
     * For the Pulled order, determine its new current status
     *
     * @param type $id The order id
     * @return string The final landing status
     */
    private function statusPull($id) {
        //find the order
        $data = $this->Order->find('first', array(
            'conditions' => array(
                'Order.id' => $id
            ),
            'fields' => array(
                'Order.id'
            ),
            'contain' => array(
                'OrderItem' => array(
                    'fields' => array(
                        'pulled'
                    )
                )
            )
        ));

        //determine if all of the line items are pulled
        $allPulled = TRUE;
        foreach ($data['OrderItem'] as $index => $record) {
            if(!$record['pulled']){
                $allPulled = FALSE;
            }
        }

        if($allPulled){
            return 'Pulled';
        } else {
            $this->Flash->set('You must mark off all of the line items as pulled.');
            return 'Released';
        }

        $key = 'Pull';
        return ($this->orderEventMap[$key]);
    }

    /**
     * For the Shipped order, determine its new current status
     *
     * @param type $id The order id
     * @return string The final landing status
     */
    private function statusShip($id) {
        $this->Order->id = $id;
        $this->Order->saveField('ship_date', date('Y-m-d H:i:s', time()));
        $key = 'Ship';
        return ('Shipped');
    }

    /**
     * For the Invoiced order, determine its new current status
     *
     * @param type $id The order id
     * @return string The final landing status
     */
    private function statusInvoice($id) {
        $key = 'Invoice';
        return ('Invoiced');
    }

    /**
     * For the Archived order, determine its new current status
     *
     * @param type $id The order id
     * @return string The final landing status
     */
    private function statusArchive($id) {
        $key = 'Archive';
        return ('Archived');
    }

    /**
     * Place a replenishment (2nd state)
     *
     * This means the PO has gone to the vendor
     * and editing is no longer appropriate
     *
     * @param type $id The replenishment id
     * @return string The final landing status
     */
    private function statusPlaced() {
        return ('Placed');
    }

    /**
     * Complete a replenishment (3rd state)
     *
     * This is done in ReplenishmentsController
     *
     * @param type $id The replenishment id
     * @return string The final landing status
     */
    private function statusCompleted() {
        return ('Completed');
    }

    /**
     * Log a status message for an Order. Tie to the logged in user
     *
     * @param string $id The order record id
     * @param string $statusMessage The status change message to log
     * @param string $start Starting status state for the order
     * @param string $stop Ending status state for the order
     */
    private function logStatusChange($id, $statusMessage, $start = '', $stop = '') {
        $ordnum = $this->Order->field('order_number', array('id' => $id));
        CakeLog::write('status', "$id:: $ordnum :: $statusMessage-$start->$stop:" . $this->Order->User->discoverName($this->Auth->user('id')));
    }

    /**
     * Move a backordered order to submitted state
     *
     * @param type $id
     */
    private function handleBackorderUpdates($id) {
        $status = $this->Order->field('status', array('Order.id' => $id));
        if ($status == 'Backordered') {
            $order = $this->Order->find('first', array(
                'conditions' => array(
                    'Order.id' => $id
                ),
                'contain' => array(
                    'OrderItem'
                )
            ));
            $this->Order->id = $order['Order']['id'];
            $this->Order->saveField('status', 'Submitted');
            foreach ($order['OrderItem'] as $key => $orderItem) {
                $this->Order->OrderItem->Item->manageUncommitted($orderItem['item_id']);
            }
        }
    }

    /**
     * Update an OrderItems quantity (and all related relevant values)
     *
     * This is the typical entry point for an Order adjustment
     * Changing an Items quantity ripples through all aspects of
     * the order, item and budget bringing them all up to date
     * with the current state of affairs.
     *
     * Zero quantity will remove the item
     * If this results in Zero items, the order will be removed
     *
     * @param string $id The item to operate on
     * @param int $qty The new quantity of this item
     * @return json data to return to js
     */
    public function updateOrderItem($id, $qty) {
        if ($this->request->is('ajax')) {
            $this->autoRender = false;
        }

        // start by reading in the current order item
        $orderItem = $this->Order->OrderItem->find('first', array(
            'conditions' => array('OrderItem.id' => $id),
            'contain' => array(
                'Item' => array(
                    'fields' => array('id', 'name', 'available_qty')
                )
            )
        ));

        // scram if it wasn't found
        if (empty($orderItem)) {
            array_merge($this->jsonReturn, array(
                'Error' => 'Failed to find the requested item, please try again.'
            ));
            return json_encode($this->jsonReturn);
        }

        // if we've gone to zero, dump the item and leave
        if ($qty == 0) {
            $this->removeOrderItem($id, $orderItem['OrderItem']['order_id'], $orderItem['OrderItem']['item_id']);
            return;
        } else {

            //set orderItem to reflect quantity change
            $orderItem['OrderItem']['quantity'] = $qty;
            $orderItem['OrderItem']['each_quantity'] = $qty * $orderItem['OrderItem']['sell_quantity'];
            $orderItem['OrderItem']['subtotal'] = $qty * $orderItem['OrderItem']['price'];
            $orderItem['OrderItem']['weight_total'] = $qty * $orderItem['OrderItem']['weight'];

            $this->Order->OrderItem->create();
            $result = $this->Order->OrderItem->save($orderItem);

            // update other models associated with Orders
            $this->Item = ClassRegistry::init('Item');
            $available = $this->Item->manageUncommitted($orderItem['OrderItem']['item_id']);

            // let js know about the save result and pass on the saved data
            $orderItem['OrderItem']['result'] = $result;
            $this->jsonReturn = array_merge($this->jsonReturn, array('Item' => $orderItem), $available);
            $this->installComponent('OrderTools');
            $this->OrderTools->updateOrder($orderItem['OrderItem']['order_id']);
        }
    }

    /**
     * Remove the specified Item from the Order
     *
     * And sort out all the other bits
     * like uncommited inventory and the
     * order wrapper.
     *
     * @param string $id The orderitem to delete
     * @param string $order_id The order id
     */
    private function removeOrderItem($id, $order_id, $item_id) {
        $this->Order->OrderItem->delete($id);
        $this->Item = ClassRegistry::init('Item');
        $available = $this->Item->manageUncommitted($item_id);
        $this->jsonReturn = array_merge($this->jsonReturn, array('deletedItem' => $id), $available);
        $this->installComponent('OrderTools');
        $this->OrderTools->updateOrder($order_id);
        return;
    }

    //============================================================
    // BACKORDER MANAGEMENT
    //============================================================

    /**
     * Based upon requested $mode, process an entire order into backorder
     *
     * Method returns if customer preference disallows backorders
     * $mode can be any of three options:
     * 'fullOrder' - where the full order is made into a backorder, with no duplication
     * 'fullQty' - where all unavailable items moved to a new backorder-type order
     *             and removed from the original order
     * 'overQty' - where all unavailable items have the unavailable portion of their qty
     *             moved to new backorder-status order and the available portion left
     *             on the orginal order
     *
     * <pre>
     * ARRAY $order
     * array(
     * 	'Order' => array(fields),
     * 	'User' => array(fields),
     * 	'UserCustomer' => array(fields)
     * 		'Customer' => array('id', 'allow_backorder')
     * 	'Backorder' => array(fields)
     * 		'Backorder' => array()
     * 	'OrderItem' => array(
     * 		(int) 0 => array(fields)
     * 			'Order' => array(fields)
     * 				'User' => array(fields),
     * 				'UserCustomer' => array(fields),
     * 				'Backorder' => array(),
     * 				'OrderItem' => array(
     * 					(int) 0 => array(fields),
     * 				'Shipment' => array(
     * 					(int) 0 => array(fields)
     * 			'Item' => array('available_qty')
     * 	'Shipment' => array(
     * 		(int) 0 => array(fields)
     * </pre>
     *
     * @param string $id The order containing items in need of backordering
     * @param string $mode The mode for backorder handling, either 'fullOrder', 'fullQty' or 'overQty'
     */
    public function backorderSweep($id, $mode) {
        // pull the order for analysis and work and setup backorder, if necessary
        $order = $this->Order->fetchDataForBackorder($id);

        //check backorder Validity
        $mode = $this->checkBackorderSweepValidity($order, $mode);
        if (!$mode) {
            $this->redirect($this->referer());
        }
        //set base variables
        $orderId = $order['Order']['id'];

        // Setup backorder
        if ($mode != 'fullOrder') {
            $order = $this->Order->setupBackorder($order);
        }

        // Just change the status of the order & update the items
        if ($mode == 'fullOrder') {
            //temporarily set the order status to backorder for efficient order line function
            $this->Order->id = $orderId;
            $this->Order->saveField('status', 'Backordered');

            //update all the order items
            foreach ($order['OrderItem'] as $key => $orderItem) {
                //setup the array for backorderItem and run
                $passItem = $this->setupArrayForBackorderItem($orderItem);
                $this->backorderItem($orderItem['id'], $mode, $passItem);
            }
            //update the status of the order, returning this->referrer()
            $this->statusChange($id, 'Backorder', $this->referer());
        }

        // Not full order, handle each line independently
        foreach ($order['OrderItem'] as $index => $orderItem) {

            if ($orderItem['Item']['available_qty'] < 0) {
                //setup the array for backorderItem and run
                $passItem = $this->setupArrayForBackorderItem($orderItem);
                $this->backorderItem($orderItem['id'], $mode, $passItem);
            }
        }
        $this->redirect($this->referer());
    }

    /**
     * Check provided order for backorderablity
     *
     * @param array $order
     * @param string $mode
     * @return boolean or $mode
     */
    private function checkBackorderSweepValidity($order, $mode) {
        // if backorders aren't allowed for the customer, return false
        if ($order == 'disallowed') {
            $this->Flash->set('This customer does not allow backordering');
            return false;
        }
        // Should we shift items or set status to Backordered
        $itemCount = $availItemCount = count($order['OrderItem']);
        foreach ($order['OrderItem'] as $index => $orderItem) {
            $availItemCount -= 1 * ($orderItem['Item']['available_qty'] < 0);
        }
        //if there are no UNavailable items, return false
        if ($availItemCount == $itemCount && $mode != 'fullOrder') {
            $this->Flash->set('There are no items to be backordered');
            return false;
        }
        //if there are no available items, make the entire order a backorder
        if ($availItemCount == 0) {
            return 'fullOrder';
        }
        return $mode;
    }

    /**
     * Setup the data array for backorderItem method
     *
     *
     * @param array $orderItem
     * @return array
     */
    private function setupArrayForBackorderItem($orderItem) {
        //setup arrays for processing
        $o = $orderItem['Order'];
        unset($orderItem['Order']);
        $i = $orderItem['Item'];
        return array('OrderItem' => $orderItem, 'Order' => $o, 'Item' => $i);
    }

    /**
     * Public facing backorderItem call, with proper data fetch and setup
     *
     *
     * @param string $id
     * @param string $mode
     * @return type
     */
    public function setupBackorderItem($id, $mode) {
        $data = $this->Order->fetchDataForBackorderItem($id);
        // Setup backorder record
        $data = $this->Order->setupBackorder($data);
        // if backorders aren't allowed for the customer, scram-ola
        if ($data == 'disallowed') {
            $this->Flash->set('This customer does not allow backordering');
            return;
        }
        $this->backorderItem($id, $mode, $data);
        $this->redirect($this->referer());
    }

    /**
     * Move the selected line item to the backorder, with qty determined by $mode
     *
     *
     * @param string $id The line item's id
     * @param type $mode The backorder mode chosen, either 'fullOrder', 'fullQty' or 'overQty'
     */
    private function backorderItem($id, $mode, $data = array()) {
        //setup base variables
        $itemQty = $data['OrderItem']['quantity'];
        $availQty = $data['Item']['available_qty'];
        $orderId = $data['Order']['id'];
        $backorderId = $this->Order->newBackorderId;

        //log the backorder of each item
        $statusMessage = 'Backordered - ' . $data['OrderItem']['name'];
        $this->logStatusChange($orderId, $statusMessage);

        //handle fullOrder mode, where you only update EXISTING items
        if ($mode == 'fullOrder') {
            $this->updateOrderItem($id, $itemQty);
            return;
        }

        //handle all other backorder modes, where a new item is necessary
        if (!isset($data['OrderItemIndex'][$data['OrderItem']['item_id']])) {
            //if there IS NOT already an item on the backorder matching this item
            $newBoItem['OrderItem'] = $data['OrderItem'];
            unset($newBoItem['OrderItem']['id']);
            unset($newBoItem['OrderItem']['Item']);
            $newBoItem['OrderItem']['order_id'] = $backorderId;

            $this->Order->OrderItem->create();
            $this->Order->OrderItem->save($newBoItem, false);
            $backorderItemId = $this->Order->OrderItem->id;
        } else {
            //if there IS already an item on the backorder matching this item
            $backorderItemId = $data['OrderItemIndex'][$data['OrderItem']['item_id']]['id'];
        }
        //set quantities for order item and backorder item based upon $mode
        if ($mode == 'fullQty') {
            $backorderQty = $itemQty;
            $orderQty = 0;
        } else {
            $posQty = abs($itemQty + $availQty);
            $orderQty = ($posQty > $itemQty) ? $itemQty : $posQty;
            $backorderQty = $itemQty - $orderQty;
        }
        //run base updateOrderItem methods for both backorder item and order item
        $this->updateOrderItem($backorderItemId, $backorderQty);
        $this->updateOrderItem($id, $orderQty);
    }

    //============================================================
    // PULL MANAGEMENT
    //============================================================

    public function ship($id) {
        if($this->request->is('post') | $this->request->is('put')){
            if ($this->Order->Shipment->save($this->request->data)) {
                $this->statusChange($this->request->data['Shipment']['order_id'], 'Ship', array(
                    'controller' => strtolower($this->Session->read('Auth.User.group')),
                    'action' => 'status'
                ));
            } else {
                $this->Session->setFlash("This shipment didn't save. Please try again.", 'flash_error');
            }
        }

        //Pull existing data to setup shipment
        $this->request->data = $this->Order->Shipment->find('first', array(
            'conditions' => array(
                'Shipment.order_id' => $id
            )
        ));
    }

    public function activity($start, $end, $customer) {
        if(FileExtension::hasExtension($customer)){
            $this->layout = 'default';
            $customer = FileExtension::stripExtension($customer);
        }
        $start = date('Y-m-d H:i:s', $start);
        $end = date('Y-m-d H:i:s', $end);
        $conditions = array(
            'Order.user_customer_id' => $customer,
            'Order.created BETWEEN ? AND ?' => array($start, $end)
        );
        $data = $this->Order->fetchOrders($customer, false, false, $conditions);
        $this->set('customers', $this->User->getPermittedCustomers($this->Auth->user('id')));
        $this->set('customerName', $this->Order->User->discoverName($customer));
        $this->set(compact('data', 'start', 'end', 'customer'));
    }

    //============================================================
    // METHODS FOR AUTOMATIC COMPONENT
    //============================================================

    /**
     * Cron task function to push all 'Shipping' status orders through status update to 'Shipped'
     *
     * Calling cron task must use named parameter 'robot' = TRUE or status change will redirect
     */
    public function updateShippingOrders($status, $date = '1970-1-1 12:00:00') {
        $ol = $this->Order->fetchShippingOrders($status, $date);
        if ($ol->valid()) {
            foreach ($ol as $orderId) {
                $this->statusChange($orderId, 'Ship');
            }
        }
        exit();
    }


    /**
     * Stub function for the handling of the notification cron task
     *
     * @param type $param
     */
    public function notification($param) {
        exit;
    }

    public function archiveOrders() {
        $list = $this->Order->fetchArchivingOrderInList();
        foreach ($list as $orderNumber => $id) {
            $this->request->params['named']['robot'] = TRUE;
            $this->statusChange($id, 'Archive');
        }
    }


    public function shippingLabels($id = '5460d867-48f8-4ab4-abd4-04c547139427') {
        $Label = ClassRegistry::init('Label');
        $this->set('order', $this->Order->getOrderForPrint($id));
        $this->set('orderId', $id);
        $this->set('labelList', $Label->labelList($id));
        $this->layout = 'sidebar';
    }

    public function newLabel($orderId) {
        $order = $this->Order->getOrderForPrint($orderId);
        $data = array('Label' => array(
            'id' => NULL,
            'order_id' => $orderId,
            'name' => 'Un-named Label',
            'items' => $order['items']
        ));
        $this->request->data = Hash::insert($data, 'Label.items.{n}.include', '1');
        $this->layout = 'ajax';
        $this->render('/Elements/edit_shipping_label');
    }

    /**
     * Save new setting for inclusion of this order on the invoice
     *
     * This is an ajax call point
     *
     * @param string $id Order id
     * @param int $exclusion exclusion setting
     */
    public function updateExclusion($id, $exclusion) {
        $this->Order->id = $id;
        if ($this->Order->saveField('exclude', $exclusion)) {
            $this->Session->setFlash('Change saved', 'flash_success');
        } else {
            $this->Session->setFlash('The change was not saved', 'flash_error');
        }
        $this->layout = 'ajax';
        $this->render('/AppAjax/flash_out');
    }

    public function testMe() {
        $this->archiveOrders();
    }
}