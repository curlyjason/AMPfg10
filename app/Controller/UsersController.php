<?php

/**
 * Users Controller
 *
 * Handle base User methods common to all User types
 *
 * @copyright     Copyright (c) Dreaming Mind (http://dreamingmind.com)
 * @package       app.Controller
 */
App::uses('AppController', 'Controller');
App::uses('User', 'Helper');
App::uses('Time', 'Helper');
App::uses('EventObservers', 'Lib');
App::uses('IdHashTrait', 'Lib/Trait');
//App::uses('AutomaticComponent', 'Component');

/**
 * Users Controller
 *
 * Handle base User methods common to all User types
 *
 * @package		app.Controller
 * @property User $User
 */
class UsersController extends AppController {

	use IdHashTrait;
	
// <editor-fold defaultstate="collapsed" desc="Properties">
	public $helpers = array('User', 'Time');
    public $displayField = 'username';
	
	public $components = array('Paginator', 'Prefs');

	public $userprop = array('time', 'heals', 'all', 'booboos');
// </editor-fold>

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('add');
        $this->User->userId = $this->Auth->user('id');
        $roots = $this->User->userRoots = $this->Auth->user("UserRoots");
        $this->User->rootOwner = isset($roots[$this->User->ultimateRoot]);
		//establish access
		$this->accessPattern['Manager'] = ['all'];
		$this->accessPattern['Buyer'] = 
		[
			'index', 
			'shop', 
			'listOrders', 
			'edit_userGrain', 
			'addressAdd', 
			'addressEdit', 
			'addressDelete', 
			'userEdit'
		];
		$this->accessPattern['Guest'] = 
		[
			'listOrders', 
			'addressAdd', 
			'addressEdit', 
			'edit_userGrain', 
			'addressDelete', 
			'userEdit'];
    }

    //============================================================
    // USER LOGIN/OUT AND SESSION MANAGEMENT
    //============================================================

    public function login() {
        $this->layout = 'login';
        if ($this->request->is('post')) {
            if ($this->Auth->login()) {
                $this->eradicateEditLocks();
                $this->initUser();
                return $this->redirect($this->Auth->loginRedirect);
            }
            $this->Flash->set(__('Invalid username or password, try again'));
        }
    }

    public function isAuthorized($user) {
        return $this->authCheck($user, $this->accessPattern);
    }

    /**
     * Cleanup db entries that support the sesion and logout
     *
     * Make sure there are no edit locks left in place
     * No more need to have other users inform us about
     * 	    tree structure and permission changes to nodes we can see
     * Take the closed-lid value out of our own record
     *
     * @return type
     */
    public function logout() {
        $this->closeUserSession();
        return $this->redirect($this->Auth->logout());
    }

    /**
     * A landing point for session timeout condition
     */
    public function timeout() {
        $this->layout = 'login';
        $this->closeUserSession();
        // Yep. just renders a simple timeout/login page
    }

    /**
     * Perform the session related db actions to end a user session
     * Then destroy the session
     */
    private function closeUserSession() {
        $this->saveCart();
        $this->eradicateEditLocks();
        $this->unregisterPublicUser();
        $this->releaseUserRecord();
        $this->Prefs->savePreferences();
        $this->Session->destroy();
    }

    /**
     * Write Auth and db data to support this session
     *
     * Work out our role, put it in Auth session
     * Look up our permitted nodes, put them in Auth session
     * Put our initial editing state data in Auth session
     * Let other editors know which nodes are of interest to us
     * Put a last-touch value in our own record
     * Retrieve shopping cart if one exists
     * Make a new budget if this is a new month
     */
    public function initUser() {
        //Setup elements for secure access by expanding Role in Group & Access
        $this->setRoleAccess();
        $this->setNodeAccess();
        $this->clearEditMode();
        $this->registerPublicUser();
        $this->flagUserRecord();
        $this->retreiveCart();
        $this->Prefs->retrievePreferences();
        // check for login redirect preference
        $this->User->Budget->setBudget($this->Auth->user());
        $this->Session->write('Auth.User.budget_id', $this->User->Budget->getBudgetId($this->Auth->user('id')));
        //We are having to cut out the webroot from the redirect
        if ($this->Session->read('Prefs.home')) {
            $this->Auth->loginRedirect = $this->Session->read('Prefs.home');
        } else {
            $this->Auth->loginRedirect = array('controller' => Inflector::underscore($this->Auth->user('group')), 'action' => 'status');
        }
    }

    /**
     * Guarantee the user is keeping no records locked
     */
    private function eradicateEditLocks() {
        if ($this->Auth->user('id') != '') {
            $users = $this->User->find('all', array(
                'conditions' => array('User.lock' => $this->Auth->user('id')),
                'fields' => array('User.id', 'User.lock'),
                'contain' => false
            ));
            if (!empty($users)) {
                foreach ($users as $index => $record) {
                    $users[$index]['User']['lock'] = 0;
                }
                $this->User->saveMany($users);
            }
            $catalogs = $this->User->Catalog->find('all', array(
                'conditions' => array('Catalog.lock' => $this->Auth->user('id')),
                'fields' => array('Catalog.id', 'Catalog.lock'),
                'contain' => false
            ));
            if (!empty($catalogs)) {
                foreach ($catalogs as $index => $record) {
                    $catalogs[$index]['Catalog']['lock'] = 0;
                }
                $this->User->Catalog->saveMany($catalogs);
            }
        }
    }

//  <editor-fold defaultstate="collapsed" desc="MOVED TO IdHashTrait">
	/**
	 * Check a set of secure ids and strip hashes from the source data
	 *
	 * When permissions are included in User form data, they don't validate
	 * normally. This is our manual way of taking care of them
	 * array [
	 * 	    0 => 93/432abf349abf98934cc98c9c89a89a
	 * 	    1 => 89/08708b8089e098f098e890f083423f
	 * ]
	 * Operates on a reference of the original data, so no return necessary
	 *
	 * @todo Move this to a generalized security class
	 * @param array $ids an array of xx/xyzhash pairs to validate
	     */
