<?php

App::uses('AppController', 'Controller');
App::uses('IdHashTrait', 'Lib/Trait');

/**
 * Customers Controller
 *
 * @property Customer $Customer
 */
class CustomersController extends AppController {
	
	use IdHashTrait;

    public $defaultUser = array(
        'User' => array(
            'role' => 'Clients Guest',
            'folder' => 1,
            'parent_id' => ''
    ));
	
    public $defaultCustomer = array();
	
	public $components = ['Flash'];

	public function beforeFilter() {
        parent::beforeFilter();
		//establish access patterns
		$this->accessPattern['Manager'] = array ('all');
		$this->accessPattern['StaffManager'] = array('all');
		$this->accessPattern['StaffBuyer'] = array('index');
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
        $this->Customer->recursive = 0;
        $this->set('customers', $this->paginate());
    }

    /**
     * view method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function view($id = null) {
        if (!$this->Customer->exists($id)) {
            throw new NotFoundException(__('Invalid customer'));
        }
        $options = array('conditions' => array('Customer.' . $this->Customer->primaryKey => $id));
        $this->set('customer', $this->Customer->find('first', $options));
    }

    /**
     * add method
     *
     * @return void
     */
    public function add() {
        $this->layout = 'ajax';
		$this->Address->validator()
			->add('address', 'required', array(
				'rule' => 'notEmpty',
				'message' => 'A billing address is required'
			))
			->add('city', 'required', array(
				'rule' => 'notEmpty',
				'message' => 'A billing address is required'
			))
			->add('state', 'required', array(
				'rule' => 'notEmpty',
				'message' => 'A billing address is required'
			))
			->add('zip', 'required', array(
				'rule' => 'notEmpty',
				'message' => 'A billing address is required'
			))
			->add('email', 'required', array(
				'rule' => 'notEmpty',
				'message' => 'A contact email is required'
			))
			->add('phone', 'required', array(
				'rule' => 'notEmpty',
				'message' => 'A contact phone number is required'
			));
        if ($this->request->is('post')) {
            $this->Customer->create();
            $this->request->data['Address']['name'] = $this->request->data['User']['username'];
            $this->request->data['Address']['company'] = $this->request->data['User']['username'];
			$s = $this->request->data['User']['username'] . time();
			$this->request->data['Customer']['token'] = $this->secureHash($s);
			$address['Address'] = $this->request->data['Address'];
			unset($this->request->data['Address']);
            if ($this->Customer->saveAll($this->request->data)) {
				
				//=============get created id's
				$custId = $this->Customer->id;
				$custUserId = $this->Customer->field('user_id', array(
                    'Customer.id' => $this->Customer->id
                ));

                //=============attach address to user
				
                $address['Address']['user_id'] = $this->Customer->User->id;
                if (!$this->Customer->Address->save($address)) {
                    $this->Session->setFlash(__('the address failed to update'), 'flash_error');
                }
				$addressId = $this->Customer->Address->id;
				$addressSave = ($this->Customer->saveField('address_id', $addressId));
				
                //=============make the new customer a vendor also so they can resupply thier own inventory
                $vendor = $address;
				$vendor['Address']['type'] = 'vendor';
				$vendor['Address']['user_id'] = NULL;
				$vendor['Address']['customer_id'] = $custId;
                if (!$this->Customer->Address->save($vendor)) {
                    $this->Session->setFlash(__('the address for vendor failed to update'), 'flash_error');
                }

                //=============setup ownership in users:users for the creating user
                $this->setCustomerOwnership();

                //=============create a catalog root connected to the customer
                $this->createCatalogRoot($custId, $custUserId);

                //=============refresh permissions, if necessary
                if (!$this->rootOwner) {
                    $this->setNodeAccess($this->Auth->user('id'));
                }

				$this->Session->setFlash(__('The customer has been saved'), 'flash_success');
                $this->redirect(array(
                    'controller' => 'users',
                    'action' => 'edit_userGrain/',
                    $custUserId,
                    $this->secureHash($custUserId)));
            } else {
                $this->Session->setFlash(__('The customer could not be saved. Please, try again.'), 'flash_error');
                $this->redirect($this->referer());
            }
        } else {
            $this->defaultUser['User']['role'] = $this->secureSelect($this->defaultUser['User']['role']);
            $this->defaultUser['User']['parent_id'] = $this->secureSelect($this->Customer->User->ultimateRoot);
			$this->setBasicAddressSelects();
            $this->request->data = array_merge($this->defaultCustomer, $this->defaultUser);
        }
    }

