<?php
App::uses('AppController', 'Controller');
/**
 * OrderItems Controller
 *
 * @property OrderItem $OrderItem
 */
class OrderItemsController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
		//establish access patterns
		$this->accessPattern['Manager'] = array ('all');
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
		$this->OrderItem->recursive = 0;
		$this->set('orderItems', $this->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->OrderItem->exists($id)) {
			throw new NotFoundException(__('Invalid order item'));
		}
		$options = array('conditions' => array('OrderItem.' . $this->OrderItem->primaryKey => $id));
		$this->set('orderItem', $this->OrderItem->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->OrderItem->create();
			if ($this->OrderItem->save($this->request->data)) {
				$this->Flash->set(__('The order item has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->set(__('The order item could not be saved. Please, try again.'));
			}
		}
		$orders = $this->OrderItem->Order->find('list');
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
		if (!$this->OrderItem->exists($id)) {
			throw new NotFoundException(__('Invalid order item'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->OrderItem->save($this->request->data)) {
				$this->Flash->set(__('The order item has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->set(__('The order item could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('OrderItem.' . $this->OrderItem->primaryKey => $id));
			$this->request->data = $this->OrderItem->find('first', $options);
		}
		$orders = $this->OrderItem->Order->find('list');
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
		$this->OrderItem->id = $id;
		if (!$this->OrderItem->exists()) {
			throw new NotFoundException(__('Invalid order item'));
		}
		$this->request->allowMethod(['post', 'delete']);
		if ($this->OrderItem->delete()) {
			$this->Flash->set(__('Order item deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Flash->set(__('Order item was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
