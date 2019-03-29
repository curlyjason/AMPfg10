<?php
App::uses('AppController', 'Controller');
/**
 * ReplenishmentItems Controller
 *
 * @property ReplenishmentItem $ReplenishmentItem
 */
class ReplenishmentItemsController extends AppController {

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
	public function index() {
		$this->ReplenishmentItem->recursive = 0;
		$this->set('replenishmentItems', $this->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->ReplenishmentItem->exists($id)) {
			throw new NotFoundException(__('Invalid replenishment item'));
		}
		$options = array('conditions' => array('ReplenishmentItem.' . $this->ReplenishmentItem->primaryKey => $id));
		$this->set('replenishmentItem', $this->ReplenishmentItem->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->ReplenishmentItem->create();
			if ($this->ReplenishmentItem->save($this->request->data)) {
				$this->Session->setFlash(__('The replenishment item has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The replenishment item could not be saved. Please, try again.'));
			}
		}
		$replenishments = $this->ReplenishmentItem->Replenishment->find('list');
		$this->set(compact('replenishments'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->ReplenishmentItem->exists($id)) {
			throw new NotFoundException(__('Invalid replenishment item'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->ReplenishmentItem->save($this->request->data)) {
				$this->Session->setFlash(__('The replenishment item has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The replenishment item could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('ReplenishmentItem.' . $this->ReplenishmentItem->primaryKey => $id));
			$this->request->data = $this->ReplenishmentItem->find('first', $options);
		}
		$replenishments = $this->ReplenishmentItem->Replenishment->find('list');
		$this->set(compact('replenishments'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->ReplenishmentItem->id = $id;
		if (!$this->ReplenishmentItem->exists()) {
			throw new NotFoundException(__('Invalid replenishment item'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->ReplenishmentItem->delete()) {
			$this->Session->setFlash(__('Replenishment item deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Replenishment item was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
	
    /**
     * 
     * @param string $id ReplenishmentItem id
     * @param string $unit New unit data
     */
    function writeNewPoUnit(){
	extract($this->request->data);
	
	if ($this->ReplenishmentItem->exists($id)) {
	    $this->request->data['save'] = $this->ReplenishmentItem->save($this->request->data);
	} else {
	    $this->request->data['save'] = false;
	}
	
	$this->autoRender = false;
	echo json_encode($this->request->data);
    }

    /**
     * 
     * @param string $id ReplenishmentItem id
     * @param string $unit New unit data
     */
    function writeNewPoQty(){
	extract($this->request->data);
	
	if ($this->ReplenishmentItem->exists($id)) {
	    $this->request->data['save'] = $this->ReplenishmentItem->save($this->request->data);
	} else {
	    $this->request->data['save'] = false;
	}
	
	if ($this->request->data['save']) {
	    $this->ReplenishmentItem->id = $id;
	    $itemId = $this->ReplenishmentItem->field('item_id');
	    $pending = $this->ReplenishmentItem->Item->managePendingQty($itemId);
	}
	
	$this->autoRender = false;
	echo json_encode(array_merge($this->request->data, $pending));
    }
    
}
