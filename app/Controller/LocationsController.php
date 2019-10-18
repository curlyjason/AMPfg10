<?php
App::uses('AppController', 'Controller');
/**
 * Locations Controller
 *
 * @property Location $Location
 */
class LocationsController extends AppController {
	
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
		$this->Location->recursive = 0;
		$this->set('locations', $this->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Location->exists($id)) {
			throw new NotFoundException(__('Invalid location'));
		}
		$options = array('conditions' => array('Location.' . $this->Location->primaryKey => $id));
		$this->set('location', $this->Location->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Location->create();
			if ($this->Location->save($this->request->data)) {
				$this->Session->setFlash(__('The location has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The location could not be saved. Please, try again.'));
			}
		}
		$items = $this->Location->Item->find('list');
		$this->set(compact('items'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->Location->exists($id)) {
			throw new NotFoundException(__('Invalid location'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Location->save($this->request->data)) {
				$this->Session->setFlash(__('The location has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The location could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Location.' . $this->Location->primaryKey => $id));
			$this->request->data = $this->Location->find('first', $options);
		}
		$items = $this->Location->Item->find('list');
		$this->set(compact('items'));
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
		$this->Location->id = $id;
		if (!$this->Location->exists()) {
			throw new NotFoundException(__('Invalid price'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->Location->delete()) {
			if ($ajax) {
				echo true;
			} else {
				$this->Session->setFlash(__('Location deleted'));
				$this->redirect(array('action' => 'index'));
			}
		} else {
			if ($ajax) {
				echo false;
			} else {
				$this->Session->setFlash(__('Location was not deleted'));
				$this->redirect(array('action' => 'index'));
			}
		}
	}
	/**
	 * Render side for the locations table on an item
	 * 
	 */
	public function pullLocations($item_id) {
		$this->layout = 'ajax';

//		$customer_id = $this->request->data['id'];
//		$item_id = $this->request->data['Item']['id'];
		
		$locations = $this->Location->find('all', array(
			'conditions' => array(
				'Location.item_id' => $item_id
			),
			'contain' => FALSE
		));

		$buildings = $this->Location->Item->getLocations();
		$this->set('rowMax', $this->Location->rowMax);
		$this->set('binMax', $this->Location->binMax);
		$this->set(compact('locations', 'buildings'));
	}
	
	/**
	 * Save side for the locations table for an item
	 * 
	 * @return type
	 */
	public function saveLocations() {
		$this->layout = 'ajax';
		// Save
		$save = $this->Location->saveAll($this->request->data);
		if($save){
			$this->Flash->success('The locations saved.');
		}
		$this->set('save', $save);
	}
	
	public function refreshLocations($item_id) {
		$this->layout = 'ajax';
		$data = $this->Location->Item->find('first', array(
			'conditions' => array(
				'id' => $item_id
			),
			'contain' => array(
				'Location'
			)
		));
		//reset array to work with standard helper
		$data['Item']['Location'] = $data['Location'];
		
//		$this->ddd($data, 'data in refresh locations');
//		die;
		$this->set('data', $data);
	}
	
	public function fetchLocationRow($itemId) {
		$this->layout = 'ajax';
		
		$this->set('index', time());
		$this->set('buildings', $this->Location->Item->getLocations());
		$this->set('rowMax', $this->Location->rowMax);
		$this->set('binMax', $this->Location->binMax);
		$this->set('itemId', $itemId);
		$this->render('/Elements/Warehouse/fetch_location_row');
	}
	
	public function viewLocations($item_id, $ordId) {
		$this->layout = 'ajax';
		
		$locations = $this->Location->Item->find('first', array(
			'conditions' => array(
				'Item.id' => $item_id
			),
			'contain' => 'Location'
		));
		
		$locations['id'] = $locations['Item']['id'];
		
		$this->set('data', $locations);
		$this->set('id', $ordId);

		$this->render('/Elements/Warehouse/locations');
//		echo $this->element('Warehouse/locations', array('data' => $this->request->data['Item'], 'id' => $this->request->data['Item']['id']));
	}


	
}
