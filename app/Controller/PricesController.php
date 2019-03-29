<?php

App::uses('AppController', 'Controller');

/**
 * Prices Controller
 *
 * @property Price $Price
 */
class PricesController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
		//establish access patterns
		$this->accessPattern['AdminsManager'] = array ('all');
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
	public function index() {
		$this->Price->recursive = 0;
		$this->set('prices', $this->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Price->exists($id)) {
			throw new NotFoundException(__('Invalid price'));
		}
		$options = array('conditions' => array('Price.' . $this->Price->primaryKey => $id));
		$this->set('price', $this->Price->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Price->create();
			if ($this->Price->save($this->request->data)) {
				$this->Session->setFlash(__('The price has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The price could not be saved. Please, try again.'));
			}
		}
		$customers = $this->Price->Customer->find('list');
		$this->set(compact('customers'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->Price->exists($id)) {
			throw new NotFoundException(__('Invalid price'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Price->save($this->request->data)) {
				$this->Session->setFlash(__('The price has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The price could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Price.' . $this->Price->primaryKey => $id));
			$this->request->data = $this->Price->find('first', $options);
		}
		$customers = $this->Price->Customer->find('list');
		$this->set(compact('customers'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		if ($this->request->is('ajax')) {
			$this->autoRender = false;
			$ajax = true;
		}
		$this->Price->id = $id;
		if (!$this->Price->exists()) {
			throw new NotFoundException(__('Invalid price'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->Price->delete()) {
			if ($ajax) {
				echo true;
			} else {
				$this->Session->setFlash(__('Price deleted'));
				$this->redirect(array('action' => 'index'));
			}
		} else {
			if ($ajax) {
				echo false;
			} else {
				$this->Session->setFlash(__('Price was not deleted'));
				$this->redirect(array('action' => 'index'));
			}
		}
	}

/*
 * Ajax handler for quantity pull fee editing
 * another note
 */

	public function pullFees() {
		$this->layout = 'ajax';

		$customer_id = $this->request->data['id'];

		$feeTable = $this->Price->find('all', array('conditions' => array(
				'customer_id' => $customer_id
					),
					'order' => 'max_qty ASC'
				));

		$this->set(compact('feeTable', 'customer_id'));
	}
	
	public function savePullFees() {
		$this->autoRender = false;
//		$this->ddd($this->request->data,'TRD');
//		die;
			$count = 0;
			$data = array();
			$validationErrors = array();
			
			//Validate
			foreach ($this->request->data as $index => $priceRecord) {
				$this->Price->set($priceRecord);
				$validate = $this->Price->validates();
				$validationErrors = array_merge($validationErrors,$this->Price->validationErrors);
			}
			
			if(!empty($validationErrors)){
				//I have an error in validation
				echo json_encode($this->Price->fetchModelValidationMessage($validationErrors));
				return;
			} else {
				// Save
				$save = $this->Price->saveAll($this->request->data);
				if($save){
					$custId = $this->Price->field('customer_id');
//					$this->ddd($custId,'customer ID');
					$maxRecord = $this->Price->find('first', array(
						'conditions' => array(
							'customer_id' => $custId,
						),
						'order' => 'max_qty DESC',
						'contain' => false
					));
					$maxRecord['Price']['test_max_qty'] = 2000000000;
					$this->Price->save($maxRecord);
					$this->Session->setFlash('The pull fee pricing table saved.');
				}
				echo json_encode(array($save));
				return;
			}
	}

}
