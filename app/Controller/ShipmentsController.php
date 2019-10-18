<?php

App::uses('AppController', 'Controller');

/**
 * Shipments Controller
 *
 * @property Shipment $Shipment
 */
class ShipmentsController extends AppController {
	

    public function beforeFilter() {
        parent::beforeFilter();
		//establish access patterns
		$this->accessPattern['Manager'] = array ('all');
		$this->accessPattern['Buyer'] = array ('index', 'listTracking');
		$this->accessPattern['Guest'] = array ('index', 'listTracking');
		$this->accessPattern['Warehouses'] = array ('index', 'listTracking');
    }
	
    public function isAuthorized($user) {
        return $this->authCheck($user, $this->accessPattern);
    }

    /**
     * index method
     *
     * @return void
     */
    public function index() {
        $this->Shipment->recursive = 0;
        $this->set('shipments', $this->paginate());
    }

    /**
     * view method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function view($id = null) {
        if (!$this->Shipment->exists($id)) {
            throw new NotFoundException(__('Invalid shipment'));
        }
        $options = array('conditions' => array('Shipment.' . $this->Shipment->primaryKey => $id));
        $this->set('shipment', $this->Shipment->find('first', $options));
    }

    /**
     * add method
     *
     * @return void
     */
    public function add() {
        if ($this->request->is('post')) {
            $this->Shipment->create();
            if ($this->Shipment->save($this->request->data)) {
                $this->Flash->set(__('The shipment has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Flash->set(__('The shipment could not be saved. Please, try again.'));
            }
        }
        $orders = $this->Shipment->Order->find('list');
        $this->set(compact('orders'));
    }

    /**
     * edit method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function edit($id = null) {
        if (!$this->Shipment->exists($id)) {
            throw new NotFoundException(__('Invalid shipment'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Shipment->save($this->request->data)) {
                $this->Flash->set(__('The shipment has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Flash->set(__('The shipment could not be saved. Please, try again.'));
            }
        } else {
            $options = array('conditions' => array('Shipment.' . $this->Shipment->primaryKey => $id));
            $this->request->data = $this->Shipment->find('first', $options);
        }
        $orders = $this->Shipment->Order->find('list');
        $this->set(compact('orders'));
    }

    /**
     * delete method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function delete($id = null) {
        $this->Shipment->id = $id;
        if (!$this->Shipment->exists()) {
            throw new NotFoundException(__('Invalid shipment'));
        }
        $this->request->allowMethod(['post', 'delete']);
        if ($this->Shipment->delete()) {
            $this->Flash->set(__('Shipment deleted'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Flash->set(__('Shipment was not deleted'));
        $this->redirect(array('action' => 'index'));
    }
    
    public function listTracking(){
        $this->index();
        $this->render('index');
    }

    /**
     * Edit an order's shipment from the status page
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function editOrderShipment($order_id = null) {
		$this->Order = ClassRegistry::init('Order');
		$this->layout = 'ajax';
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Shipment->save($this->request->data)) {
                $this->Flash->set(__('The shipment has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Flash->set(__('The shipment could not be saved. Please, try again.'));
            }
        } else {
            $options = array('conditions' => array('Shipment.order_id' => $order_id));
			$this->request->data = $this->Shipment->find('first', $options);
			if($this->request->data == array()){
				throw new NotFoundException('no shipment found');
			}
            $this->request->data = $this->Shipment->find('first', $options);
			$myAddresses = ($this->User->getSecureList($this->Auth->user('id'), 'myAddresses'));
			$connectedAddresses = ($this->User->getSecureList($this->Auth->user('id'), 'connectedAddresses'));
			$thirdParty = ($this->User->getSecureList($this->Auth->user('id'), 'thirdParty'));
			$pageHeading = $title_for_layout = 'Shipping Address Edit';
			$carrier = $this->Order->carrier;
			$UPS = $this->Order->method['UPS'];
			$FedEx = $this->Order->method['FedEx'];
			$Other = $this->Order->method['Other'];
			$method = array($this->Order->method[$this->request->data['Shipment']['carrier']]);
			$shipmentBillingOptions = $this->getShipmentBillingOptions();

			$this->setBasicAddressSelects();
			$this->set(compact(
					'myAddresses', 
					'connectedAddresses', 
					'pageHeading', 
					'title_for_layout', 
					'carrier', 
					'method',
					'UPS',
					'FedEx',
					'Other',
					'shipmentBillingOptions',
					'thirdParty'));
        }
    }
	
	/**
	 * Save the shipment from an order from the status page
	 * 
	 * @param array $data the serialized data from the ajax call
	 */
	public function saveOrderShipment() {
		$this->layout = 'ajax';
        $order_id = $this->Shipment->saveOrderShipment($this->request->data);
		if($order_id) {
            $data = $this->Shipment->Order->find('first', array(
                'conditions' => array(
                    'Order.id' => $order_id
                )
            ));
        } else {
            $this->set('json', FALSE);
            $this->render('/Common/echo_json');
        }
        $this->set('data', $data);
        $this->render('ship_cell');
	}

}
