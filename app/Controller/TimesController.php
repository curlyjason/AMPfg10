<?php
App::uses('AppController', 'Controller');
/**
 * Times Controller
 *
 * @property Time $Time
 */
class TimesController extends AppController {

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->Time->recursive = 0;
		$this->set('times', $this->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Time->exists($id)) {
			throw new NotFoundException(__('Invalid time'));
		}
		$options = array('conditions' => array('Time.' . $this->Time->primaryKey => $id));
		$this->set('time', $this->Time->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Time->create();
			if ($this->Time->save($this->request->data)) {
				$this->Session->setFlash(__('The time has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The time could not be saved. Please, try again.'));
			}
		}
		$users = $this->Time->User->find('list');
		$projects = $this->Time->Project->find('list');
		$this->set(compact('users', 'projects'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->Time->exists($id)) {
			throw new NotFoundException(__('Invalid time'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Time->save($this->request->data)) {
				$this->Session->setFlash(__('The time has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The time could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Time.' . $this->Time->primaryKey => $id));
			$this->request->data = $this->Time->find('first', $options);
		}
		$users = $this->Time->User->find('list');
		$projects = $this->Time->Project->find('list');
		$this->set(compact('users', 'projects'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Time->id = $id;
		if (!$this->Time->exists()) {
			throw new NotFoundException(__('Invalid time'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->Time->delete()) {
			$this->Session->setFlash(__('Time deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Time was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
