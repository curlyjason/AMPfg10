<?php
App::uses('AppController', 'Controller');
/**
 * Helps Controller
 *
 * @property Help $Help
 */
class HelpsController extends AppController {

	public function beforeFilter() {
        parent::beforeFilter();
		//establish access patterns
		$this->accessPattern['Manager'] = array ('all');
		$this->accessPattern['Buyer'] = array('getHelpLinks', 'displayHelp');
		$this->accessPattern['Guest'] = array('getHelpLinks', 'displayHelp');
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
		$this->Help->recursive = 0;
		$this->set('helps', $this->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Help->exists($id)) {
			throw new NotFoundException(__('Invalid help'));
		}
		$options = array('conditions' => array('Help.' . $this->Help->primaryKey => $id));
		$this->set('help', $this->Help->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Help->create();
			if ($this->Help->save($this->request->data)) {
				$this->Flash->set(__('The help has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->set(__('The help could not be saved. Please, try again.'));
			}
		}
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->Help->exists($id)) {
			throw new NotFoundException(__('Invalid help'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Help->save($this->request->data)) {
				$this->Flash->set(__('The help has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->set(__('The help could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Help.' . $this->Help->primaryKey => $id));
			$this->request->data = $this->Help->find('first', $options);
		}
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Help->id = $id;
		if (!$this->Help->exists()) {
			throw new NotFoundException(__('Invalid help'));
		}
		$this->request->allowMethod(['post', 'delete']);
		if ($this->Help->delete()) {
			$this->Flash->set(__('Help deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Flash->set(__('Help was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
	
	public function getHelpLinks() {
		$this->layout = 'ajax';
		$helpDocs = $this->Help->find('all', array(
			'conditions' => array(
				'tag' => $this->request->data
			),
			'fields' => array(
				'tag',
				'name'
			),
			'order' => 'name ASC'
			));
				
		$new = array();
		if (count($this->request->data) > count($helpDocs)) {
			$new = $this->createNewHelpEntries($helpDocs);
		}
			
//			
//		$this->ddd($this->request->data, 'remainder');
//		$this->ddd($helpDocs, 'found data');
//		$this->ddd($new, 'new entry');
		$help = array_merge($helpDocs, $new);
		$this->set('help', $help);
	}
		
	/**
	 * Make records for any help documents that don't already exist
	 * 
	 * thisRequestData has the list of required docs for this page
	 * 
	 * @param array $helpDocs The set of help docs that already exists
	 * @return array The array of new docs for link construction
	 */
	private function createNewHelpEntries($helpDocs) {

		// Some entries are new, figure out which ones
		foreach($helpDocs as $index => $help) {
				unset($this->request->data[$help['Help']['tag']]);
			}

		// build the required new records
		foreach ($this->request->data as $newEntry) {
			$new[] = array('Help' => array(
				'tag' => $newEntry,
				'name' => "zzNEW HELP: $newEntry",
				'help' => 'Write a new help document'
			));
		}

		// save the new records
		$this->Help->create();
		$save = $this->Help->saveAll($new);
		return $new;
	}
	
	public function displayHelp($tag) {
		$this->layout = 'ajax';
		$this->Help->primaryKey = 'tag';
		if($this->Help->exists($tag)){
			$this->set('helpText', $this->Help->findByTag($tag));
		}
	}
	
	public function editHelp($tag = '') {
		$this->layout = 'ajax';
		if(empty($tag)){
			//save
			$tag = $this->request->data['Help']['tag'];
			$save = $this->Help->save($this->request->data);
			$this->redirect(array('action' => 'displayHelp', $tag));
		}
		$this->Help->primaryKey = 'tag';
		if($this->Help->exists($tag)){
			$this->request->data = $this->Help->findByTag($tag);
		}		
	}
}
