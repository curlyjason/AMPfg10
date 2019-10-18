<?php
App::uses('AppController', 'Controller');
App::uses('FileExtension', 'Lib');

/**
 * Replenishments Controller
 *
 * @property Replenishment $Replenishment
 */
class ReplenishmentsController extends AppController {

    public $jsonReturn = array();

    public function beforeFilter() {
        parent::beforeFilter();
        //establish access patterns
        $this->accessPattern['Manager'] = array ('all');
        $this->accessPattern['StaffManager'] = array ('all');
    }

    public function isAuthorized($user) {
        return $this->authCheck($user, $this->accessPattern);
    }

    /**
     * index method
     *
     * @return void
     */
    public function index($within = 0) {
        $this->Replenishment->recursive = 0;
        $this->set('replenishments', $this->paginate());
    }

    /**
     * view method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function view($id = null) {
        if (!$this->Replenishment->exists($id)) {
            throw new NotFoundException(__('Invalid replenishment'));
        }
        $options = array('conditions' => array('Replenishment.' . $this->Replenishment->primaryKey => $id));
        $this->set('replenishment', $this->Replenishment->find('first', $options));
    }

    /**
     * add method
     *
     * @return void
     */
    public function add() {
        if ($this->request->is('post')) {
            $this->Replenishment->create();
            if ($this->Replenishment->save($this->request->data)) {
                $this->Flash->set(__('The replenishment has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Flash->set(__('The replenishment could not be saved. Please, try again.'));
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
        if (!$this->Replenishment->exists($id)) {
            throw new NotFoundException(__('Invalid replenishment'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Replenishment->save($this->request->data)) {
                $this->Flash->set(__('The replenishment has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Flash->set(__('The replenishment could not be saved. Please, try again.'));
            }
        } else {
            $options = array('conditions' => array('Replenishment.' . $this->Replenishment->primaryKey => $id));
            $this->request->data = $this->Replenishment->find('first', $options);
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
        $this->Replenishment->id = $id;
        if (!$this->Replenishment->exists()) {
            throw new NotFoundException(__('Invalid replenishment'));
        }
        $this->request->onlyAllow('post', 'delete');
        if ($this->Replenishment->delete()) {
            $this->Flash->set(__('Replenishment deleted'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Flash->set(__('Replenishment was not deleted'));
        $this->redirect(array('action' => 'index'));
    }

    /* =============================================================
     * REPLENISHMENT AGGRAGATOR TOOLS
     ============================================================ */

    public function manage_replenishments() {
        $this->layout = 'timed_simple';
        $pageHeading = $title_for_layout = 'Manage Replenishments';
        $this->set(compact('pageHeading', 'title_for_layout'));
        $this->set('editGrain', $this->Replenishment->getReplenishments());
        debug($this->viewVars['editGrain']);
    }

    /**
     * Update an ReplenishmentItems quantity (and all related relevant values)
     *
     * This is the typical entry point for an Replenishment adjustment
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
    public function updateReplenishmentItem($id, $qty) {
        if ($this->request->is('ajax')) {
            $this->autoRender = false;
        }

        // start by reading in the current order item
        $orderItem = $this->Replenishment->ReplenishmentItem->find('first', array(
            'conditions' => array('ReplenishmentItem.id' => $id),
            'contain' => false
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
            $this->removeReplenishmentItem($id, $orderItem['ReplenishmentItem']['replenishment_id'], $orderItem['ReplenishmentItem']['item_id']);
            return;
        }

        //set orderItem to reflect quantity change
        $orderItem['ReplenishmentItem']['quantity'] = $qty;
        $orderItem['ReplenishmentItem']['subtotal'] = $qty * $orderItem['ReplenishmentItem']['price'];
        $orderItem['ReplenishmentItem']['weight_total'] = $qty * $orderItem['ReplenishmentItem']['weight'];

        // save the revised record
        $this->Replenishment->ReplenishmentItem->create();
        $result = $this->Replenishment->ReplenishmentItem->save($orderItem);

        // update other models associated with Replenishments
        $this->Item = ClassRegistry::init('Item');
        $available = $this->Item->managePendingQty($orderItem['ReplenishmentItem']['item_id']);

        // let js know about the save result and pass on the saved data
        $orderItem['ReplenishmentItem']['result'] = $result;
        $this->jsonReturn = array_merge($this->jsonReturn, array('Item' => $orderItem), $available);
        $this->updateReplenishment($orderItem['ReplenishmentItem']['replenishment_id']);
    }

    /**
     * Remove the specified Item from the Replenishment
     *
     * And sort out all the other bits
     * like uncommited inventory and the
     * order wrapper.
     *
     * @param string $id The orderitem to delete
     * @param string $replenishment_id The order id
     */
    private function removeReplenishmentItem($id, $replenishment_id, $item_id) {
        $this->Replenishment->ReplenishmentItem->delete($id);
        $this->Item = ClassRegistry::init('Item');
        $this->jsonReturn = array_merge($this->jsonReturn, array('deletedItem' => $id),$this->Item->managePendingQty($item_id));
        $this->updateReplenishment($replenishment_id);
        return;
    }

    /**
     * Establish all the Replenishment values or delete the order
     *
     * Read in the Replenishment Items and related data
     * Set all the accumulator values in the order
     * Save the updated Replenishment
     * Resolve the user's budget
     * Return json data reflecting the new values
     *
     * @param string $id order id
     * @param boolean $remove indicates a request to delete an order
     * @return mixed json data or false if the order is zapped
     * @throws NotFoundException
     */
    public function updateReplenishment($id, $remove = false) {
        if (!$this->Replenishment->exists($id)) {
            throw new NotFoundException(__('Invalid order'));
        }
        $order = $this->Replenishment->find('first', array(
            'conditions' => array(
                'Replenishment.id' => $id),
            'fields' => array(
                'id',
                'user_id',
                'status',
                'vendor_company',
                'weight',
                'order_item_count',
                'subtotal',
                'tax',
                'shipping',
                'total',
                'company',
            ),
            'contain' => array(
                'ReplenishmentItem' => array(
                    'fields' => array(
                        'SUM(weight)',
                        'SUM(subtotal)',
                        'COUNT(id)'
                    )
                )
            )
        ));
        if (empty($order['ReplenishmentItem']) || $remove) {
            $this->clearReplenishment($id);
        } else {
            $order['Replenishment']['subtotal'] = $order['ReplenishmentItem'][0]['ReplenishmentItem'][0]['SUM(subtotal)'];
            $order['Replenishment']['weight'] = $order['ReplenishmentItem'][0]['ReplenishmentItem'][0]['SUM(weight)'];
            $order['Replenishment']['total'] = $order['Replenishment']['subtotal'];
            $order['Replenishment']['order_item_count'] = $order['ReplenishmentItem'][0]['ReplenishmentItem'][0]['COUNT(id)'];
            $this->Replenishment->create();
            $this->Replenishment->save($order);
        }

        // this only works on the logged in user. May not have meaning in this context
        $this->jsonReturn = array_merge($this->jsonReturn, $order);
        echo json_encode($this->jsonReturn);
    }

    /**
     * When a replenisment has no more items it should be deleted
     *
     * Replenishment and Shippments should be dumped
     * Make sure ReplenishmentItems are gone too
     *
     * @param string $id The order to eliminate
     * @return json Data to return to update page display
     */
    private function clearReplenishment($id) {
        $items = $this->Replenishment->ReplenishmentItem->find('all', array(
            'conditions' => array(
                'ReplenishmentItem.replenishment_id' => $id
            ),
            'fields' => array('ReplenishmentItem.id', 'ReplenishmentItem.item_id')
        ));
        if (!empty($items)) {
            $this->Item = ClassRegistry::init('Item');
            foreach ($items as $item) {
                $this->Replenishment->ReplenishmentItem->delete($item['ReplenishmentItem']['id']);
                $this->Item->manageUncommitted($item['ReplenishmentItem']['item_id']);
                $this->Item->managePendingQty($item['ReplenishmentItem']['item_id']);
            }
        }
        $this->Replenishment->delete($id);
        $this->jsonReturn = array_merge($this->jsonReturn, array('deletedReplenishment' => $id));
    }

    /* =============================================================
     * REPLENISHMENT CREATION AND UI BITS
     ============================================================ */

    public function createReplenishment_old($within = 0) {
        if ($this->request->is('post')) {
            //fix user connection
            $this->request->data['Replenishment']['user_id'] = $this->Auth->user('id');
            $this->request->data['Replenishment']['order_item_count'] = count($this->request->data['ReplenishmentItem']);
            foreach ($this->request->data['ReplenishmentItem'] as $index => $item) {
                $this->request->data['ReplenishmentItem'][$index]['price'] = str_replace('$', '', $this->request->data['ReplenishmentItem'][$index]['price']);
                $this->request->data['ReplenishmentItem'][$index]['subtotal'] = $this->request->data['ReplenishmentItem'][$index]['price'] * $this->request->data['ReplenishmentItem'][$index]['quantity'];
            }
            $result = $this->Replenishment->saveAll($this->request->data);
            if ($result) {
                $number = $this->Replenishment->getReplenishmentNumber($this->Replenishment->id);
                if ($number) {
                    $this->Replenishment->saveField('order_number', $number);
                }
                // update Item model pending_qty vals
                foreach($this->request->data['ReplenishmentItem'] as $index => $lineItem) {
                    $this->Replenishment->ReplenishmentItem->Item->managePendingQty($lineItem['item_id']);
                }
            } else {
                $this->Flash->set('The data was not saved. Please try again');
                $this->redirect($this->referer());
            }
            $this->redirect(array('controller' => 'clients', 'action' => 'status', $this->Replenishment->id));
        }
        $this->Address = ClassRegistry::init('Address');
        $addressId = $this->Address->field('id', array('name' => 'Amp Printing'));
        if (!empty($addressId)) {
            // There is no meaningful default shipping address at this point. rem'd the call out
        } else {
            $defaultShipping = array();
        }
        extract($this->Replenishment->ReplenishmentItem->Item->needsReorder($within)); // returns $lowStock and $itemData

        $this->set(compact('lowStock', 'itemData', 'defaultShipping', 'otherVendors', 'vendorAccess'));
    }

    /**
     * Generate the single-vender ui for making replenishments
     *
     * This is the simplified version where every customer is their own vendor.
     * Choose one of these guys and you see all their products, regardless of
     * inventory level. Then you create the replenisment as before (with an ajaxy table)
     *
     * @param type $vendor_id
     */
    public function createReplenishment($vendor_id = FALSE) {
        $this->Item = ClassRegistry::init('Item');
        if ($this->request->is('post')) {
            //fix user connection
            $this->request->data['Replenishment']['user_id'] = $this->Auth->user('id');
            $this->request->data['Replenishment']['order_item_count'] = count($this->request->data['ReplenishmentItem']);
            foreach ($this->request->data['ReplenishmentItem'] as $index => $item) {
                $this->request->data['ReplenishmentItem'][$index]['price'] = str_replace('$', '', $this->request->data['ReplenishmentItem'][$index]['price']);
                $this->request->data['ReplenishmentItem'][$index]['subtotal'] = $this->request->data['ReplenishmentItem'][$index]['price'] * $this->request->data['ReplenishmentItem'][$index]['quantity'];
            }
            $result = $this->Replenishment->saveAll($this->request->data);
            if ($result) {
                $number = $this->Replenishment->getReplenishmentNumber($this->Replenishment->id);
                if ($number) {
                    $this->Replenishment->saveField('order_number', $number);
                }
                // update Item model pending_qty vals
                foreach($this->request->data['ReplenishmentItem'] as $index => $lineItem) {
                    $this->Item->managePendingQty($lineItem['item_id']);
                }
            } else {
                $this->Flash->set('The data was not saved. Please try again');
                $this->redirect($this->referer());
            }
            $this->redirect(array('controller' => 'clients', 'action' => 'status', $this->Replenishment->id));
        }
        $itemData = array();
        if($vendor_id){
            extract($this->Item->findItemsByVendorId($vendor_id, 'active'));
        }
        $this->Address = ClassRegistry::init('Address');
        $vendors = $this->Address->fetchCustomerVendorList();
        $addressId = $this->Address->field('id', array('name' => 'Amp Printing'));
        $defaultShipping = array();

        $this->set(compact('defaultShipping', 'otherVendors', 'vendorAccess', 'vendors', 'itemData'));
    }

    /**
     * Replenishment List for Warehouse user
     */
    public function replenishmentList() {
        $pageHeading = $title_for_layout = 'Replenishment List';
        $this->set(compact('title_for_layout', 'pageHeading'));

        $replenishmentList = $this->Replenishment->fetchReplenishmentList();

        $this->set(compact('title_for_layout', 'pageHeading', 'replenishmentList'));
        $this->render('/Clients/replenishment_list');
    }

    public function statusChange($id, $action) {
        if (!$this->Replenishment->exists($id)) {
            throw new NotFoundException(__('Invalid replenishment'));
        }
        $replenishment = $this->Replenishment->find('first', array(
            'conditions' => array(
                'Replenishment.id' => $id
            ),
            'contain' => array(
                'ReplenishmentItem' => array(
                    'fields' => array(
                        'SUM(ReplenishmentItem.pulled) as totalReplenishments',
                        'COUNT(ReplenishmentItem.id) as countItems'
                    )
                )
            )
        ));
        //check to ensure all items have been received
        $totalReplenishment = $replenishment['ReplenishmentItem'][0]['ReplenishmentItem'][0]['totalReplenishments'];
        $countItems = $replenishment['ReplenishmentItem'][0]['ReplenishmentItem'][0]['countItems'];
        if($totalReplenishment != $countItems){
            $this->Flash->error(__('You must receive all items before Completing the Replenishment'));
            $this->redirect($this->referer());
        }

        //all items WERE received, proceed
        //set the result status
        $status = $this->orderEventMap[$action];

        $replenishment['Replenishment']['status'] = $status;
        unset ($replenishment['ReplenishmentItem']);

        if(!$this->Replenishment->save($replenishment)){
            $this->Flash->error(__('The Replenishment failed to update, please try again'));
        }
        $this->Flash->success(__('The Replenishment was completed'));
        $this->redirect($this->referer());
    }

    public function fetchItemRow(){
        //ensure method was provided data
        if (empty($this->request->data)) {
            array_merge($this->jsonReturn, array(
                'Error' => 'Failed to find the requested item, please try again.'
            ));
            return json_encode($this->jsonReturn);
        }

        $this->layout = 'ajax';
        $item = $this->request->data;
        $this->set(compact('item'));
        //

    }
    /**
     *
     * @param string $id ReplenishmentItem id
     * @param string $unit New unit data
     */
    function writeNewPoPrice(){
        extract($this->request->data);

        if ($this->Replenishment->ReplenishmentItem->exists($id)) {
            $this->request->data['save'] = $this->Replenishment->ReplenishmentItem->save($this->request->data);
        } else {
            $this->request->data['save'] = false;
        }

        if ($this->request->data['save']) {
            $this->Replenishment->ReplenishmentItem->id = $id;
            $qty = $this->Replenishment->ReplenishmentItem->field('quantity');
            $this->jsonReturn = array_merge($this->jsonReturn, $this->request->data);
            $this->updateReplenishmentItem($id, $qty);
        }
        $this->autoRender = false;
    }

    public function findItemsForReplenishments() {
        $this->layout = 'ajax';
        $this->set('findItems',$this->Replenishment->ReplenishmentItem->Item->findItemsByQuery($this->request->data['Replenishment']['search']));
    }

    public function printReplenishment($id) {
        if(FileExtension::hasExtension($id)){
            $this->layout = 'default';
            $id = FileExtension::stripExtension($id);
        } else {
            $this->layout = 'print_accumulator';
        }
        $data = $this->Replenishment->getReplenishmentForPrint($id);
        $type = 'replenishment';
        $headerRow = $data['headerRow'];
        $chunk = $data['chunk'];
        $this->set(compact('data', 'chunk', 'type', 'headerRow'));
    }

}
