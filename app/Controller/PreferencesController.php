<?php
App::uses('AppController', 'Controller');
/**
 * Preferences Controller
 *
 * @property Preference $Preference
 */
class PreferencesController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('add');
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
		$this->Preference->recursive = 0;
		$this->set('preferences', $this->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Preference->exists($id)) {
			throw new NotFoundException(__('Invalid preference'));
		}
		$options = array('conditions' => array('Preference.' . $this->Preference->primaryKey => $id));
		$this->set('preference', $this->Preference->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Preference->create();
			if ($this->Preference->save($this->request->data)) {
				$this->Session->setFlash(__('The preference has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The preference could not be saved. Please, try again.'));
			}
		}
		$users = $this->Preference->User->find('list');
		$this->set(compact('users'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->Preference->exists($id)) {
			throw new NotFoundException(__('Invalid preference'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Preference->save($this->request->data)) {
				$this->Session->setFlash(__('The preference has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The preference could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Preference.' . $this->Preference->primaryKey => $id));
			$this->request->data = $this->Preference->find('first', $options);
		}
		$users = $this->Preference->User->find('list');
		$this->set(compact('users'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Preference->id = $id;
		if (!$this->Preference->exists()) {
			throw new NotFoundException(__('Invalid preference'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->Preference->delete()) {
			$this->Session->setFlash(__('Preference deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Preference was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
