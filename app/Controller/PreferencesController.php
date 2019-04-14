<?php
App::uses('AppController', 'Controller');
/**
 * Preferences Controller
 *
 * @property Preference $Preference
 */
class PreferencesController extends AppController {

	public $components = array('Prefs');

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
	

	/**
	 * Set the current page to be this user's default home page
	 * 
	 * @param type $controller
	 * @param type $action
	 */
	public function homePreference($controller, $action) {
		$this->layout = 'ajax';
		
		$this->Session->write(
				'Prefs.home', 
				['controller' => $controller, 'action' => $action]);
		$this->Prefs->savePreferences();
		
		$this->render('/Common/ajax');
	}

	/**
	 * Save the current shipping values as preferences
	 * 
	 * These prefs will apply to the current customer for this users orders
	 * This tool is only available from shop/address page
	 * $this->request->data['customer'] is customer-userid of the Customer the controlls the Shop
	 * $a is the id of the selected Address record (or 'customer' if there is none selected)
	 */
	public function shippingPreference() {
		$this->layout = 'ajax';
		
		$a = ($this->request->data['address'] != '') 
				? ".{$this->request->data['address']}" 
				: '.customer';
		$this->Session->write(
				'Prefs.ship.' . $this->request->data['customer'] . $a
				, $this->request->data['shipment']);
		
		$this->Prefs->savePreferences();
		$this->render('/Common/ajax');
	}

	/**
	 * Save the user's requested pagination limit
	 * 
	 * @param INT $limit, the requested limit
	 */
	public function paginationLimitPreference($limit) {
		$this->autoRender = false;
		$this->Session->write('Prefs.Catalog.paginationLimit', $limit);
		$this->Prefs->savePreferences();
	}
	
}
