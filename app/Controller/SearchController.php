<?php
App::uses('AppController', 'Controller');

class SearchController extends AppController {
	
// <editor-fold defaultstate="collapsed" desc="Properties">
	public $userQuery = false;
	
	public $customerQuery = array();
	
	public $helpers = array('Status');
	
	public $uses = array('Customer', 'Order', 'Replenishment');


	/**
	 * Master search filter list
	 * 
	 * A checkbox list for the search page
	 *
	 * @var array
	 */
	public $searchFilter = array(
		'user' => 'Users and Customers',
		'catalog' => 'Catalogs',
		'order' => 'Orders',
//		'address' => 'Addresses',
		'replenishment' => 'Replenishments',
		'active' => 'Active only',
		'archived' => 'Include Archived Orders'
	);


	/**
	 * The default selections for the search filter
	 *
	 * @var array
	 */
	public $defaultFilter = array(
		'user',
		'catalog',
		'order',
		'active'
	);


	/**
	 * The users current search filter settings
	 *
	 * @var array
	 */
	public $filter = array(); 
// </editor-fold>

	public function beforeFilter() {
        parent::beforeFilter();
		//establish access patterns
		$this->accessPattern['Guest'] = array ('all');
		$this->accessPattern['Buyer'] = array ('all');
		$this->accessPattern['Manager'] = array ('all');
    }
	
    public function isAuthorized($user) {
        return $this->authCheck($user, $this->accessPattern);
    }
	
	/**
	 * Basic Search
	 * 
	 * Operates from a single input
	 * but checks multiple tables
	 */
	public function search() {
		if ($this->request->is('post')) {
			
			// filters are memorized every visit
			$this->searchFilterPreference($this->request->data['filter']);

			// capture users search parameters
			$this->query = $this->request->data['User']['search'];
			$this->filter = array_flip($this->request->data['filter']); // see the searchFilter property for details
			
			// perform search of User & Customer
			if (isset($this->filter['user'])) {
				$users = $this->User->queryUsers($this->query);
				$customers = $this->Customer->queryCustomers($this->query);
			} 
			
			// perform search of catalog
			if (isset($this->filter['catalog'])){
				$catalogs = $this->Catalog->queryCatalog($this->query, isset($this->filter['active']));
				$catalogs = $this->Catalog->gatherComponentGrain($catalogs);
				
				// stuff necessary to display shopping grain
				$itemLimitBudget = $this->Auth->user('use_item_limit_budget');
				$backorderAllow = false;
			}
			
			// perform search of orders
			// Orders searches number and billing company
			// queryUser finds the list of orders for Users and Companies that contain $query
			// item query will find orders who's line items contain $query
			if (isset($this->filter['order'])) {
				
				//setup params for Order queries
				if (!isset($this->filter['user'])) {
					$this->User->queryUsers($this->query);
				}
				if(!isset($this->filter['catalog'])){
					$this->Catalog->queryCatalog($this->query, isset($this->filter['active']));
				}
				
				$orders = $this->Order->queryOrders($this->query, isset($this->filter['archived']));
				
				// stuff necessary for status grain display
				$approvable = $this->User->getObservationList('Approval');
			} else {
				$orders = array();
			}
			
			//Replenishments Search
			if (isset($this->filter['replenishment'])) {
				//setup params for Order queries
				if (!isset($this->filter['user'])) {
					$this->User->queryUsers($this->query);
				}
				if(!isset($this->filter['catalog'])){
					$this->Catalog->queryCatalog($this->query, isset($this->filter['active']));
				}
				
				$replenishments = $this->Replenishment->queryReplenishments($this->query, isset($this->filter['archived']));
			} else {
				$replenishments = array();
			}
			
		}
		
		// Things to do for every page render
		$this->set(array(
			'filters' => $this->searchFilter, 
			'defaultFilters' => ($this->Session->read('Prefs.Search') != null) ? $this->Session->read('Prefs.Search') : $this->defaultFilter,
			'query' => $this->query
		));
//		$this->ddd($replenishments, 'replenishments');
		$this->set(compact('users', 'customers', 'orders', 'approvable', 'catalogs', 'itemLimitBudget', 'backorderAllow', 'replenishments'));
	}
	
}
?>