//	private function validateIds(&$ids)
//	{
//		if (!empty($ids)) {
//			foreach ($ids as $index => $id) {
//				$check = explode('/', $id);
//				if ($this->secureId($check[0], $check[1])) {
//					$ids[$index] = $check[0];
//				} else {
//					$ids[$index] = FALSE;
//				}
//			}
//		}
//	}

	/**
	 * Not used at this point
	 * @todo Move this to a generalized security class
	 * @param type $id
	     */
//	private function validateId(&$id)
//	{
//		$check = explode('/', $id);
//		if (count($check) == 2 && $this->secureId($check[0], $check[1])) {
//			$ids[$index] = $check[0];
//		} else {
//			$ids[$index] = $id;
//		}
//	}


	/**
	 * Was an id and hash provided and if so, was it valid
	 * 
	 * @param string $id
	 * @param string $hash
	 * @return mixed Null = not supplied, True = valid, False = invalid
	 */
//	private function suppliedAndValid($id, $hash)
//	{
//		if ($id != null && $hash != null) {
//			$result = $this->secureId($id, $hash);
//		} else {
//			$result = NULL;
//		}
//		return $result;
//	}

// </editor-fold>

	/**
     * Save the current cart's session on logout/timeout
     * 
     * Execute a find to determine if this user has a cart record
     * If there is one, save the current session id to the user record
     * for cart retreival on login.
     */
    protected function saveCart() {
//        //init the cart class & find any existing cart
//        $this->Cart = ClassRegistry::init('Cart');
//        $existing = $this->Cart->find('first', array(
//            'recursive' => -1,
//            'conditions' => array(
//                'Cart.sessionid' => $this->Session->id()
//            )
//        ));
//        if ($existing) {
//            //if there is a cart, save it's session id to the user record
//            $this->User->Behaviors->disable('ThinTree');
//            $cart['User']['id'] = $this->Auth->user('id');
//            $cart['User']['cart_session'] = $this->Session->id();
//            $this->User->save($cart);
//            $this->User->Behaviors->enable('ThinTree');
//            $this->Session->destroy();
//        }
    }

    /**
     * Retreive the user's saved cart, if any
     *
     * Based upon the existance of a cart_session id in the user session record
     * find the previous session's cart items and update them to the current session
     * value
     */
    protected function retreiveCart() {
        //init the cart class & find any existing cart
        $this->CartComponent = $this->Components->load('Cart');
        $this->Cart = ClassRegistry::init('Cart');
        $this->Item = ClassRegistry::init('Item');
        $existing = $this->Cart->find('all', array(
            'recursive' => -1,
            'conditions' => array(
                'Cart.user_id' => $this->Auth->user('id')
            )
        ));
        //if there is an existing cart, update its session ids
        if ($existing) {
            foreach ($existing as $index => $record) {
                $existing[$index]['Cart']['sessionid'] = $this->Session->id();
                $this->writeCartSession($record);
            }
            //setup the customer session record
            $customerId = $existing[0]['Cart']['customer_id'];
            $customer = $this->User->Customer->find('first', array(
                'conditions' => array(
                    'Customer.id' => $customerId
                ),
                'contain' => array(
                    'Address',
                    'User'
                )
            ));
            if($customer){
                $this->Session->write('Shop.Customer', $customer['Customer']);
                $this->Session->write('Shop.Customer.Address', $customer['Address']);
                $this->Session->write('Shop.Customer.User', $customer['User']);
            }

            $this->Cart->saveAll($existing);
        }

        //no matter if the cart was recovered, clear the user
        $this->User->Behaviors->disable('ThinTree');
        $cart['User']['id'] = $this->Auth->user('id');
        $cart['User']['cart_session'] = NULL;
        $this->User->save($cart);
        $this->User->Behaviors->enable('ThinTree');
    }


    /**
     * Write the retreived cart to the session
     *
     * @param array $product
     */
    protected function writeCartSession($product) {
        //setup the retreived cart info
        $data = $product['Cart'];
        //find the catalog for this cart line
        $catalog = $this->Catalog->find('first', array(
            'conditions' => array(
                'Catalog.id' => $product['Cart']['catalog_id']
            ),
            'contain' => array(
                'Item' => array(
                    'Image'
                ),
                'ParentCatalog' => array(
                    'Item' => array(
                        'Image'
                    )
                )
            )));

        //establish the array for the session
        $data['Item'] = $catalog['Item'];
        $data['Catalog'] = $catalog['Catalog'];
        $data['Catalog']['ParentCatalog'] = $catalog['ParentCatalog'];
        $data['Image'] = $catalog['Item']['Image'];

        //write the session
        $this->Session->write('Shop.OrderItem.' . $product['Cart']['catalog_id'], $data);
        $this->Session->write('Shop.Order.shop', 1);

        //init the cart
        $this->CartComponent->cart();
    }

    //============================================================
    // BASIC CRUD
    //============================================================

    public function index() {
        $this->User->recursive = 0;
        $this->set('users', $this->paginate());
    }

    /**
     * view method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function view($id = null) {
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        $this->set('user', $this->User->read(null, $id));
    }

    public function ajaxEdit() {
        $this->add();
    }

    /**
     * add method
     *
     * BTW, this is also used for ajax adding and editing, and thus we have some
     * switches on $this->request->action to limit functionality in those cases
     *
     * AJAX calls directly for form rendering services
     * 	    new child or sibling call as action=add
     * 	    edit calls as action=edit_renderEditForm
     * Also both add and edit AJAX call for record saving services
     *
     * @return void
     */
    public function add() {
        // ajax calls post the first time, but then put... who knows why
        if ($this->request->is('post') || $this->request->is('put')) {
            // The HABTM fields won't validate. Do it manually

            //manage different array structures for edits and adds
            if ($this->request->action == 'edit_saveEditForm') {
                //validate connected users & catalogs for EDIT users
                $this->validateIds($this->request->data['UserManaged']['UserManaged']);
                $this->validateIds($this->request->data['Catalog']['Catalog']);
            } else {
                //validate connected users & catalogs for ADD users
                $this->validateIds($this->request->data['UserManaged']);
                $this->validateIds($this->request->data['Catalog']);
            }

            //set role from parent
            if (empty($this->request->data['User']['role'])){
                //find the role fromt the new user's parent
                $parentRole = $this->User->field(
                    'role',
                    array('id' => $this->request->data['User']['parent_id'])
                );
                //set role with a secure hash pair
                $this->request->data['User']['role'] = $parentRole . '/' . $this->secureHash($parentRole);
            }

            //unset unnecessary fields if user is folder
            if($this->request->data['User']['folder']){
                unset($this->request->data['User']['first_name']);
                unset($this->request->data['User']['last_name']);
                unset($this->request->data['UserManaged']);
                unset($this->request->data['Catalog']);
            }


            $this->User->create();
            if ($this->request->action == 'edit_saveEditForm') {
                // ajax editing has 'extra field' problems. this solves them
                // ajax add works fine
                $this->User->data = $this->request->data;
            }
            if ($this->User->save($this->request->data)) {
                // a bit 'big-hammer', but we may have a new node permission.
                // make sure the Auth session gets updated to include it
                if ($this->User->refreshPermissions) {
                    $this->setNodeAccess($this->Auth->user('id'));
                }
                $this->Flash->success(__('The user has been saved'));

                // normal CRUD adds redirect to index
                if ($this->request->action == 'add') {
                    return $this->redirect(array('action' => 'index'));

                    // ajax calls need to head back to prepare the response
                } elseif ($this->request->action == 'edit_newChild' || $this->request->action == 'edit_newSibling' || $this->request->action == 'edit_saveEditForm') {
                    return;
                }
            } else {
                $msg = '';
                foreach ($this->User->validationErrors as $message){
                    $msg .= $message[0] . " \n\r ";
                }
                $this->Flash->error(__($msg));
            }
        }

        if ($this->request->action == 'add') {
            // make select and checkbox lists for the view

            $roles = $this->User->getSecureList($this->Auth->user('role'), 'role');
            $accessibleUsers = $this->fetchTreeCompliantArray('User');
            $accessibleCatalogs = $this->fetchTreeCompliantArray('Catalog');
            $user_selected = array();
            $catalog_selected = array();
            $this->set(compact('accessibleUsers', 'roles', 'parent_catalogs', 'user_selected', 'catalog_selected', 'accessibleCatalogs'));
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
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            // The HABTM fields won't validate. Do it manually
            $this->validateIds($this->request->data['UserManaged']['UserManaged']);
            $this->validateIds($this->request->data['Catalog']['Catalog']);

            if ($this->User->save($this->request->data)) {
                $this->Flash->success(__('The user has been saved'));
                return $this->redirect(array('action' => 'index'));
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        } else {
            $this->fetchRecordForEdit($id);
        }
        $this->fetchVariablesForEdit();
    }

    /**
     * delete method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function delete($id = null) {
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        $this->request->onlyAllow('post', 'delete');
        if ($this->User->delete()) {
            $this->Flash->success(__('User deleted'));
            $this->redirect($this->referer());
        }
        $this->Flash->error(__('User was not deleted'));
        $this->redirect($this->referer());
    }

    /**
     * General use Fetch of a user record when a form needs populating data
     *
     * @param type $id
     */
    public function fetchRecordForEdit($id) {
        $this->request->data = $this->User->read(null, $id);
        $this->request->data['User']['role'] = $this->secureSelect($this->request->data['User']['role']);
        $this->request->data['User']['parent_id'] = $this->secureSelect($this->request->data['User']['parent_id']);
        unset($this->request->data['User']['password']);
    }

    /**
     * set variables for all edit situations
     *
     * @param type $id
     */
    public function fetchVariablesForEdit() {
        $roles = $this->User->getSecureList($this->Auth->user('role'), 'role');
        $accessibleUsers = $this->fetchTreeCompliantArray('User');
        $accessibleCatalogs = $this->fetchTreeCompliantArray('Catalog');
        $user_selected = $this->User->getSecureList($this->request->data['UserManaged'], 'permittedUsers');
        $catalog_selected = $this->User->getSecureList($this->request->data['Catalog'], 'permittedCatalogs');
        $this->set(compact('roles', 'user_selected', 'catalog_selected', 'accessibleCatalogs', 'accessibleUsers'));
    }

    //============================================================
    // TREE MANAGEMENT METHODS
    //============================================================

    /**
     * The method to allow editing of User Trees
     *
     * $controller is important but in this case is set by AppController->beforeFilter()
     * $lock if true will prevent editing by forcing output of a plain tree w/no toolss
     *
     * @todo Add access node points so interface can do tool filtering
     * @param type $id
     * @param type $hash
     */
    public function edit_user($id = null, $hash = null) {

        if (!empty($this->userRoots)) {
            $this->layout = 'sidebar';
            $pageHeading = $title_for_layout = 'Group & Organize Customers & Users';
            $this->set(compact('pageHeading', 'title_for_layout'));
            // The user has select a tree node for detailed editing
            if ($this->suppliedAndValid($id, $hash)) {

                // true = sorry, can't edit this; false = go for it!!
                // checkLock also locks this record for our edit if necessary
                $this->set('lock', $this->checkLock($this->User, $id) === TRUE); // this can force a switch to a view-only detail tree
                $this->set('renderNode', $id);
                $this->set('catalogEditFlag', false);
                $this->set('userEditFlag', true);

                //set parameters for getFullNode
                $group = true;
                $conditions = array(
                    'User.active' => 1
                );

                //retreive nodes
                $nodes = $this->User->getFullNode($id, $group, $conditions);
                if(!$nodes){
                    //if current head node was deactivated, redirect to base page
                    $this->redirect(array('controller' => 'users', 'action' => 'edit_user'));
                }

                $this->set('editTree', $this->detailTreeNodes($nodes));

                // The requested node was invalid. A security problem.
            } elseif ($this->suppliedAndValid($id, $hash) === FALSE) {
                // don't f with the data! That's a security violation!
                throw new ForbiddenException("Security validation failed on your request for \r
		    {$this->request->url}\rContact your admin for more information.");
            }

            // In all cases, get the side-panel selector tree data and send it to the view
            $this->prepareUserSidebar();

            // Sorry, there's nothing you can do here even though you are allowed   
        } else {
            $this->Flash->error('You don\'t have permission to edit any Users');
        }
    }

    /**
     * Add necessary details to flat nodes
     *
     * The flat node array for a tree needs some deeper data
     * This process drills down and inserts the needed values
     * 1. the count of watchers
     * 2. a flag indicating if this is a customer
     *
     * @param array $nodes Nodes that need details
     */
    protected function detailTreeNodes($nodes){
        foreach ($nodes as $parent => $branch) {
            foreach ($branch as $index => $leaf) {
                $nodes[$parent][$index]['watchers'] = $this->addUserWatcherCounts($leaf);
                $nodes[$parent][$index]['customer'] = $this->addCustomerFlag($leaf);
            }
        }

        return $nodes;
    }
    /**
     * Add the number of watchers for each user in the array
     *
     * Given the standard array for edit tree output,
     * add a value indicating how many users are watching
     * each user node
     *
     * @param array $nodes The nodes array to analyze for watchers
     * @return array nodes array with the watcher value inserted
     */
    public function addUserWatcherCounts($node) {
        $accessors = $this->User->UserManaged->find('all', array(
            'conditions' => array('UserManaged.id' => $node['id']),
            'fields' => array(
                'id',
            ),
            'contain' => array('UserManager' => array(
                'fields' => array(
                    'UserManager.id'
                )
            )),
        ));

        return (count($accessors[0]['UserManager']) > 0) ? count($accessors[0]['UserManager']) : false ;
    }

    /**
     * Add a customer flag to to node. Will later control tools in the tree
     *
     * @param array $node node being detailed
     * @return boolean
     */
    private function addCustomerFlag($node) {

        // also indicate if this is a customer
        $customer = $this->User->Customer->find('first', array(
            'conditions' => array('Customer.user_id' => $node['id']),
            'fields' => 'id',
            'contain' => false
        ));

        return (!empty($customer)) ? true : false ;
    }
	
	

    public function edit_userGrain($id = null, $hash = null, $showInvoicePDF = null) {
		
        // The user has selected a tree node for detailed editing
        if ($this->suppliedAndValid($id, $hash) === TRUE) {
			
            $this->set('editGrain', 
					$this->User->find('first', 
							array('conditions' => array('User.id' => $id))
				));
			
            $this->set('addresses', 
					$this->User->getAccessibleUserNodes(
							$this->User->getOwnedUserRoots($id))
				);
			
        }  elseif ($this->suppliedAndValid($id, $hash) === false) {
			
            throw new ForbiddenException("Security validation failed on your" 
				. "request for \r {$this->request->url}\rContact your admin "
				. "for more information.");
			
        }
		
        $this->layout = 'sidebar';
		
        $access = $this->Auth->user('access');
        $group = $this->Auth->user('group');
        $owner = $this->Auth->user('id') == $id;
        $pageHeading = $title_for_layout = 'Detail Customers & Users';
        $invoices = $this->User->Invoice->fetchInvoices($id);
        $company_types = $this->User->Customer->company_types;
		
        $this->set(compact(
				'access', 
				'group', 
				'owner', 
				'pageHeading', 
				'invoices', 
				'company_types',
				'showInvoicePDF',
				'title_for_layout'
			)
		);
        // In all cases, get the side-panel selector tree data and send it to the view
        $this->prepareUserSidebar();
        $this->render('/Common/manage_tree_object');
    }

    /**
     * Prepare all User tree sidebar data and send it to the view
     *
     * @todo How about css and scripts? can we send vars that will let those load too?
     */
    private function prepareUserSidebar() {
        $this->set('tree', $this->fetchTreeCompliantArray('User'));
    }

    public function fetchTreeCompliantArray($alias) {
        if($alias == 'User'){
            $flatNodes = $this->User->getAccessibleUserNodes($this->Auth->user('UserRoots'), array('User.active' => 1));
            $this->passRootNodes('User');
            return $this->User->nodeGroups($flatNodes);
        } else if($alias == 'Catalog'){
            $flatNodes = $this->User->Catalog->getAccessibleCatalogNodes($this->Auth->user('CatalogRoots'), array('Catalog.active' => 1));
            $this->passRootNodes('Catalog');
            return $this->User->Catalog->nodeGroups($flatNodes);
        }
    }

    /**
     * The method to allow editing of Catalog Trees
     *
     * @todo Add access node points so interface can do tool filtering
     * @param type $id
     * @param type $hash
     */
    public function edit_catalog($id = null, $hash = null) {
        $this->layout = 'sidebar';
        $pageHeading = $title_for_layout = 'Manage Catalogs';
		//override the normal path data used by js url constructors
        $controller = 'catalogs';
        $this->set(compact('controller', 'pageHeading', 'title_for_layout')); 

        if (!empty($this->catalogRoots)) {

            // the user has requested a node be detailed for editing
            if ($this->suppliedAndValid($id, $hash) === TRUE) {

                // true = sorry, can't edit this; false = go for it!!
                // checkLock also locks this record for our edit if necessary
                $this->set('lock', $this->checkLock($this->User->Catalog, $id)); // this can force a switch to a view-only detail tree
                $this->set('renderNode', $id);
                $this->set('catalogEditFlag', true);
                $this->set('userEditFlag', false);

                //set parameters for getFullNode
                $group = true;
                $conditions = array(
                    'Catalog.active' => 1
                );

                //retreive nodes
                $nodes = $this->User->Catalog->getFullNode($id, $group, $conditions);
                if(!$nodes){
                    //if current head node was deactivated, redirect to base page
                    $this->redirect(array('controller' => 'users', 'action' => 'edit_catalog'));
                }
                $this->set('editTree', $nodes);
				
            } elseif ($this->suppliedAndValid($id, $hash) === FALSE) {
				
                throw new ForbiddenException("Security validation failed on "
						. "your request for \r {$this->request->url}\r Contact "
						. "your admin for more information.");
            }

            // In all cases, get the side-panel selector tree data and send it to the view
            $this->prepareCatalogSidebar();
        } else {
            $this->Flash->set('You don\'t have permission to edit any Catalogs');
        }
    }

	/**
	 * 
	 * @todo What can be made of the comments 'true = sorry...' missing call?
	 *			see 'function edit_catalog(...' for example
	 * @todo comment 'override the normal...' implies new home for this method
	 * 
	 * @param type $id
	 * @param type $hash
	 * @throws ForbiddenException
	 */
    public function edit_catalogGrain($id = null, $hash = null) {
        // the user has requested a node be detailed for editing
        if ($this->suppliedAndValid($id, $hash) === TRUE) {
			// true = sorry, can't edit this; false = go for it!!
			// checkLock also locks this record for our edit if necessary
            $this->set('catalogGrain', 
					$this->User->Catalog->find('all', 
							array('conditions' => array('Catalog.id' => $id))
					));
//		
        } elseif ($this->suppliedAndValid($id, $hash) === FALSE) {
            throw new ForbiddenException("Security validation failed on your "
					."request for \r {$this->request->url}\rContact your "
					. "admin for more information.");
        }

        $this->layout = 'sidebar';
		//override the normal path data used by js url constructors
        $this->set('controller', 'catalogs'); 
        $this->prepareCatalogSidebar();
        $this->render('/Common/manage_tree_object');
    }

    /**
     * Prepare all Catalog tree sidebar data and send it to the view
     *
     * @todo How about css and scripts? can we send vars that will let those load too?
     */
    public function prepareCatalogSidebar() {
        $conditions = array('Catalog.active' => 1/* , 'Catalog.folder' => 1 */);
        $flatNodes = $this->User->getAccessibleCatalogNodes($this->Auth->user('CatalogRoots'), $conditions);
        $this->passRootNodes('Catalog');
        $this->set('tree', $this->User->Catalog->nodeGroups($flatNodes));
    }

    /**
     * The call point for all ajax drag/drop tree edits
     *
     * Simple pass through to AppController which
     * handles the tree edits for everybody
     */
    public function edit_tree() {
        $this->treeJax($this->User);
        $this->render('/AppAjax/edit_tree');
    }

    /**
     * Ajax 'demote to child' tool entry point
     *
     * Logic is in AppController to serve Catalog too.
     * This pass through sets the proper Model context
     */
    public function edit_toChild() {
        $this->ajax_toChild($this->User);
        $this->render('/AppAjax/edit_tree');
    }

    /**
     * Ajax 'new child' tool entry point
     *
     * Logic is in AppController to serve Catalog too.
     * This pass through sets the proper Model context
     */
    public function edit_newChild() {
        $password = $this->initNewUserPass();
        $this->ajax_newChild($this->User);
        $this->sendRegisterEmail($this->request->data['User']['username'], $this->request->data['User']['password']);
        $this->render('/AppAjax/edit_tree');
    }

    /**
     * Ajax 'new sibling' tool entry point
     *
     * Logic is in AppController to serve Catalog too.
     * This pass through sets the proper Model context
     */
    public function edit_newSibling() {
        $this->initNewUserPass();
        $this->ajax_newSibling($this->User);
        $this->render('/AppAjax/edit_tree');
    }

    /**
     * AJAX EDIT RENDER PHASE
     *
     * Pull the data that will populate the form
     * Then use add to generate the form inputs
     *
     * @param type $id
     * @throws BadRequestException
     */
    public function edit_renderEditForm($id) {
        //fetch associated customer, if any
        $customer = $this->User->Customer->find('first', array(
            'conditions' => array(
                'Customer.user_id' => $id
            ),
            'fields' => array(
                'Customer.id', 'Customer.customer_code'
            )
        ));

        //if there is an associated customer, edit it on the grain edit page
        if(!empty($customer)){
            $this->autoRender = false;
            $eid = explode('li', $id);
            echo router::url(array('controller' => 'users', 'action' => 'edit_userGrain', $eid[0], $eid[1]), true);
            return;
        } else {
            //no customer, edit here
            $this->ajax_RenderEditForm($id);
            $this->add();
            $this->render('add');
        }
    }

    /**
     * AJAX EDIT SAVE PHASE: entry point for the edit tool pallet choice
     */
    public function edit_saveEditForm() {
        $this->ajax_newChild($this->User);
        $this->render('/AppAjax/edit_tree');
    }

    /**
     * Deactivate selected user and all it's descendents
     *
     *
     * @param string $id the selected user's id, with li separated hash
     * @param string $activeToggle whether to active or deactivate the user
     */
    public function edit_deactivate($id, $activeToggle = 'deactivate') {
        $activeChange = ($activeToggle == 'deactivate') ? -1 : 1;
        $check = $this->validateSelect($id, 'li');
        //check integrity of chosen user
        if ($check[2]) {
            $id = $check[0];

            //setup conditions and options for getDescendents
            $conditions = [];
            $options = [
                'fields' => ['id','name', 'active'],
                'contain' => false
            ];

            //get all the selected user's descendents for inactivation
            $descendents = $this->User->getDecendents($id, false, $conditions, $options);

            //add selected user to the list
            $descendents[] = [
                'User' => [
                    'id' => $id,
                    'active' => ($activeToggle == 'deactivate') ? 1 : 0
                ]
            ];

            //mark all users a inactive
            for($i = 0; $i < count($descendents); $i++) {
                $descendents[$i]['User']['active'] = $descendents[$i]['User']['active'] + $activeChange;
            }

            //Save with error capture
            if($this->User->saveAll($descendents)){
                $this->Flash->success("User and descendents {$activeToggle}d.");
                //check if user is customer deactivate the user's root catalog node
                if($this->User->Customer->field('id', array('user_id' => $id))){
                    $r = $this->User->Catalog->field('id', array('customer_user_id' => $id));
                    $h = $this->secureSelect($r, 'li');
                    $this->requestAction(array('controller' => 'Catalogs', 'action' => 'edit_deactivate/' . $h . '/' . $activeToggle));
                }
            } else {
                $this->Flash->error("Failed to $activeToggle user, please try again.");
            }
        }
        $this->redirect($this->referer());
    }

    //============================================================
    // USER REGISTRATION EMAIL METHODS
    //============================================================

    /**
     * Reset any users password based upon the username in TRD
     *
     * @param array TRD with User:username
     * @returns boolean
     *
     * In case of ajax, with return boolean result
     * In non-ajax call, redirects to login
     */
    public function forgotPassword() {
        // if username is in db
        $user = $this->User->find('first', array(
            'conditions' => array('username' => $this->request->data['User']['username']),
            'contain' => false
        ));
        if (!empty($user)) {
            $user['User']['password'] = $this->initNewUserPass();
            $this->User->id = $user['User']['id'];
            $save = $this->User->saveField('password', $user['User']['password']);
            if ($save) {
                $result = TRUE;
                $this->sendRegisterEmail($user['User']['username'], $user['User']['password']);
                $this->Flash->success('An email has been sent so you can reset your password');
            } else {
                $result = FALSE;
                $this->Flash->error('The process failed. Please try again.');
            }
        } else {
            $result = FALSE;
            $this->Flash->error('No account found for that email');
        }
        $this->set('result', $result);
        if(!$this->request->is('ajax')){
            $this->redirect('login');
        }
    }

    /**
     * The user called function to create and send register emails
     *
     * This function is called from user creation to allow managers
     * to send new users a link to login which embeds their temporary
     * password and their login ID
     *
     * @param string $username
     * @param array $settings
     */
    public function sendRegisterEmail($username, $password) {
        //don't send email for any user folder object
        if(isset($this->request->data['User']['folder']) && $this->request->data['User']['folder']){
            return;
        }
        $Email = new CakeEmail();
        $Email->config('smtp')
            ->template('new_user_email', 'default')
            ->viewVars(array('email' => $username, 'password' => $password))
            ->emailFormat('html')
            ->from(array('ampfg@ampprinting.com' => 'AMP FG System Robot'))
            ->to($username)
            ->subject('Your AMPFG user login is here!')
            ->send();
    }

    /**
     * Create a password based upon strength and length declarations
     *
     * @param int $length
     * @param int $strength
     * @return string
     */
    private function generatePassword($length = 9, $strength = 0) {
        $vowels = 'aeuy';
        $consonants = 'bdghjmnpqrstvz';
        if ($strength & 1) {
            $consonants .= 'BDGHJLMNPQRSTVWXZ';
        }
        if ($strength & 2) {
            $vowels .= "AEUY";
        }
        if ($strength & 4) {
            $consonants .= '23456789';
        }

        $password = '';
        $alt = time() % 2;
        for ($i = 0; $i < $length; $i++) {
            if ($alt == 1) {
                $password .= $consonants[(rand() % strlen($consonants))];
                $alt = 0;
            } else {
                $password .= $vowels[(rand() % strlen($vowels))];
                $alt = 1;
            }
        }
        return $password;
    }

    /**
     *
     * @return type
     */
    public function initNewUserPass() {
        $password = $this->generatePassword(9, 9);
        $this->request->data['User']['password'] = $password;
        return $password;
    }

    /**
     * Entry point from new user emails
     *
     *
     * @param type $username
     * @param type $password
     * @return boolean|string
     */
    public function registration($username = false, $password = null) {
        //validate proper data provided
        if (!$username) {
            $this->Flash->set(__('Invalid username or password, try again'));
            return false;
        }

        //setup proper username and find the user
        $username = str_replace('%40', '@', $username);
        $user = $this->User->find('first', array(
            'conditions' => array(
                'User.username' => $username
            ),
            'contain' => array(
                'Customer',
                'ParentUser'
            )
        ));

        //test for a valid user & matching password
        if (empty($user) || ($user['User']['password'] != $this->Auth->password($password))) {
            $this->Flash->set('This email link has expired. Contact your manager if you don\'t have access to the site.');
            $this->redirect(array('action' => 'login'));
        }

        //login user
        $this->Auth->login(array(
            'id' => $user['User']['id'],
            'username' => $user['User']['username'],
            'role' => $user['User']['role'],
            'password' => $user['User']['password']
        ));

        //write user session information
        $this->Session->write('Auth.User', $user['User']);
        $this->Session->write('Auth.User.ParentUser', $user['ParentUser']);
        $this->Session->write('Auth.User.Customer', $user['Customer']);
        $this->initUser();
        $this->resetPassword($password);
        $this->render('reset_password');
    }

    /**
     *
     * @param type $username
     * @param type $password
     */
    public function resetPassword($password = '') {
        $requireCurrent = ($this->request->params['action'] === 'registration') ? false : true;
        if ($this->request->is('post') || $this->request->is('put')) {
            $pass = $this->User->field('password', array('id' => $this->request->data['User']['id']));
            if ($pass === $this->Auth->password($this->request->data['User']['currentPassword'])) {
                // do the two new passwords match?
                if ($this->request->data['User']['password'] === $this->request->data['User']['verifyPassword']) {
                    $this->request->data['User']['logged_in'] = time();
                    $this->request->data['User']['verified'] = 1;
                    $this->request->data['User']['password'] = $this->Auth->password($this->request->data['User']['password']);

                    $this->User->create(false);
                    $this->User->Behaviors->unload('ThinTree');
                    $save = $this->User->save($this->request->data, array('validate' => false, 'callbacks' => false));

                    // did the password save?
                    if ($save) {
                        $this->Flash->success('Your password was reset');
                        // did the user request this change? (or was it part of the registration process)
                        if (!$requireCurrent) {
                            $this->Flash->success('Your password was reset. Welcome to the Amp Finished Goods System.');
                        } else {
                            $Email = new CakeEmail();
                            $Email->config('smtp')
                                ->template('password_change', 'default')
                                ->viewVars(array(
                                    'message' => 'If you did not change your password, please contact your administrator and ask them to reset your access.'
                                ))
                                ->emailFormat('html')
                                ->from(array('ampfg@ampprinting.com' => 'AMP FG System Robot'))
                                ->to($this->Auth->user('username'))
                                ->subject('Your AMP FG password was reset')
                                ->send();

                        }
                        $this->redirect(array('controller' => Inflector::underscore($this->Auth->user('group')), 'action' => 'status'));
                    } else {
                        $this->Flash->error('Your new password did not save properly. Please try again');
                    }
                } else {
                    $this->Flash->error('Both passwords must match. Please try again.');
                }
            } else {
                $this->Flash->error('Your current password is not correct');
            }
            // validate and save
        }
        if (isset($this->request->data['User']['password']) && $this->request->data['User']['password'] == '' && $password == '' ) {
            $password = $this->request->data['User']['password'];
        }
        $this->set(compact('requireCurrent', 'password'));
    }

    //============================================================
    // GRAIN MANAGEMENT METHODS
    //============================================================

    /**
     * Render and Save user data from the User Grain page
     *
     * @param type $id
     * @param type $hash
     */
    public function userEdit($id, $hash) {
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->secureData($this->request->data, 'User')) {
                $this->User->create();
                $this->User->validator()->remove('parent_id');
                $this->User->Behaviors->detach('ThinTree');
                if ($this->User->save($this->request->data, true, array(
                        'id',
                        'first_name',
                        'last_name',
                        'username',
                        'role',
                        'folder',
                        'active',
                        'use_budget',
                        'budget',
                        'use_item_budget',
                        'item_budget',
                        'rollover_budget',
                        'rollover_item_budget'
                    )
                )) {
                    $this->redirect(array('action' => 'edit_userGrain', $this->request->data['User']['id'], $this->request->data['User']['secure']));
                } else {
                    $this->Flash->set('The record did not save.');
                }
            }
        }
        if ($this->secureId($id, $hash)) {
            $this->fetchRecordForEdit($id);
            $this->layout = 'ajax';
            $roles = $this->User->getSecureList($this->Auth->user('role'), 'role');
            $this->set(compact('roles', 'id'));
            $this->render('/Elements/user_form');
        };
    }

    /**
     * Ajax address/vendor edit call
     *
     * @param int $id Id of the form populating record
     */
    public function addressEdit($id = NULL) {
        $this->redirect(array('controller' => 'addresses', 'action' => 'addressEdit', $id));
    }

    /**
     * Ajax address/vendor new call
     *
     * @param int $id Id of the form populating record
     */
    public function addressAdd($id = NULL) {
        $this->redirect(array('controller' => 'addresses', 'action' => 'addressAdd', $id));
    }

    /**
     * Ajax address/vendor new call
     *
     * @param int $id Id of the form populating record
     */
    public function observerEdit($id = NULL) {
        $this->redirect(array('controller' => 'observers', 'action' => 'observerEdit', $id));
    }

    /**
     * Ajax address/vendor new call
     *
     * @param int $id Id of the form populating record
     */
    public function userObserverEdit($id = NULL) {
        $this->redirect(array('controller' => 'observers', 'action' => 'userObserverEdit', $id));
    }

    /**
     * Ajax address/vendor new call
     *
     * @param int $id Id of the form populating record
     */
    public function observerAdd($id = NULL) {
        $this->redirect(array('controller' => 'observers', 'action' => 'observerAdd', $id));
    }

    /**
     * Ajax address/vendor new call
     *
     * @param int $id Id of the form populating record
     */
    public function userObserverAdd($id = NULL) {
        $this->redirect(array('controller' => 'observers', 'action' => 'userObserverAdd', $id));
    }

    public function userPermissionEdit($id = NULL) {
        if ($this->request->is('post') || $this->request->is('put')) {
            // The HABTM fields won't validate. Do it manually
            $this->validateIds($this->request->data['UserManaged']);

            if ($this->User->save($this->request->data)) {
                $this->Flash->success(__('The user access has been saved'));
                return $this->redirect($this->referer());
            }
            $this->Flash->error(__('The user access could not be saved. Please, try again.'));
            return $this->redirect($this->referer());
        }
        $this->fetchRecordForEdit($id);
        $this->fetchVariablesForEdit();
        $this->layout = 'ajax';
        $this->render('/Elements/user_permission_form');
    }

    public function catalogPermissionEdit($id = NULL) {
        if ($this->request->is('post') || $this->request->is('put')) {
            // The HABTM fields won't validate. Do it manually
            $this->validateIds($this->request->data['Catalog']);

            if ($this->User->save($this->request->data)) {
                $this->Flash->success(__('The catalog access has been saved'));
                return $this->redirect($this->referer());
            }
            $this->Flash->error(__('The catalog access could not be saved. Please, try again.'));
            return $this->redirect($this->referer());
        }
        $this->fetchRecordForEdit($id);
        $this->fetchVariablesForEdit();
        $this->layout = 'ajax';
        $this->render('/Elements/catalog_permission_form');
    }

    //============================================================
    // ORPHAN MANAGEMENT METHODS
    //============================================================

    public function fetchOrphans(){
        $orphans = array();
        //pull items with no catalog
        $orphanItems = array();
        $this->Item = ClassRegistry::init('Item');
        $allItems = $this->Item->find('list');
        $this->Catalog = ClassRegistry::init('Catalog');
        $allCatalogs = $this->Catalog->find('list', array(
            'fields' => array(
                'Catalog.item_id',
                'Catalog.id'
            )
        ));
        foreach ($allItems as $id => $item) {
            if(!array_key_exists($id, $allCatalogs)){
                $orphanItems[$id] = $item;
            }
        }

        //pull customers with no user
        $allCustomers = $this->User->Customer->find('list', array(
            'fields' => array(
                'Customer.user_id',
                'Customer.id'
            )
        ));
        $allUsers = $this->User->find('list');

        foreach ($allCustomers as $userId => $customerId) {
            if(!array_key_exists($userId, $allUsers)){
                $orphanCustomers[$customerId] = $userId;
            }
        }


        //pull users with no / bad ancestor tree
        $orphanUserAncestors = array();
        $allUserAncestors = $this->User->find('list', array (
            'conditions' => array(
                'User.id !=' => $this->User->ultimateRoot
            ),
            'fields' => array(
                'User.id',
                'User.ancestor_list'
            )
        ));

        foreach ($allUserAncestors as $user => $ancestor_list) {
            $ancestor = explode(',', ltrim(rtrim($ancestor_list, ','),','));
            foreach ($ancestor as $index => $id) {
                if(!array_key_exists($id, $allUsers)){
                    $orphanUserAncestors["$user->$id"] = "Ancestor $id no longer exists. User $user's ancestor list is invalid";
                }
            }
        }

        //pull catalogs with no / bad ancestor tree
        $orphanCatalogAncestors = array();
        $allCatalogs = $this->Catalog->find('list');
        $allCatalogAncestors = $this->Catalog->find('all', array (
            'conditions' => array(
                'Catalog.id !=' => $this->Catalog->ultimateRoot
            ),
            'fields' => array(
                'Catalog.id',
                'Catalog.ancestor_list',
                'Catalog.parent_id'
            ),
            'contain' => false
        ));


        foreach ($allCatalogAncestors as $index => $fields) {
            $ancestor = explode(',', ltrim(rtrim($fields['Catalog']['ancestor_list'], ','),','));
            if ($ancestor[count($ancestor)-1] != $fields['Catalog']['parent_id']){
                $orphanCatalogAncestors["{$fields['Catalog']['id']}->parent{$fields['Catalog']['parent_id']}->ancestor{$ancestor[count($ancestor)-1]}"] = "Catalog {$fields['Catalog']['id']} has a parent/ancestor mismatch";
            }
            foreach ($ancestor as $index => $id)
                if(!array_key_exists($id, $allCatalogs)){
                    $orphanCatalogAncestors["{$fields['Catalog']['id']}->$id"] = "Catalog ancestor $id no longer exists. Catalog {$fields['Catalog']['id']}'s ancestor list is invalid";
                }
        }

        //pull addresses with no user
        $orphanUserAddresses = array();
        $allNonvendorAddresses = $this->User->Address->find('list', array (
            'conditions' => array(
                'Address.type !=' => 'vendor'
            ),
            'fields' => array(
                'Address.id',
                'Address.user_id'
            )
        ));

        foreach ($allNonvendorAddresses as $id => $user_id) {
            if(!array_key_exists($user_id, $allUsers)){
                $orphanUserAddresses[$id] = "The user ($user_id) connected to Address ($id) no longer exists.";
            }
        }

        //pull budgets with no user
        $orphanBudgets = array();
        $allBudgets = $this->User->Budget->find('list', array(
            'fields' => array(
                'Budget.id',
                'Budget.user_id'
            )
        ));

        foreach ($allBudgets as $id => $user_id) {
            if(!array_key_exists($user_id, $allUsers)){
                $orphanBudgets[$id] = "The user ($user_id) connected to Budget ($id) no longer exists.";
            }
        }

        //pull out of date carts
        $this->Cart = ClassRegistry::init('Cart');
        $outOfDateCarts = $this->Cart->find('all', array(
            'conditions' => array(
                'Cart.modified <= DATE_ADD(CURDATE(), INTERVAL -30 DAY)'
            ),
            'fields' => array('Cart.id', 'Cart.modified')
        ));

        $this->set(compact('orphanItems',
            'orphanCustomers',
            'orphanUserAncestors',
            'orphanCatalogAncestors',
            'orphanUserAddresses',
            'orphanBudgets',
            'outOfDateCarts'));
    }

    /**
     * Reactivation interface for User entries
     *
     * @todo Need to add node-permission filtering to the query
     * @todo Need to get Customer info for labeling the elements on the view
     */
    public function inactive($customer = NULL, $state = NULL) {

        $customers = $this->User->getPermittedCustomers($this->Auth->user('id'), 0);
        $this->processed = false;

        // default data to populate filter-inputs. these will match url values
        $this->request->data = array(
            'customer' => '',
            'active' => 'inactive',
            'paginationLimit' => (isset($this->request->params['named']['limit'])) ? $this->request->params['named']['limit'] : 25
        );

        // build up proper query conditions given current url params and other factors
        $allowed = $this->User->getAccessibleUserInList();
        $this->conditions = array(
            'User.id' => $allowed,
            'User.active' => '0',
            'NOT' => array(
                'User.id' => 1
            )
        );
        $args = func_get_args();
        if (!empty($args)) {
            foreach ($args as $index => $arg) {
                $result = preg_match('/\d*/', $arg, $match);

                // detected a customer id in the url
                if ($match[0] == $arg) {
                    $this->intArg($arg, $customers);

                    // detected a state-filter in the url (actually, it's a string that doesn't appear to be a cust id
                } elseif (!$this->processed) {
                    $this->stringArg($arg);
                }
            }
        }
        try {
            $this->paginate = array(
                'limit' => $this->request->data['paginationLimit'],
                'conditions' => $this->conditions,
                'contain' => 'Customer'
            );
            $this->Paginator->settings = $this->paginate;
            $users = $this->Paginator->paginate();


        } catch (Exception $exc) {
            // probably a filter changed the return count and the
            // previous page # is now out of range. go back to page 1.
            $this->request->params['named']['page'] = 1;
            $catalogs = $this->Paginator->paginate();
        }
        $this->set('customers', $customers);
        $this->set('users', $users);
    }
    /**
     * From state filter choice, set query condition and UI input value
     *
     * @param string $arg assumed to be a state filter choice
     */
    private function stringArg($arg) {
        $this->processed = true;
        switch ($arg) {
            case 'all' :
                $this->request->data['active'] = 'all';
                unset($this->conditions['User.active']);
                break;
            case 'active' :
                $this->conditions['User.active'] = '1';
                $this->request->data['active'] = 'active';
                break;
            case 'inactive' :
            default:
                $this->conditions['User.active'] = '0';
                $this->request->data['active'] = 'inactive';
                break;
        }
    }

    /**
     * From customer filter choice, set the query condition and UI input value
     *
     * @param int $arg requested cust id filter value
     * @param array $allowed List of cust ids allowed for this user
     */
    private function intArg($arg, $allowed) {
        if (array_key_exists($arg, $allowed)) {
            $this->conditions['OR'] = array(
                'User.ancestor_list LIKE' => ",1,$arg,%",
                'User.id' => $arg);
            $this->request->data['customer'] = $arg;
            unset($this->conditions['User.id']);
            unset($this->conditions['NOT']);
        }
    }

    /**
     * Set user record active state to provided parameter
     *
     * @param int $id the id of the catalog record
     * @param int $state the desired state of the catalog record
     * @return html Elements/Catalog/inactive_row
     */
    public function setActive($id, $state) {
        if($state == 1){
            $activeToggle = 'activate';
        } else {
            $activeToggle = 'deactivate';
        }
        $valid = $this->validateSelect($id, 'li');
        $this->layout = 'ajax';
        try{
            $this->edit_deactivate($id, $activeToggle);
        } catch (Exception $exc){
            return $exc->getTraceAsString();
        }
        try {
            $user = $this->User->find('all', array(
                'conditions' => array('User.id' => $valid[0]),
                'contain' => array('Customer')));
        } catch (Exception $exc) {
            return $exc->getTraceAsString();
        }
        $this->set('user', $user[0]);
        $this->render('/Elements/User/inactive_row');
    }

}