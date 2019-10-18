<?php

/**
 * Observer Controller
 *
 * Handle base Observer methods to create the eyes-on system
 *
 * @copyright     Copyright (c) Dreaming Mind (http://dreamingmind.com)
 * @package       app.Controller
 */
App::uses('AppController', 'Controller');

/**
 * Observer Controller
 *
 * Handle base Observer methods to create the eyes-on system
 *
 * @package		app.Controller
 * @property Observer $Observer
 */
class ObserversController extends AppController {

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
        $this->Observer->recursive = 0;
        $this->set('observers', $this->paginate());
    }

    /**
     * view method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function view($id = null) {
        if (!$this->Observer->exists($id)) {
            throw new NotFoundException(__('Invalid observer'));
        }
        $options = array('conditions' => array('Observer.' . $this->Observer->primaryKey => $id));
        $this->set('observer', $this->Observer->find('first', $options));
    }

    /**
     * add method
     *
     * @return void
     */
    public function add() {
        if ($this->request->is('post')) {
            $this->Observer->create();
            if ($this->Observer->save($this->request->data)) {
                $this->Flash->success(__('The observer has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Flash->error(__('The observer could not be saved. Please, try again.'));
            }
        }
        $users = $this->Observer->User->find('list');
        $userObservers = $this->Observer->UserObserver->find('list');
        $this->set(compact('users', 'userObservers'));
    }

    /**
     * edit method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function edit($id = null) {
        if (!$this->Observer->exists($id)) {
            throw new NotFoundException(__('Invalid observer'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Observer->save($this->request->data)) {
                $this->Flash->success(__('The observer has been saved'));
                if ($this->request->params['action'] != 'edit') {
                    $this->redirect($this->referer());
                } else {
                    $this->redirect(array('action' => 'index'));
                }
            } else {
                $this->Flash->error(__('The observer could not be saved. Please, try again.'));
            }
        } else {
            $options = array('conditions' => array('Observer.' . $this->Observer->primaryKey => $id));
            $this->request->data = $this->Observer->find('first', $options);
        }
        $users = $userObservers = $this->Observer->getAccessibleObservers($this->Auth->user('UserRoots'));
        $types = Observer::$allTypes;
        $this->set(compact('users', 'userObservers', 'types'));
    }

    /**
     * delete method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function delete($id = null) {
        $this->Observer->id = $id;
        if (!$this->Observer->exists()) {
            throw new NotFoundException(__('Invalid observer'));
        }
        $this->request->onlyAllow('post', 'delete');
        if ($this->Observer->delete()) {
//            $this->Flash->set(__('Observer deleted'));
			$result = true;
        } else {
//			$this->Flash->set(__('Observer was not deleted'));
			$result = false;
		}
        
		if ($this->request->is('ajax')) {
			$this->autoRender = false;
			// the delete button needs values back from it's original array
			echo json_encode(array_merge($this->request->data, array('result' => $result, 'id' => $id)));
		} else {
			$this->redirect(array('action' => 'index', 'id' => $id));
		}    
	}

	/**
	 * Prep the form allowing section of a user who will observe
	 * 
	 * @param int $id observer record id
	 */
    public function observerEdit($id) {
        $this->layout = 'ajax';
        $this->edit($id);
		$this->Observer->id = $id;
		$this->set('selectedUser', $this->Observer->field('user_observer_id'));
		$this->passRootNodes('User');
        $this->set('alias', 'Observer');
        $this->render('/Elements/observer_form');
    }

	/**
	 * Prep the form allowing section of a user who will be observed
	 * 
	 * @param int $id oberserver record id
	 */
    public function userObserverEdit($id) {
        $this->layout = 'ajax';
        $this->edit($id);
		$this->Observer->id = $id;
		$this->set('selectedUser', $this->Observer->field('user_id'));
        $this->set('alias', 'UserObserver');
        $this->render('/Elements/observer_form');
    }
    
    public function observerAdd($id) {
        if ($this->request->is('post') || $this->request->is('put')) {
            $this->Observer->create();
            if ($this->Observer->save($this->request->data)) {
                $this->Flash->success(__('The observer has been saved'));
                $this->redirect($this->referer());
            } else {
                $this->Flash->error(__('The observer could not be saved. Please, try again.'));
            }
        }
        $this->layout = 'ajax';
        $userObservers = $this->Observer->getAccessibleObservers($this->Auth->user('UserRoots')); //this is the one that's email ONLY
        $types = Observer::$allTypes;
        $this->request->data['Observer']['user_id'] = $id;
        $alias = 'Observer';
		$this->passRootNodes('User');
        $this->set(compact('alias', 'userObservers', 'types'));
        $this->render('/Elements/observer_form');
    }

    public function userObserverAdd($id) {
        if ($this->request->is('post') || $this->request->is('put')) {
            $this->Observer->create();
            if ($this->Observer->save($this->request->data)) {
                $this->Flash->success(__('The observer has been saved'));
                $this->redirect($this->referer());
            } else {
                $this->Flash->error(__('The observer could not be saved. Please, try again.'));
            }
        }
        $this->layout = 'ajax';
        $userObservers = $this->Observer->getAccessibleObservers($this->Auth->user('UserRoots'));
        $types = Observer::$allTypes;
        $this->request->data['Observer']['user_observer_id'] = $id;
        $alias = 'UserObserver';
		$this->passRootNodes('User');
        $this->set(compact('alias', 'userObservers', 'types'));
        $this->render('/Elements/observer_form');
    }

}
