<?php

App::uses('AppController', 'Controller');
App::uses('UserController', 'Controller');

/**
 * Gateways Controller
 *
 * @property Gateway $Gateway
 */
class GatewaysController extends AppController {

// <editor-fold defaultstate="collapsed" desc="Properties">

	public $uses = array('Gateway', 'User', 'Order');
	public $id = '';
	public $gatekeeperMessage = '';
	public $record = array();
	public $actionUser = '';

// </editor-fold>

	public function beforeFilter() {
		parent::beforeFilter();
		//establish access patterns
		$this->accessPattern['Manager'] = array('all');
		$this->accessPattern['Buyer'] = array('all');
		$this->accessPattern['Guest'] = array('all');
		$this->accessPattern['AdminsManager'] = array('all');
	}

	public function isAuthorized($user) {
		return $this->authCheck($user, $this->accessPattern);
	}

	//============================================================
	// BASIC CRUD
	//============================================================

	/**
	 * index method
	 *
	 * @return void
	 */
	public function index() {
		$this->Gateway->recursive = 0;
		$this->set('gateways', $this->paginate());
	}

	/**
	 * view method
	 *
	 * @throws NotFoundException
	 * @param string $id
	 * @return void
	 */
	public function view($id = null) {
		if (!$this->Gateway->exists($id)) {
			throw new NotFoundException(__('Invalid gateway'));
		}
		$options = array('conditions' => array('Gateway.' . $this->Gateway->primaryKey => $id));
		$this->set('gateway', $this->Gateway->find('first', $options));
	}

	/**
	 * add method
	 *
	 * @return void
	 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Gateway->create();
			if ($this->Gateway->save($this->request->data)) {
				$this->Session->setFlash(__('The gateway has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The gateway could not be saved. Please, try again.'));
			}
		}
		$users = $this->Gateway->User->find('list');
		$this->set(compact('models', 'users'));
	}

	/**
	 * edit method
	 *
	 * @throws NotFoundException
	 * @param string $id
	 * @return void
	 */
	public function edit($id = null) {
		if (!$this->Gateway->exists($id)) {
			throw new NotFoundException(__('Invalid gateway'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Gateway->save($this->request->data)) {
				$this->Session->setFlash(__('The gateway has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The gateway could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Gateway.' . $this->Gateway->primaryKey => $id));
			$this->request->data = $this->Gateway->find('first', $options);
		}
		$users = $this->Gateway->User->find('list');
		$this->set(compact('models', 'users'));
	}

	/**
	 * delete method
	 *
	 * @throws NotFoundException
	 * @param string $id
	 * @return void
	 */
	public function delete($id = null) {
		$this->Gateway->id = $id;
		if (!$this->Gateway->exists()) {
			throw new NotFoundException(__('Invalid gateway'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->Gateway->delete()) {
			$this->Session->setFlash(__('Gateway deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Gateway was not deleted'));
		$this->redirect(array('action' => 'index'));
	}

	//============================================================
	// CORE GATEWAY FUNCTIONS
	//============================================================

	public function takeAction($id) {
		//Set the target record id to the property
		$this->id = $id;

		//Validate the gateway record and its targets
		if (!$this->gatekeeper()) {
			$this->Flash->error($this->gatekeeperMessage);
			$this->redirect(array('controller' => 'users', 'action' => 'login'));
		}

		//Set the user to the actionUser property and login the user
		$loggedIn = $this->Auth->login(array(
			'id' => $this->record['User']['id'],
			'username' => $this->record['User']['username'],
			'role' => $this->record['User']['role'],
			'password' => $this->record['User']['password']
		));
		$this->Session->write('Auth.User', $this->record['User']);
		$this->Session->write('Auth.User.ParentUser', $this->record['User']['ParentUser']);
		$this->requestAction(array('controller' => 'users', 'action' => 'initUser'));

		if (!$loggedIn) {
			$this->Session->setFlash('Login failed. Please contact your administrator');
			$this->redirect(array('controller' => 'Users', 'action' => 'login'));
		}
		//execute the action
		$baseUrl = array('controller' => $this->record['Gateway']['controller'], 'action' => $this->record['Gateway']['action']);
		if ($this->record['Gateway']['action'] == 'statusChange') {
			$baseUrl[] = $this->record['Gateway']['model_id'];
			$baseUrl[] = $this->request->query['status'];
			$baseUrl['robot'] = true;
		}
		$executed = $this->requestAction( $baseUrl );
		if (!$executed) {
			$this->Session->setFlash('Process failed. Please login to take this action.');
		} else {
			//if succeeded, set all matching actions to completed
			if ($this->completeAction()) {
				$this->Session->setFlash('Action succeeded, thanks!');
			} else {
				$this->Session->setFlash('Completion failed. Please inform your administrator.');
			}
		}
		$this->Auth->logout();
		$this->redirect(array('controller' => 'users', 'action' => 'login'));
	}

	/**
	 * Verify external link to gateway
	 * 
	 * Check existance of record
	 * Ensure gateway record modified within last thirty days
	 * Ensure controller and action actually exist
	 * Ensure the target record exists
	 * 
	 * @return boolean
	 */
	private function gatekeeper() {
		$this->record = $this->Gateway->find('first', array(
			'conditions' => array(
				'Gateway.id' => $this->id
			),
			'contain' => array(
				'User' => array(
					'ParentUser'
				)
			)
		));

		//check the record find
		if (empty($this->record)) {
			$this->gatekeeperMessage .= 'This link record is missing. Please login to take this action.';
			return FALSE;
		}

		//check for valid user on the gateway record
		if (empty($this->record['User']['id'])) {
			$this->gatekeeperMessage .= 'This user record is missing. Please login to take action.';
			return FALSE;
		}

		//fail if the gateway record is older than 30 days
		if (time() > (strtotime($this->record['Gateway']['modified']) + MONTH)) {
			$this->gatekeeperMessage .= 'This link has expired. Please login to take this action.';
			return FALSE;
		}

		//validate record completion
		if ($this->record['Gateway']['complete']) {
			$this->gatekeeperMessage .= 'This action has already been performed. Thanks!';
			return FALSE;
		}

		//validate the target controller and action
		if (!$this->gateCheck($this->record['Gateway']['controller'], $this->record['Gateway']['action'])) {
			$this->gatekeeperMessage .= 'This controller or action is invalid. Please login to perform this task.';
			return FALSE;
		}

		//validate the target record
		$this->loadModel($this->record['Gateway']['model_alias']);
		if (!$this->{$this->record['Gateway']['model_alias']}->exists($this->record['Gateway']['model_id'])) {
			$this->gatekeeperMessage .= 'The record you want to act upon no longer exists. Please login to review.';
			return FALSE;
		}

		//All tests passed, action is approved
		$this->gatekeeperMessage .= 'This action is valid.';
		return TRUE;
	}

	/**
	 * Mark the current gateway record and all matching gateway records as complete
	 * 
	 * @return boolean
	 */
	private function completeAction() {
		$matchingActions = $this->Gateway->find('all', array(
			'conditions' => array(
				'model_id' => $this->record['Gateway']['model_id'],
				'model_alias' => $this->record['Gateway']['model_alias'],
				'action' => $this->record['Gateway']['action'],
				'controller' => $this->record['Gateway']['controller']
			),
			'contain' => FALSE
		));
		foreach ($matchingActions as $index => $action) {
			$matchingActions[$index]['Gateway']['complete'] = 1;
		}
		return $this->Gateway->saveAll($matchingActions);
	}

}
