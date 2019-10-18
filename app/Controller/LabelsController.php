<?php
App::uses('AppController', 'Controller');
App::uses('FileExtension', 'Lib');

/**
 * Labels Controller
 *
 * @property Label $Label
 */
class LabelsController extends AppController {

	public function beforeFilter() {
        parent::beforeFilter();

		//establish access patterns
		$this->accessPattern['Manager'] = array ('all');
		$this->accessPattern['Buyer'] = array ();
		$this->accessPattern['Guest'] = array ();
		$this->accessPattern['AdminsManager'] = array ('all');
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
		$this->Label->recursive = 0;
		$this->set('labels', $this->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Label->exists($id)) {
			throw new NotFoundException(__('Invalid label'));
		}
		$options = array('conditions' => array('Label.' . $this->Label->primaryKey => $id));
		$this->set('label', $this->Label->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Label->create();
			if ($this->Label->save($this->request->data)) {
				$this->Flash->set(__('The label has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->set(__('The label could not be saved. Please, try again.'));
			}
		}
		$orders = $this->Label->Order->find('list');
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
		if (!$this->Label->exists($id)) {
			throw new NotFoundException(__('Invalid label'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Label->save($this->request->data)) {
				$this->Flash->set(__('The label has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->set(__('The label could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Label.' . $this->Label->primaryKey => $id));
			$this->request->data = $this->Label->find('first', $options);
		}
		$orders = $this->Label->Order->find('list');
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
		$this->Label->id = $id;
		if (!$this->Label->exists()) {
			throw new NotFoundException(__('Invalid label'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->Label->delete()) {
			$this->Flash->set(__('Label deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Flash->set(__('Label was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
	
	/**
	 * Save the newly specified shipping label
	 */
	public function saveNewLabel() {
		if ($this->request->is('post')) {
			if ($this->Label->saveLabel($this->request->data)) {
				$this->request->data['Label']['id'] = $this->Label->getInsertID();
				$this->Flash->success('The new label was saved.');
			} else {
				$this->Flash->error('The label couldn\'t be saved. Please try again.');
			}
		}
		$this->redirect(array('controller' => 'orders', 'action' => 'shippingLabels', $this->request->data['Label']['order_id']));
	}
	
	/**
	 * Save the newly specified shipping label
	 */
	public function editLabel($id = NULL) {
		if ($id != NULL && !$this->Label->exists($id)) {
			throw new NotFoundException(__('Invalid label id. Label does not exist.'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Label->saveLabel($this->request->data)) {
				$this->Flash->success(__('The label has been saved'));
				$this->redirect(array('controller' => 'orders', 'action' => 'shippingLabels', $this->request->data['Label']['order_id']));
			} else {
				$this->Flash->error(__('The label could not be saved. Please, try again.'));
			}
		}
		$this->request->data = $this->Label->fetchLabel($id);
		$this->layout = 'ajax';
		$this->render('/Elements/edit_shipping_label');
	}
	
	/**
	 * Delete the indicated label
	 * 
	 * @param string $id
	 * @throws NotFoundException
	 */
	public function removeLable($id) {
		$this->Label->id = $id;
		if (!$this->Label->exists()) {
			throw new NotFoundException(__('Invalid label'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->Label->delete()) {
			$this->Flash->success(__('Label deleted'));
		} else {
			$this->Flash->error(__('Label was not deleted'));
		}
		$this->layout = 'ajax';
		$this->render('/AppAjax/flash_out');
	}
	
	public function printLabel($id) {
		if(FileExtension::hasExtension($id)){
			$this->layout = '4x6';
			$id = FileExtension::stripExtension($id);
		}
		$items = $this->Label->fetchLabel($id);
		$order = $this->Label->Order->getOrderForPrint($items['Label']['order_id']);
		$this->set(compact('items', 'order'));
		$this->render('label');
	}
}
