<?php

/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('Controller', 'Controller');
App::uses('CakeEmail', 'Network/Email');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::uses('$this->_reportStatus', 'Lib');
App::uses('Observer', 'Model');
App::uses('Logger', 'Lib/Trait');
App::uses('RobotProcessException', 'Lib/Exception');
App::uses('Markdown', 'Plugin/Markdown');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
    
//    use Logger;

	/**
	 *
	 * @var type
	 * @toDo abstract the login and logout redirect destination
	 */
	public $components = array(
		'DebugKit.Toolbar',
		'Session',
		'Auth' => array(
			'authorize' => array('Controller'),
			'logoutRedirect' => array('controller' => 'users', 'action' => 'login')
		),
		'Markdown.Markdown',
		'RequestHandler'
	);
	public $helpers = array(
		'Html',
		'Form',
		'Session',
		'Js',
		'FgHtml',
		'FgForm',
		'Markdown.Markdown'
	);
	public $uses = array(
		'User', 'Menu', 'Catalog', 'UserRegistry', 'Preference', 'Address', 'Gateway'
	);
	public $menuData = array();
	public $menuConditions = array();
	public $childMenuConditions = array();

	/**
	 * Actions that will not redirect to timeout
	 *
	 * @var array The list of actions
	 */
	public $allowed = array(
		'display' => 'display',
		'logout' => 'logout',
		'login' => 'login',
		'timeout' => 'timeout',
		'resetPassword' => 'resetPassword',
		'validateAccess' => 'validateAccess',
		'registration' => 'registration',
		'takeAction' => 'takeAction',
		'initUser' => 'initUser',
		'forgotPassword' => 'forgotPassword',
		'testMe' => 'testMe',
		'input' => 'input',
		'output' => 'output',
		'updateOrder' => 'updateOrder',
		'updateRemainingBudget' => 'updateRemainingBudget',
		'getRemainingBudget' => 'getRemainingBudget',
		'statusChange' => 'statusChange',
		'updateShippingOrders' => 'updateShippingOrders',
		'cron' => 'cron'
	);
	/**
     * Default map of action access permissions
     *
     * This property sets up the basic pattern for access arrays
	 * to be modified in specific controllers
     *
     * @var array Allowed actions for various users
     */
    public $accessPattern = array(
        'Manager' => array(),
        'Buyer' => array(),
        'Guest' => array(),
        'Staff' => array(),
        'Clients' => array(),
        'Warehouses' => array(),
		'AdminsManager' => array('all'),
        'StaffManager' => array(),
        'StaffBuyer' => array(),
        'StaffGuest' => array(),
        'ClientsManager' => array(),
        'ClientsBuyer' => array(),
        'ClientsGuest' => array(),
		'WarehousesManager' => array()
    );

	public $layout = 'timed_simple';
	public $jquery = 'jquery-1.10.2.min';

	/**
	 * Session timeout limits
	 * 
	 * @var array The idle and warning limits 
	 */
	public $timerParams = array(
		'idleLimit' => HOUR, //enter seconds (20 minutes)
		'warningLimit' => 120 //enter seconds (2 minutes)
	);

	/**
	 * The action verb to resulting status array
	 * 
	 * i.e., if you "Ship" an order, it ends up "Shipped"
	 * 
	 * @var array 
	 */
	public $orderEventMap = array(
		'Backorder' => 'Backordered',
		'Submit' => 'Submitted',
		'Approve' => 'Approved',
		'Release' => 'Released',
		'Pull' => 'Pulled',
		'Ship' => 'Shipped',
		'Shipping' => 'Shipped',
		'Invoice' => 'Invoiced',
		'Archive' => 'Archived',
		'Place' => 'Placed',
		'Complete' => 'Completed'
	);

	
	/**
	 * Private array of the shipment billing options
	 * @var array 
	 */
	private $shipmentBillingOptions = array(
		'Sender' => 'Sender',
		'Receiver' => 'Receiver',
        'Customer' => 'Customer',
		'ThirdParty' => 'ThirdParty'
		);

	/**
	 * The observers of the thingy
	 *
	 * @var array The set of observer data for a situation
	 */
	private $observers = array();
	
	private $overbudget = FALSE;

	private $submitEvaluated = FALSE;

	public $debugVal = 0;
	
	/**
	 * An accumulator for array data to return on ajax process
	 *
	 * @var array accumulated data for json return
	 */
	public $jsonReturn = array();
	
	/**
	 *
	 * @todo Remove the users list if possible
	 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->debugVal = Configure::read('debug');
		//check browser for jQuery version
		$this->setJqueryVersion();

		$this->Auth->allow($this->allowed);
		if (key_exists($this->request->action, $this->allowed)) {
			$this->layout = 'simple';
		}
		if ($this->Auth->user('id') == '' && !key_exists($this->request->action, $this->allowed)) {
			$this->redirect('/users/login');
		}
		//Pass controller name to javascript on every layout
		$this->set('controller', $this->request->controller);
		//Check for open edits and clear if appropriate
		$this->clearLock();
		//Admin tool to establish of users for header login button
		$this->set('adminLoginUsers', $this->User->find('list', array(
					'fields' => array(
						'username',
						'username'
					)
		)));
		$this->set('timerParams', $this->timerParams);
	}

	/**
	 * @todo This is not the final method
	 * @param type $MenuCondition
	 */
	public function beforeRender() {
		parent::beforeRender();
		$this->menuData = $this->Menu->find('threaded', array(
			'conditions' => array('Menu.parent_id' => '0'),
			'fields' => array('Menu.id', 'Menu.name', 'Menu.controller', 'Menu.action', 'Menu.lft', 'Menu.access', 'Menu.group'),
			'order' => 'Menu.lft ASC'
		));
		foreach ($this->menuData as $index => $menu) {
			$tmp = array();
			foreach ($menu['ChildMenu'] as $cmenu) {
				$tmp[$cmenu['lft']] = $cmenu;
			}
			ksort($tmp);
			$this->menuData[$index]['ChildMenu'] = $tmp;
		}

		$this->set('menuItems', $this->menuData);
		
		if ($this->Auth->user('budget_id') != null) {
			$this->installComponent('Budget');
			$this->set('budget', $this->Budget->refreshBudget());
		}

	}

	/**
	 * Verify that a Controller and Action exist
	 * 
	 * @param type $controller Full name like 'ObserversController'
	 * @param type $action Name of an action
	 * @return boolean does the action exist in the controller
	 */
	public function gateCheck($controller, $action) {
		$target = ucfirst($controller.'Controller');
		App::uses($target, 'Controller');
		$actions = get_class_methods($target);
		if (is_array($actions) && in_array($action,  $actions)) {
			return true;
		} else {
			return false;
		}
		
	}
	//============================================================
	// SITE ACCESS AND NODE PERMISSION
	//============================================================

	/**
	 * A preliminary take on controller level access control
	 *
	 * @todo Is this still useful in our overall scheme?
	 * @param type $user
	 * @return boolean
	 */
	public function controllerAccess($user) {
		if (isset($user['role'])) {
			if ($this->request->controller == strtolower($this->Session->read('Auth.User.group')) || $this->Session->read('Auth.User.group') == 'Admins') {
				return true;
			}
		}

		$this->Session->setFlash('You aren\'t allowed on a ' . $this->request->controller . ' ' . $this->request->action . ' page.');
		$this->redirect(array(
			'controller' => strtolower($this->Session->read('Auth.User.group')),
			'action' => 'status'
		));
	}

	/**
	 * Construct the conditions to control Menu query
	 *
	 * @return array The conditions array
	 */
	public function setMenuConditions() {
		$group = $this->readGroup();
		$access = $this->readAccess();
		switch ($group) {
			case 'Admins':
				$this->menuConditions = array('1' => '1');
				break;
			case 'Staff':
				break;
			case 'Clients':
                //setup base query on guest
				$this->menuConditions = $this->menuQueryCondition($group, 'Guest');
				$this->childMenuConditions = $this->ChildMenuQueryCondition($group, 'Guest');
				if ($access == 'Buyer' || $access == 'Manager') {
					$this->menuConditions = $this->menuConditions + $this->menuQueryCondition($group, 'Buyer');
					$this->childMenuConditions = $this->childMenuConditions + $this->childMenuQueryCondition($group, 'Buyer');
				}
				if ($access == 'Manager') {
					$this->menuConditions = $this->menuConditions + $this->menuQueryCondition($group, 'Manager');
					$this->childMenuConditions = $this->childMenuConditions + $this->childMenuQueryCondition($group, 'Manager');
				}
				debug($this->menuConditions);
				break;
			default:
				break;
		}
	}

	/**
	 * Read the Users Access Level from Auth Session
	 *
	 * @return string The logged in users group
	 */
	public function readAccess() {
		return $this->Session->read('Auth.User.access');
	}

	/**
	 * Read the Users Group from Auth Session
	 *
	 * @return string The logged in users group
	 */
	public function readGroup() {
		return $this->Session->read('Auth.User.group');
	}

	public function menuQueryCondition($group, $access) {
		return array('Menu.group' => $group, 'Menu.access' => $access);
	}

	public function childMenuQueryCondition($group, $access) {
		return array('ChildMenu.group' => $group, 'ChildMenu.access' => $access);
	}

	/**
	 * Determine action permission for visiting User
	 *
	 * Merge all possible action arrays and check
	 * to see if we're requesting an allowed action.
	 * NOTE:
	 * When properly designed, only one of the three
	 * elements will have an action list.
	 *
	 * @param array $user User data from AuthComponent
	 * @return boolean Allow access or not
	 */
	public function authCheck($user, $accessPattern) {
		//Check if the user has timed out
		$this->checkTime($user['id']);

		//Check if the user needs to refresh their session
		$this->checkSessionIntegrity($this->Auth->user('id'));

		//Proceed to authCheck
		$group = $this->Auth->user('group');
		$access = $this->Auth->user('access');

		if ($group == 'Admins') {
			return true;
		}

		$allowed = array_merge(
				$accessPattern[$group], $accessPattern[$access], $accessPattern[$group . $access]
		);
		if ($allowed[0] == 'all') {
			return true;
		}
		if (in_array($this->action, $allowed)) {
			return true;
		}
		return false;
	}

	/**
	 * write the Catalog and User node permissions into the Auth session
	 * 
	 * Possibly this provides a way to alias another user?
	 * If you pass another users id, those permissions would
	 * get written into the session
	 * 
	 * @param int $id User id
	 */
	public function setNodeAccess($id = false) {
		$user_id = (!$id) ? $this->Auth->user('id') : $id;
		$this->catalogRoots = $this->User->getOwnedCatalogRoots($user_id);
		$this->userRoots = $this->User->getOwnedUserRoots($user_id);
		$this->Session->write('Auth.User.CatalogRoots', $this->catalogRoots);
		$this->Session->write('Auth.User.UserRoots', $this->userRoots);
	}

	public function setRoleAccess($role = false) {
		//pick the logged in user's role from the database, if there is one
		$dbRole = $this->User->find('first', array(
			'conditions' => array($this->User->escapeField() => $this->Auth->user('id')),
			'fields' => array('id', 'role'),
			'contain' => false));
		//if there is no role in the database, get the role from your closest parent
		if (!$dbRole['User']['role']) {
			$role = $this->getNearestRole();
		}
		//process the role and set the sessions
		$localrole = (!$role) ? $this->Auth->user('role') : $role;
		if (($localrole != '' && !$this->Auth->user('group')) || $role) {
			$this->Session->write('Auth.User.role', $localrole);
			$roleGrain = explode(' ', $localrole);
			$this->Session->write('Auth.User.group', $roleGrain[0]);
			$this->Session->write('Auth.User.access', $roleGrain[1]);
		}
	}

	public function getSortedAncestors() {
		$source = $this->Auth->User('ancestor_list');
		$returnArray = explode(',', $source);
		$trim = array_pop($returnArray);
		return array_reverse($returnArray);
	}

	public function getNearestRole() {
		$sortedAncestors = $this->getSortedAncestors();
		$trim = array_pop($sortedAncestors);
		$role = '';
		foreach ($sortedAncestors as $key => $value) {
			$role = $this->User->find('first', array(
				'conditions' => array($this->User->escapeField() => $value)));
			if ($role['User']['role'] != '') {
				$nearestRole = $role['User']['role'];
				break;
			}
		}
		return $nearestRole;
	}

	public function passRootNodes($type) {
		$this->set('rootNodes', $this->Auth->user($type . 'Roots'));
	}

	//============================================================
	// ID/HASH SECURITY ROUTINES TO MOVE TO SEPARATE CLASS
	//============================================================

	/**
	 * See if the User needs to update their session data
	 * 
	 * DB changes may make a user's session data stale.
	 * Each user has a flag value in their record that gets set
	 * to notify them when they need to update their session
	 * 
	 * @param type $id Id of the User to work on
	 */
	public function checkSessionIntegrity($id) {
		$user = $this->User->find('all', array(
			'conditions' => array($this->User->escapeField() => $id),
			'fields' => array('id', 'role', 'session_change'),
			'contain' => false));
		$this->setNodeAccess($user[0]['User']['id']);
		$this->setRoleAccess($user[0]['User']['role']);

		$this->User->id = $id;
		$this->User->Behaviors->disable('ThinTree');
		$this->User->saveField('loggedIn', time());
		$this->User->Behaviors->enable('ThinTree');
	}

	public function checkTime($id) {
		$logged_in = $this->User->field('logged_in', array($this->User->escapeField() => $id));
		$idle = (time() - $logged_in) > $this->timerParams['idleLimit'] + 180 ? true : false;
		if ($idle) {
			$this->Session->destroy();
			//if user has exceeded idleLimit by 3min, log them out
			$this->redirect(array('controller' => 'users', 'action' => 'timeout'));
		}
		//else reset the user time flag
		$this->flagUserRecord();
	}

	/**
	 * Verify that POSTed ID was not altered/spoofed
	 *
	 * @param type $data
	 * @param type $model
	 * @return type
	 * @throws BadMethodCallException
	 */
	public function secureData($data = null, $model = null) {
		if (!is_array($data) || !key_exists('id', $data[$model]) || !key_exists('secure', $data[$model])) {
			throw new BadMethodCallException('Missing security-check parameter(s) or expected array elements');
		}
		return $this->secureId($data[$model]['id'], $data[$model]['secure']);
	}

	/**
	 * Verify that ID was not altered/spoofed
	 *
	 * @param type $id
	 * @param type $hash
	 * @return boolean
	 * @throws BadMethodCallException
	 */
	public function secureId($id = null, $hash = null) {
		if ($id === null || $hash === null) {
			throw new BadMethodCallException('Missing security-check parameter(s).');
		}
		if ($this->secureHash($id) == $hash) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Write the hash value to prevent id tampering
	 *
	 * Also defined in AppHelper & AppModel
	 * Create a hash value from a record id and user/session specific
	 * values. The id and hash can be sent to the client, then on
	 * return to the server we can verify the id has not been altered
	 *
	 * @param string $id The record id to secure
	 * @return string The secure has to use as a verification value
	 */
	public function secureHash($id) {
		return sha1($id . AuthComponent::user('id') . AuthComponent::$sessionKey . Configure::read('Security.salt'));
	}

	/**
	 * Provide a complete secured select list item
	 *
	 * Concatenate the actual id and chosen delimeter with the secureHash
	 *
	 * @param string $id The record id to secure
	 * @param string $delimeter The delimeter to concat the string on, default to '/'
	 * @return string The concatenation
	 */
	public function secureSelect($id, $delimeter = '/') {
		return $id . $delimeter . $this->secureHash($id);
	}

	/**
	 * validate a delimited secure pair
	 *
	 * explode and check a secured string for client-side tampering
	 * return all the values and the boolean result in an array
	 *
	 * @param string $id The value to secure
	 * @param string $delimeter The delimeter to concat the string on, default to '/'
	 * @return array array (id, hash, true/false)
	 */
	public function validateSelect($securePair, $delimeter = '/') {
		if (strstr($securePair, $delimeter) > '') {
			$check = explode($delimeter, $securePair);
			if (count($check == 2)) {
				$check[2] = $this->secureId($check[0], $check[1]);
				return $check;
			}
		}
		$this->Session->setFlash('An improperly formatted security string was found.');
		return false;
	}

	//============================================================
	// COMMON AJAX TOOLS FOR TREES (USER AND CATALOG)
	//============================================================

	/**
	 * Process a tree node move
	 * 
	 * A standard tree edit from the drag/drop interface 
	 * makes an ajax call to here with a form containing
	 * 	    id of moved element
	 * 	    id of the moved elements new previous-sequence
	 * 	    id of the moved elements new parent
	 * We also have security hash values for everything.
	 * 
	 * Verify all form values were sent and that they haven't 
	 * been tampered with, then update the database to 
	 * reflect the requested change.
	 * 
	 * @todo Send a security message to Admin if $tamper throws an exception
	 * @param Model $Model Necessary abstraction, many Controllers use this code
	 * @return string|false An ajax HTML fragment or false
	 */
	public function treeJax(Model $Model) {
		$this->layout = 'ajax';
		$secure = true;
		$change = array();
		foreach ($this->request->data[$Model->alias] as $index => $value) {
			if ($value == 'invalid') {

				// one of the values in the data form was left as the default 'invalid'
				$this->Session->setFlash('There was an error saving your data
		    (The app/treeJax function failed due to invalid form data)');
				$this->set('data', null);
				return FALSE;
			}
			//break value into id & hash on ul for parents or li for other
			if ($index == 'sequence' || $index == 'type_context') {

				// indicates moved item is the new First Sibling
				$change[$index] = array($value + .5);
			} else {

				// do a tampering-check on the record id
				$delimeter = ($index == 'parent_id') ? 'ul' : 'li';
				$change[$index] = explode($delimeter, $value);
				$secure = $secure && $this->secureId($change[$index][0], $change[$index][1]);
			}
		}
		if (!$secure) {
			// one of the values in the data form did not match it's hash
			$this->Session->setFlash('There was an error saving your data
		(The app/treeJax function failed due to non-matched form data)');
			$this->set('data', null);
			return FALSE;
		}
		$data = array($Model->alias => array(
				'id' => $change['id'][0],
				'sequence' => $change['sequence'][0],
				'parent_id' => $change['parent_id'][0]
		));
		$this->saveTreeEdit($Model, $data, $change['currentNode'][0]);
	}

	/**
	 * Save the ajax'd tree edit and get set the variables for the render
	 * 
	 * The various ajax edit processes send their final data
	 * here to be saved to the db. All the variables for the
	 * re-rendered tree are set here too.
	 * 
	 * @param Model $Model
	 * @param string $data The changed node data
	 * @param type $id Id of the node to detail for editing
	 * @return boolean|void false if data save fails
	 */
	private function saveTreeEdit(Model $Model, $data, $currentNode) {
		if ($Model->save($data, FALSE)) {
			$this->resetTreeEditVariables($Model, $currentNode);
		} else {
			return false;
		}
	}

	/**
	 * Set the view vars necessary to display an editable tree
	 * 
	 * Pull the tree from node $id down
	 * Send the list of permitted root nodes for the user
	 * Indicate whether the user is locked out of editing
	 * Pass debug data for development
	 * 
	 * @param Model $Model
	 * @param type $id The id of the node root node
	 */
	private function resetTreeEditVariables(Model $Model, $id) {
		$editTree = $this->{$Model->alias}->getFullNode($id);
		$this->set('renderNode', $id);
		$this->passRootNodes($Model->alias);
		if ($Model->alias == 'User') {
			$this->set('editTree', $this->detailTreeNodes($editTree));
		} else {
			$this->set('editTree', $editTree);
		}
		$this->set('lock', false); //this assumes that this call is made only during ajax editing
		$data['result'] = 'Success!';
		$this->set('data', $data);
	}

	/**
	 * Demote a node (and decendents) to the child of the indicated parent
	 * 
	 * One of several ajax method to allow drag/drop tree editing
	 * 
	 * @param Model $Model
	 */
	public function ajax_toChild(Model $Model) {
		$this->layout = 'ajax';
		$this->set('data', $this->request->data);
		// validate the data fields
		$node = $this->validateSelect($this->request->data[$Model->alias]['id'], 'li');
		$parent = $this->validateSelect($this->request->data[$Model->alias]['parent_id'], 'li');
		$currentNode = $this->validateSelect($this->request->data[$Model->alias]['currentNode'], 'li');
		if (!$parent[2] || !$node[2] || !$currentNode[2]) {
			throw new BadRequestException('The id of the node to move, the target parent node or the currently detailed node could not be validated.');
		}
		// strip inputs down to just the IDs and save
		$data[$Model->alias][$Model->primaryKey] = $node[0];
		$data[$Model->alias]['parent_id'] = $parent[0];
		$data[$Model->alias]['sequence'] = $this->request->data[$Model->alias]['sequence'];
		$this->saveTreeEdit($Model, $data, $currentNode[0]);
	}

	/**
	 * Submit data for a new sibling
	 * 
	 * Called from Catalog or User for proper Model setting
	 * The vars were all set on the page.
	 * Just go ahead with the same add as new child
	 * 
	 * @param Model $Model
	 */
	public function ajax_newSibling(Model $Model) {
		$this->ajax_newChild($Model);
	}

	/**
	 * AJAX SAVE: newChild, newSibling, editNode
	 * 
	 * Called from Catalog or User for proper Model setting
	 * Normalize the data so Validation can work
	 * Make sure the id of the detailed node hasn't been tampered with
	 * Call the add method
	 * Set everything up for the new display
	 * 
	 * @param Model $Model
	 * @throws BadRequestException
	 */
	public function ajax_newChild(Model $Model) {
		$this->layout = 'ajax';
		//swap important 'li' or 'ul' delimeters for '/' delimeters
		$this->request->data[$Model->alias]['parent_id'] = str_replace(array('li', 'ul'), '/', $this->request->data[$Model->alias]['parent_id']);
		$currentNode = $this->validateSelect($this->request->data[$Model->alias]['currentNode'], 'li');
		if ($currentNode[2]) {
			//run appropriate add function
			$this->ajaxEdit(); // Users or Catalogs Controller
			$this->resetTreeEditVariables($Model, $currentNode[0]);
			//return to refreshed list
			$this->set('data', $this->request->data);
		} else {
			throw new BadRequestException('Current Node value didn\'t match its hash');
		}
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
	public function ajax_RenderEditForm($id) {
		// turn this off until i've edited this
		$valid = $this->validateSelect($id, 'li');
		if ($valid[2]) {
			$this->layout = 'ajax';
			$this->fetchRecordForEdit($valid[0]);
			$this->fetchVariablesForEdit();
		} else {
			throw new BadRequestException('the chosen node would not validate');
		}
	}

	/**
	 * 
	 * @return boolean return is json object, with access as true or false
	 */
	public function validateAccess() {
		$this->autoRender = false;
		//set user id
		$userId = $this->Auth->user('id');
		$recordId = $this->request->data['id'];
		$userAccess = $this->Auth->user('access');

		//set default access level to Manager
		if (isset($this->request->data['accessLevel'])) {
			$accessLevel = $this->request->data['accessLevel'];
		} else {
			$accessLevel = 'Manager';
		}

		//if the user is attempting to edit their own record, allow it
		$nakedID = explode('/', $recordId);
		if ($this->request->params['controller'] == 'users' && $userId == $nakedID[0]) {
			echo json_encode(array('access' => true));
			return;
		}

		//check if the user passes the accesslevel test
		if ($accessLevel == 'Buyer' && $userAccess == 'Guest') {
			echo json_encode(array('access' => false));
			return;
		} elseif ($userAccess != 'Manager') {
			echo json_encode(array('access' => false));
			return;
		}

		//pull the validated array
		if (stristr($this->request->data['id'], '/')) {
			$delimeter = '/';
		} else {
			$delimeter = 'li';
		}
		$validatedArray = $this->validateSelect($this->request->data['id'], $delimeter);
		if ($validatedArray[2]) {
			$id = $validatedArray[0];
		} else {
			echo json_encode(array('access' => false));
			return;
		}
		if ($this->request->params['controller'] == 'users') {
			$in = $this->User->getAccessibleUserInList();
		} else {
			$in = $this->Catalog->getAccessibleCatalogInList($this->Auth->user('CatalogRoots'));
		}
		
		//NOW, the test
		if (isset($in[$id])) {
			echo json_encode(array('access' => true));
		} else {
			echo json_encode(array('access' => false));
		}
	}

	/**
	 * Determine if an object is observed, in a specific observation type, by an observer
	 * 
	 * The function will return true if the $observedId object (customer or user) being examined is 
	 * being observed in the method shown in $observationType by the user passed in $observerId.
	 * 
	 * Types come from the Observer model:
	 * public $types = array('Approval' => 'Approval',
	 * 						'Watch' => 'Watch',
	 * 						'Notify' => 'Notify');
	 * 
	 * @param string $observedId ID of the user being examined
	 * @param string $observationType type of observation
	 * @param string $observerId ID of the user examining, defaults to logged in user
	 * @return boolean
	 */
	public function isObserved($observedId, $observationType, $observerId = 'loggedInUser') {
		if ($observerId == 'loggedInUser') {
			$observerId = $this->Auth->user('id');
		}
		$directlyObserved = $this->User->UserObserver->find('all', array(
			'conditions' => array(
				'UserObserver.user_observer_id' => $observerId,
				'UserObserver.type' => $observationType
				),
			'fields' => array(
				'UserObserver.user_id',
				'UserObserver.user_name',
				'UserObserver.type'
			),
			'contain' => false
		));
		//setup conditions and options for getDescendents
		$conditions = array();
		$options = array(
			'fields' => array('id','name', 'active' ),
			'contain' => false
		);

		$indirectlyObserved = array();
		$allObserved = array();
		foreach ($directlyObserved as $index => $record) {
			$allObserved[] = $record['UserObserver']['user_id'];
			$indirectlyObserved = array_merge($indirectlyObserved, $this->User->getDecendents($record['UserObserver']['user_id'], false, $conditions, $options));
		}

		foreach ($indirectlyObserved as $index => $record) {
			$allObserved[] = $record['User']['id'];
		}
		
		$watched = array_intersect($observedId, $allObserved);
		if(!empty($watched)){
			return TRUE;
		}

		return FALSE;
	}

	//============================================================
	// COORDINATION OF USERS IN SHIFTING DATA ENVIRONMENT
	//============================================================

	/**
	 * Check if the requested node is free for editing
	 * 
	 * If it can be edited, lock it for this user.
	 * If not, the requestor will be able to view but not edit
	 * 
	 * @param Model $Model
	 * @param int $id The node that is being request for editing
	 * @return boolean True = found locked node, False = all clear for editing
	 */
	public function checkLock(Model $Model, $id) {
		$ancestors = $Model->getAncestors($id, false, array(
			$Model->alias . '.lock <> 0',
			$Model->alias . '.lock <> ' . $this->Auth->user('id')));
		$selfLock = $Model->find('first', array('conditions' => array(
				$Model->alias . '.lock <> 0',
				$Model->alias . '.lock <> ' . $this->Auth->user('id'),
				$Model->escapeField() => $id)));
		$descendents = $Model->getDecendents($id, false, array(
			$Model->alias . '.lock <> 0',
			$Model->alias . '.lock <> ' . $this->Auth->user('id')));
		if (!empty($ancestors) || !empty($selfLock) || !empty($descendents)) {
			//either an ancestor is locked, myself is locked or a descendent is locked
			$this->Session->setFlash('Records in this section are being edited by another user. You can only view at this time.');
			return true;
		}
		$this->setLock($Model, $id);
		return false;
	}

	/**
	 * Lock a node for editing and record details in Auth session
	 * 
	 * @param Model $Model
	 * @param int $id Id of the node to lock for editing
	 * @return boolean Did the lock succeed
	 */
	public function setLock(Model $Model, $id) {
		$Model->id = $id;
		$this->User->Behaviors->disable('ThinTree');
		if ($Model->saveField('lock', $this->Auth->user('id'))) {
			$this->Session->write('Auth.User.edit.mode', true);
			$this->Session->write('Auth.User.edit.model', $Model->alias);
			$this->Session->write('Auth.User.edit.id', $id);
			$this->User->Behaviors->enable('ThinTree');
			return true;
		}
		$this->User->Behaviors->enable('ThinTree');
		return false;
	}

	/**
	 * Determines when an edit lock needs to be cleared
	 * 
	 * @return boolean
	 * @throws BadRequestException
	 */
	public function clearLock() {
		$action = explode('_', $this->action);
		if (!$this->Auth->user('edit.mode')) {
			//fall through to true
		} elseif ($action[0] != 'edit' && $action[0] != 'add') {
			//clear lock and fall through to true
			if (!$this->clearEditLock()) {
				return false;
			}
		} elseif ($action[0] == 'add' || ($action[1] != 'user' && $action[1] != 'catalog')) {
			//fall through to true
		} elseif (!isset($this->request->params['pass'][0]) && !isset($this->request->params['pass'][1])) {
			//clear lock and fall through to true
			if (!$this->clearEditLock()) {
				return false;
			}
		} elseif (!$this->secureId($this->request->params['pass'][0], $this->request->params['pass'][1])) {
			//clear lock, fail out through Exception
			if (!$this->clearEditLock()) {
				return false;
			}
			throw new BadRequestException('Security hash failed. Unlocking and ending edit');
		} elseif ($this->request->params['pass'][0] != $this->Auth->user('edit.id')) {
			//clear lock, fall through to true
			if (!$this->clearEditLock()) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Clear the Edit lock on a node
	 * 
	 * @return boolean Did the clearEdit succeed
	 * @throws NotFoundException
	 */
	public function clearEditLock() {
		$Model = $this->Auth->user('edit.model');
		$id = $this->Auth->user('edit.id');
		if ($Model == 'User') {
			$lockedModel = $this->User;
		} elseif ($Model == 'Catalog') {
			$lockedModel = $this->Catalog;
		} else {
			throw new NotFoundException("Unexpected model locked: $Model");
		}
		$lockedModel->id = $id;
		$this->User->Behaviors->disable('ThinTree');
		if ($lockedModel->saveField('lock', 0)) {
			$this->clearEditMode();
			$this->User->Behaviors->enable('ThinTree');
			return true;
		}
		$this->Session->setFlash("The lock on Model $Model of id $id failed to clear. Contact admin NOW.");
		$this->User->Behaviors->enable('ThinTree');
		return false;
	}

	/**
	 * Set Auth session to show no editing is underway for this user
	 */
	public function clearEditMode() {
		$this->Session->write('Auth.User.edit.mode', false);
		$this->Session->write('Auth.User.edit.model', null);
		$this->Session->write('Auth.User.edit.id', null);
	}

	/**
	 * Write the user's node permission to the public registry
	 * 
	 * Editors need to know what logged in users they may be effecting
	 * This is the place they look to see what users need to 
	 * be forced to refresh session values to stay current with tree patterns
	 * 
	 * @param type $model
	 * @param type $roots
	 */
	protected function writePublicRegistry($model, $roots) {
		$id = $this->Auth->user('id');
		if ($id != '') {
			foreach ($roots as $index => $root) {
				$data[$index]['UserRegistry']['user_id'] = $id;
				$data[$index]['UserRegistry']['node_id'] = $root;
				$data[$index]['UserRegistry']['model'] = $model;
			}
			$this->UserRegistry->saveMany($data);
		}
	}

	/**
	 * Write this users node permissions where editors can keep an eye on them
	 * 
	 * Editors may effect logged in user's permitted nodes.
	 * The public registry gives a watch-point so users can be flagged
	 * when the need to refresh their session to get current permission settings
	 */
	protected function registerPublicUser() {
		if (!empty($this->userRoots)) {
			$userRoots = array_keys($this->userRoots);
			$this->writePublicRegistry('User', $userRoots);
		}
		if (!empty($this->catalogRoots)) {
			$catalogRoots = array_keys($this->catalogRoots);
			$this->writePublicRegistry('Catalog', $catalogRoots);
		}
	}

	/**
	 * Remove users node permissions from the public registry
	 * 
	 * When the user logs off we won't need to inform them
	 * of changes to their permitted nodes
	 */
	protected function unregisterPublicUser() {
		$id = $this->Auth->user('id');
		if ($id != '') {
			$this->UserRegistry->query("DELETE FROM `user_registries` WHERE `user_id` = $id");
		}
	}

	/**
	 * Mark the logged in user's record as 'touched'
	 * 
	 * Part of the time-out system. Record the last-touch timestamp
	 * for comparison with the next touch at a later point
	 */
	protected function flagUserRecord() {
		if ($this->Auth->user('id') != '') {
			$this->User->id = $this->Auth->user('id');
			$this->User->Behaviors->disable('ThinTree');
			$this->User->saveField('logged_in', time());
			$this->User->Behaviors->enable('ThinTree');
		}
	}

	/**
	 * Logging out a user means clearing their last-touch flag
	 */
	protected function releaseUserRecord() {
		if ($this->Auth->user('id') != '') {
			$this->User->id = $this->Auth->user('id');
			$this->User->Behaviors->disable('ThinTree');
			$this->User->saveField('logged_in', 0);
			$this->User->Behaviors->enable('ThinTree');
		}
	}

	//============================================================
	// USER PREFERENCES AND CONVENIENCE AIDS
	//============================================================

	/**
	 * 
	 */
	public function retrievePreferences() {
		$prefs = $this->Preference->find('first', array('conditions' => array('Preference.user_id' => $this->Auth->user('id'))));
		if ($prefs) {
			$this->Session->write('Prefs', unserialize($prefs['Preference']['prefs']));
			$this->Session->write('Prefs.id', $prefs['Preference']['id']);
		}
	}

	/**
	 * 
	 */
	public function savePreferences() {
		if ($this->Session->read('Prefs')) {
			$prefs['Preference']['prefs'] = serialize($this->Session->read('Prefs'));
			$prefs['Preference']['id'] = $this->Session->read('Prefs.id');
			$prefs['Preference']['user_id'] = $this->Auth->user('id');
			$this->Preference->save($prefs);

			// make sure even a brand new prefs record id gets into the session
			// this could be handled in afterSave of Preference
			$this->Session->write('Prefs.id', $this->Preference->id);
		}
	}

	/**
	 * Set the current page to be this user's default home page
	 * 
	 * @param type $controller
	 * @param type $action
	 */
	public function homePreference($controller, $action) {
		$this->layout = 'ajax';
		$this->Session->write('Prefs.home', array('controller' => $controller, 'action' => $action));
		$this->savePreferences();
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
		$a = ($this->request->data['address'] != '') ? ".{$this->request->data['address']}" : '.customer';
		$this->layout = 'ajax';
		$this->Session->write('Prefs.ship.' . $this->request->data['customer'] . $a, $this->request->data['shipment']);
		$this->savePreferences();
		$this->render('/Common/ajax');
	}

	public function searchFilterPreference($filter) {
//		$this->autoRender = false;
		$this->Session->write('Prefs.Search', $filter);
		$this->savePreferences();
	}
	/**
	 * Save the user's requested pagination limit
	 * 
	 * @param INT $limit, the requested limit
	 */
	public function paginationLimitPreference($limit) {
		$this->autoRender = false;
		$this->Session->write('Prefs.Catalog.paginationLimit', $limit);
		$this->savePreferences();
	}
	
	/**
	 * Returns the shipmentBillingOptions property
	 * 
	 * @return array
	 */
	public function getShipmentBillingOptions() {
		return $this->shipmentBillingOptions;
	}



	//============================================================
	// DEVELOPER UTILITIES
	//============================================================

    /**
     * Test emails to developer address with settable email config to use
     *
     * @param config
     * @return null
     */
    public function testEmail($config) {
            $Email = new CakeEmail();
            $Email->config($config)
                ->template('default', 'default')
                ->emailFormat('html')
                ->from(array('ampfg@ampprinting.com' => 'AMP FG System Robot'))
                ->to('jasont@ampprinting.com')
                ->subject("Test of email sending")
                ->send();
        return;
    }


    public function ddd($dbg, $title = false, $stack = false) {
		//set variables
		$ggr = Debugger::trace();
		$line = preg_split('/[\r*|\n*]/', $ggr);
		$togKey = sha1($line[1]);

		echo "<div class=\"cake-debug-output\">";
		if ($title) {
			echo "<h3 class=\"cake-debug\">$title</h3><p class=\"toggle\" id=\"$togKey\"><strong>$line[1]</strong></p>";
		}
		if ($stack) {
			echo "<pre class=\"$togKey hide\">$ggr</pre>";
		}
		echo"</div>";
		debug($dbg);
	}

	public function setJqueryVersion() {
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$browser = $_SERVER['HTTP_USER_AGENT'];
			if (stristr($browser, 'MSIE 10')) {
				$this->jquery = 'jquery-2.0.3.min';
			}
			$this->set(array('jquery' => $this->jquery));
		}		
	}

	/**
	 * Return the country code => country list
	 * 
	 * http://www.iso.org/iso/country_names_and_code_elements (ISO 3166)
	 * File saved in webroot/country_codes.txt
	 * '=' is field delimeter
	 * "\n" is line delimeter
	 * 
	 * @return array suitable for select list
	 */
	function getCountryList() {
		$file = new File(WWW_ROOT . 'address_codes/country_codes.txt');

		$countryList = $this->parseCountryStateFile($file);

		return $countryList;
	}

	/**
	 * ajax return a state/province select or text input
	 * 
	 * If a file is found for the country, make a
	 * select of the states/provinces otherwise
	 * just send back a text input for the user
	 * 
	 * @param string $country a two letter country code
	 * @return string a select or text input
	 */
	function getStateInput($country, $model) {
		$this->layout = 'ajax';

		$stateList = $this->getStateList($country);

		$this->set(compact('stateList', 'model'));
		$this->render('/Elements/get_state_list');
	}

	/**
	 * Returns a select list of states based upon the provided country code
	 * 
	 * @param string $country The country chosen
	 * @return array The State select list
	 */
	public function getStateList($country) {
		$file = new File(WWW_ROOT . "address_codes/{$country}_states.txt");
		$stateList = $this->parseCountryStateFile($file);
		return $stateList;
	}

	/**
	 * Parse the found country/state lists into select lists
	 * 
	 * Optionally, if no file is provided, return an empty string
	 * 
	 * @param string $file The file found in either the getCountry or getState list
	 * @return array The select list of states or countries, or an empty string, if no list
	 */
	private function parseCountryStateFile($file) {
		if ($file->exists()) {
			$content = explode("\n", $file->read());

			foreach ($content as $entry) {
				$entry = explode('=', $entry);
				$parsedList[$entry[0]] = $entry[1];
			}
		} else {
			$parsedList = '';
		}
		return $parsedList;
	}

	/**
	 * No address form works without these input select lists
	 * 
	 * tax_rate_id
	 * countryList
	 * stateList (defaults to US)
	 * 
	 * All vars set to the view
	 */
	public function setBasicAddressSelects($country = 'US') {
		$tax_rate_id = $this->Address->TaxRate->getTaxJurisdictionList();
		$countryList = $this->getCountryList();
		$stateList = $this->getStateList($country);
		$this->Customer = ClassRegistry::init('Customer');
		$customer_type = $this->Customer->customer_type;
		$this->set(compact('tax_rate_id', 'countryList', 'stateList', 'customer_type'));
	}

	//============================================================
	// OBSERVER AND NOTIFICATION STUFF
	//============================================================

	/**
	 * Perform a status-triggered notification process if necessary
	 * 
	 * Given an Order/Replenishment and its starting and stoping status
	 * Lookup the observing Users and perform a notification process (or not)
	 * 
	 * @param string $id Id of the Order/Replen
	 */
	public function sendObserverNotification($id) {
		$m = microtime(TRUE);
		// make a list of observation types that should get notified about this event
		// either the starting status or final status may trigger notification
		$notify = array();
		$this->Observer = ClassRegistry::init('Observer');
		debug($this->Observer->observationTriggers);
		foreach($this->Observer->observationTriggers as $type => $triggers) {
			if (in_array($this->currentStatus, $triggers) || in_array($this->startStatus, $triggers)) {
				$notify[$type] = true;
			}
		}

		if (empty($notify)) {
			return; // no notifications for this status
		}
		
		// if we need to do notifications given this status situation
		// find the users that might be observed
		// Orders have two users that may be subject to observation
		if ($this->alias === 'Order') {
				$userId = array(
						
					$this->userId => $this->userId,
					$this->customerUserId => $this->customerUserId
				);
		// rReplenishments have a single user that may be watched
		} elseif ($this->alias === 'Replenishment') {
			$recordData = $this->User->Replenishment->find('first', array(
				'conditions' => array(
					'Replenishment.id' => $id
				),
				'contain' => false
			));
			// set up the array for getAllObservers
			if ($recordData) {
				$userId = array(
					$recordData['Replenishment']['user_id'] => $recordData['Replenishment']['user_id']
				);
			}
		}
		// Now see if what observers there are for these users
		// and group them by the notification type that applies for for this status situation
		foreach($notify as $type => $boolean) {
			$this->getAllObservers($type, $userId);
		}
		if (empty($this->observers)) {
			return; // nobody is watching
		}
		
		// Now do the various notifications to each observer
		// $recordData still has the Order or Replenishment data
		foreach ($this->observers as $type => $observers) {
			switch ($type) {
				case 'Approval':
					$this->sendApprovalLinkEmails($observers);
					break;
				case 'Notify':
					$this->sendNotificationEmails($observers);
					break;
				case 'XML':
					$this->sendXmlCompletionData($observers);
					break;
				default:
					new NotImplementedException("The observation type $type is not implemented. Contact your administrator.");
					break;
			}
		}
		$this->ddd(microtime(TRUE) - $m, 'Status notification microseconds');
	}

	/**
 * Get all observers of a certain type interested in userId(s)
 * 
 * This Order/Replen/Whatever may be watched because:
 *		- the User that ordered it is directly watched
 *		- the Comapny it's ordered for is directly watched
 *		- Either is downstream of a watched user
 * Find all the Users who Observe the user(s) activities
	 * 
 * @param string $type The type of observation
 * @return array|string The user IDs ($list[id] => $id) or a single id
 */
	public function getAllObservers($type, $userId) {
//		debug(func_get_args());die;
		if (empty($userId)) {
			$this->observers = array();
		}
		if (!is_array($userId)) {
			$userList[$userId] = $userId;
		} else {
			$userList = $userId;
		}
		
		// get a big list of upstream user nodes starting from the vertices provided
		$inclusive = true;
		$ancestors = array();
		foreach ($userList as $id) {
			$ancestors = array_merge($ancestors, $this->User->getAncestorInList($id, $inclusive));
		}
		// are there Approval watches on any of these?
		$observers = $this->User->Observer->find('all', array(
			'conditions' => array(
				'Observer.user_id' => $ancestors,
				'Observer.type' => $type
			),
			'contain' => array(
				'ObservingUser' => array(
					'fields' => array(
						'username'
					)
				)
			)
		));
		foreach ($observers as $observer) {
			$this->observers[$type][$observer['Observer']['user_observer_id']] = array_merge($observer['Observer'], $observer['ObservingUser']);
		}
	}

	/**
	 * Send Email with an approval for this order to a set of Approving user
	 * 
	 * @param array $observers Observer/Observed data
	 * @param array $recordData The Order record (no deeper data)
	 * @return null
	 */
	public function sendApprovalLinkEmails($observers) {
		
		$approverCount = count($observers);
		// can this order actually be approved?
		if(!$this->submitEvaluated){
			$this->submitEvaluation();
		}
		
		// make a gateway record template to capture incoming link clicks
		$this->request->data['Gateway'] = array(
			'model_alias' => $this->alias,
			'model_id' => $this->order['id'],
			'complete' => 0,
			'controller' => 'orders',
			'action' => 'statusChange'
		);
		
		// send all the emails
		foreach ($observers as $observer) {
		$this->request->data['Gateway']['user_id'] = $observer['user_observer_id'];
		$this->Gateway->create();
		$this->Gateway->save($this->request->data);
			$Email = new CakeEmail();
			$Email->config('smtp')
					->template('approval_link_email', 'default')
					->viewVars(array(
						'recordData' => $this->order, 
						'approverCount' => $approverCount,
						'overbudget' => $this->overbudget,
						'inStock' => $this->inStock,
						'gateway_id' => $this->Gateway->id
					))
					->emailFormat('html')
					->from(array('ampfg@ampprinting.com' => 'AMP FG System Robot'))
					->to($observer['username'])
					->subject("Order {$this->order['order_number']} is ready for approval.")
					->send();
		}
		return;
	}
	
	/**
	 * Send notification email for this order to a set of users
	 * 
	 * Right now only Orders trigger notifications. Some day
	 * Replenishments might also. We'd have to expand code to
	 * handle it, but it should be possible without much trouble
	 * 
	 * @param array $observers Observer/Observed data
	 * @return null
	 */
	public function sendNotificationEmails($observers) {
		foreach ($observers as $observer) {
			$Email = new CakeEmail();
			$Email->config('smtp')
					->template('notify_status_change', 'default')
					->viewVars(array(
						'recordData' => $this->order,
						'user' => $this->user,
						'customer' => $this->customer,
						'loggedUser' => $this->Auth->user(),
						'status' => $this->currentStatus
					))
					->emailFormat('html')
					->from(array('ampfg@ampprinting.com' => 'AMP FG System Robot'))
					->to($observer['username'])
					->subject("Order {$this->order['order_number']} status change: {$this->currentStatus}")
					->send();
		}
		return;
	}
	
	/**
	 * Perform an XML transaction for this order to a set of users
	 * 
	 * Right now only Replenishments trigger XML. Some day
	 * Orders might also. We'd have to expand code to
	 * handle it, but it should be possible without much trouble
	 * 
	 * @param array $observers Observer/Observed data
	 * @param array $recordData The Replenishment record (no deeper data, may someday be a Order too)
	 * @return null
	 */
	public function sendXmlCompletionData($observers, $recordData) {
		return;
	}

	/**
	 * See if order must stop at submitted or can go to a later status
	 * 
	 * @param string $id The order id
	 * @return string|boolean true to go to next test, false must wait for approval (stop at submitted)
	 */
	public function submitEvaluation() {
		$this->submitEvaluated = TRUE;
		$userAncestors = $this->allPossibleWatchPoints($this->orderId);

		// are there Approval watches on any of these?
		if(!$this->approvers){
			$this->approvers = $this->Order->User->Observer->find('all', array(
				'conditions' => array(
					'Observer.user_id' => $userAncestors,
					'Observer.type' => 'Approval'
				)
			));
		}
		//is the user over budget?
		$this->overbudget = FALSE;
		if (isset($this->budget['Budget'])) {
			if ($this->budget['Budget']['remaining_budget'] > 0 && $this->budget['Budget']['remaining_item_budget'] > 0) {
				$this->overbudget = FALSE;
			} else {
				$this->overbudget = TRUE;
			}
		}
		
		// are any items out of stock
		// or does the user honor item count limits and any limt is exceeded
		// First pull the array to check
		$this->inStockCheck();

		// if Approvers exist or overbudget or any out-of-stock, Submitted
		if (!empty($this->approvers) || $this->overbudget || !$this->inStock === TRUE) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/**
	 * Determine if any items are out of stock or if there is an item limit budget overage
	 * 
	 * Needs setOrderStatusChangeProperties() run first
	 * Leaves on the first out-of-stock item or budget overage
	 * so there is no comprehensive reporting and one or the other
	 * condition may not be detected at all
	 * 
	 * @param boolean $stock Check for out-of-stock items
	 * @param boolean $limit Check for item limit budget overages
	 * @return property True = all good or string telling which problem was found first
	 */
	public function inStockCheck($stock = true, $limit = true) {
		// are any items out of stock
		// or does the user honor item count limits and any limt is exceeded
		// First pull the array to check
		$this->inStock = TRUE;
		$limitBudget = $this->user['use_item_limit_budget'];
		$i = 0;
		while ($this->inStock === TRUE && $i < count($this->items)) {
			$lineItem = $this->items[$i];

			// out of stock check
			if ($stock && $lineItem['Item']['available_qty'] < 0) {
				$this->inStock = 'At least one item is out of stock';

			// item limit check
			} elseif ($limit && $lineItem['Catalog']['max_quantity'] > 0 && $limitBudget && $lineItem['quantity'] > $lineItem['Catalog']['max_quantity']) {
				$this->inStock = 'At least one item is over the item limit';
			}
			$i++;
		}
	}
	
	public function testAccess() {
		debug($this->displayField);
		debug($this->userprop);
		debug(Configure::read('debug'));
		$this->logVars('blah');
	}
	
	public function xmlError($message) {
		$return = "\r\n";
		$return .= sprintf('<?xml version="1.0" encoding="UTF-8"?><error>%s</error>', $message);
		$return .= "\r\n\n";
		return $return;
	}

	public function installComponent($name) {
		$this->$name = $this->Components->load($name);
		$this->$name->initialize($this);
	}
	
	/**
	 * Generate cyrptographically random UUIDs
	 * 
	 * @return UUID
	 */
	public function guidv4(){
		$data = openssl_random_pseudo_bytes(16);

		$data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}
	
	/**
	 * The common entry point for all cron jobs, passing in any args
	 * 
	 * This method requires that args arrive as string. It could be a serialize, urlencoded string
	 * which would require un-serialization and un-urlencoding in the target method
	 * 
	 * @param string $method
	 * @param string $args
	 */
	public function cron($method, $args = NULL) {
		$this->$method($args);
        /**
         * @todo Add logging to cron call.
         */
		exit;
	}
}

	//============================================================
	// APPLICATION EXCEPTIONS
	//============================================================

	/**
	 * A class to handle critical failures during data-save processes
	 * 
	 * These are multi-step saves that would be very hard to correct
	 * So as much info as possible is logged in a file for examination
     * @todo Add logging
	 */
	class FailedSaveException extends CakeException {
		protected $_messageTemplate = "The %s failed. Everything has been logged for review.<br />\n\r";
		
		public function __construct($message, $code = 500) {
			parent::__construct($message, $code);
		}
	}