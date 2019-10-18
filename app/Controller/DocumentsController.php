<?php

App::uses('AppController', 'Controller');

/**
 * Documents Controller
 *
 * @property Document $Document
 */
class DocumentsController extends AppController {

	function beforeFilter() {
		parent::beforeFilter();
		//establish access patterns
		$this->accessPattern['Manager'] = array('all');
		$this->accessPattern['Buyer'] = array();
		$this->accessPattern['Guest'] = array();
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
		$this->Document->recursive = 0;
		$this->set('documents', $this->paginate());
		$this->ddd($this->request->data);
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Document->exists($id)) {
			throw new NotFoundException(__('Invalid document'));
		}
		$options = array('conditions' => array('Document.' . $this->Document->primaryKey => $id));
		$this->set('document', $this->Document->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Document->create();
			if ($this->Document->save($this->request->data)) {
				$this->Flash->set(__('The document has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->set(__('The document could not be saved. Please, try again.'));
			}
		}
		$orders = $this->Document->Order->find('list');
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
		if (!$this->Document->exists($id)) {
			throw new NotFoundException(__('Invalid document'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Document->save($this->request->data)) {
				$this->Flash->set(__('The document has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->set(__('The document could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Document.' . $this->Document->primaryKey => $id));
			$this->request->data = $this->Document->find('first', $options);
		}
		$orders = $this->Document->Order->find('list');
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

		$this->Document->id = $id;
		if (!$this->Document->exists()) {
			$r = array('result' => FALSE);
			if($this->request->is('post')){
				throw new NotFoundException(__('Invalid document'));
			}
		} else {
			$this->request->onlyAllow('post', 'delete');
			if ($this->Document->delete()) {
				$r = array('result' => TRUE);
			} else {
				$r = array('result' => FALSE);
			}

			if ($this->request->is('post')) {
				if ($r['result']) {
					$this->Flash->set(__('Document deleted'));
				} else {
					$this->Flash->set(__('Document was not deleted'));
				}
				$this->redirect(array('action' => 'index'));
			}
		}

		$this->layout = 'ajax';
		$this->set('r', $r);
		$this->render('/Elements/show_flash');
	}

	public function new_doc() {
		$this->layout = 'ajax';
		$this->render('/Elements/Doc/new_doc');
	}
	
	/**
	 * 
	 * 
	 * @param type $order_id
	 */
	public function order($order_id) {
		$docs = $this->Document->find('all', array(
			'recursive' => -1,
			'conditions' => array(
				'Document.order_id' => $order_id
			)
		));
		if (!empty($docs)) {
			foreach ($docs as $doc) {
				$this->request->data['Document'][] = $doc['Document'];
			}
		}
		$this->Document->Order->id = $order_id;
		$this->request->data['Order'] = array(
			'id' => $order_id,
			'order_number' => $this->Document->Order->field('order_number'));
//		$this->ddd($this->request->data);
		$this->layout = 'ajax';
		$this->render('/Elements/Doc/document_table');
	}
	
	/**
	 * Save the documents form
	 * 
	 * Requires trd be set
	 * Redirect to referrer
	 */
	public function save() {
		$w = $this->request->data['Document'];
		foreach ($w as $index => $record) {
			if($record['img_file'] == ''){
				unset($this->request->data['Document'][$index]);
			}
			if($record['id'] != '' && $record['img_file']['name'] == ''){
				unset($this->request->data['Document'][$index]['img_file']);
			}
			if($record['order_id'] == '' && isset($this->request->data['Order']['id'])){
				$this->request->data['Document'][$index]['order_id'] = $this->request->data['Order']['id'];
			}
		}
//		$this->ddd($this->request->data, 'trd');
//		die;
		$this->Session->setFlash('bummer - TRD empty', 'flash_error');
		if(!empty($this->request->data)){
			$save =$this->Document->saveMany($this->request->data['Document']);
			if($save) {
				$this->Session->setFlash('Saved the Documents', 'flash_success');
			} else {
				$this->Session->setFlash('Document save failed', 'flash_error');
			}
		}
		$this->redirect($this->referer());
	}

	/**
	 * Download file based upon provided link
	 * 
 	 * @param string $path the path to the file item
	 * @return file the downloaded file
 	 */
	public function sendFile($path) {
		$path = implode('/', func_get_args());
		$this->response->file($path, array('download' => TRUE));
		// Return response object to prevent controller from trying to render
		// a view
		return $this->response;
	}
}