    /**
     * edit method
	 * 
	 * @todo The id, role, and parent_id are all hashed. role and 
	 *		parent_id have validators that check and remove the hashing 
	 *		but id does not. Since the save and validators are in the 
	 *		path of all other system code, I did a local check/fix of 
	 *		the id value as part of the conditional save decision
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function edit_customer($id = null) {
        $this->layout = 'ajax';
        if (!$this->Customer->exists($id)) {
            throw new NotFoundException(__('Invalid customer'));
        }
        if (
				($this->request->is('post') || $this->request->is('put'))
				&& $this->validateRequestData('User.id')->isValid()
			) 
		{
			$this->request->data(
					'Address.name', 
					$this->request->data('User.username')
				);
			$this->request->data(
					'Address.company', 
					$this->request->data('User.username')
				);
            if ($this->Customer->saveAll($this->request->data)) 
			{
				$this->Flash->success(__('The customer has been saved'));
                $this->redirect($this->referer());
            } 
			else 
			{
                $this->Flash->error(
						__('The customer could not be saved. Please, try again.'));
                $this->redirect($this->referer());
            }
        } else {
            $options = ['conditions' => ['Customer.id' => $id]];
            $this->request->data = $this->Customer->find('first', $options);
            $this->secureRequestData('User.id');
            $this->secureRequestData('User.role');
            $this->secureRequestData('User.parent_id');
			$this->setBasicAddressSelects($this->request->data('Address.country'));
        }
        $tax_rate_id = $this->Customer->Address->TaxRate->getTaxJurisdictionList();
		$this->set('customer_type', $this->Customer->customer_type);
        $this->set(compact('tax_rate_id'));
    }

    /**
     * delete method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function delete($id = null) {
        $this->Customer->id = $id;
        if (!$this->Customer->exists()) {
            throw new NotFoundException(__('Invalid customer'));
        }
        $this->request->onlyAllow('post', 'delete');
        if ($this->Customer->delete()) {
            $this->Session->setFlash(__('Customer deleted'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('Customer was not deleted'));
        $this->redirect(array('action' => 'index'));
    }

    /**
     * Assign a newly created customer's user to creator
     * 
     * This function is a replacement for afterSave in user not doing this on it's own
     * Not sure why this is not happening there, but it doesn't work
     * 
     * @param string $id - this is the id of the newly created user
     */
    public function setCustomerOwnership() {
        if (!$this->Customer->User->rootOwner) {
            $data = array(
                'UserManager' => array('id' => $this->Auth->user('id')
                )
            );
            if (!$this->Customer->User->save($data, false)) {
                $this->Session->setFlash('The permissions were not saved');
            }
        }
    }

    /**
     * Creates a folder-type catalog named for the customer
     * 
     */
    public function createCatalogRoot($custId, $custUserId) {
        $data = array(
            'Catalog' => array(
                'type' => FOLDER,
                'active' => '1',
                'name' => $this->request->data['User']['username'],
                'parent_id' => '1', // the save uses false validation so hashing this will not work
				'customer_id' => $custId,
				'customer_user_id' => $custUserId
        ));
        $this->Catalog->create();
        if (!$this->Catalog->save($data, array(
                    'validate' => false,
                    'fieldlist' => array('id', 'type', 'active', 'name', 'parent_id', 'customer_id', 'customer_user_id'),
                    'callbacks' => 'before'
                ))) {
            $this->Session->setFlash('The catalog was not saved', 'flash_error');
            return false;
        }
        //After save in catalog again doesn't work
        //This sets the users catalog permissions manually
        if (!$this->User->rootOwner) {
            $permission = array(
                'User' => array('id' => $this->Auth->user('id')
                )
            );
            if (!$this->User->Catalog->save($permission, false)) {
                $this->Session->setFlash('The catalog permissions were not saved', 'flash_error');
            }
        }
    }
    
    public function customersEdit($id = NULL) {
        if (!$this->Customer->exists($id)) {
            throw new NotFoundException(__('Invalid customer'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
	    $this->Customer->save($this->request->data);
	}
	$this->Customer->find('first', array('conditions' => array('user_id' => $id)));
	$this->layout = 'ajax';
	$this->render('customer_form');
    }
	
	public function fetchCustomerUserId($token) {
		return $this->Customer->field('user_id', array('token' => $token));
	}

    /**
     * function to reset the customer token on user request
     *
     * Security of this function in managed by user's access to the page where this button exists.
     *
     * @param $id the customer ID
     * @return bool
     */
	public function resetCustomerToken(){
	    $this->layout = 'ajax';
	    //create a new hash for the token
        $s = $this->Auth->user('name') . time();
        $this->request->data['Customer']['token'] = $this->secureHash($s);

        //If the save fails, set flash message and return false
        If(!$this->Customer->save($this->request->data,array(
            'validate' => false,
            'fieldlist' => ['id', 'token']
        ))) {
            $this->Session->setFlash('The token did not save', 'flash_error');
            $result = false;
        } else {
            //If the save happens, return true
            $this->Session->setFlash('The token was updated', 'flash_success');
            $result = true;
        }
        $this->set('result', $result);
        $this->render('reset_customer_token');
    }

}
