<?php

App::uses('AppController', 'Controller');
App::uses('IdHashTrait', 'Lib/Trait');
App::uses('ClassRegistry', 'Utility');


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
	
	public $components = ['Flash', 'Prefs'];
	
	public $helpers = ['Flash'];

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
        $this->customerAddAddressValidationSetup();
        if ($this->putOrPost()) {
            $this->Customer->create();

            $this->setupNewCustomerVendor();
            $this->setupNewCustomerToken();

            if ($this->Customer->saveAll($this->request->data)) {
				
				//=============get created id's
				$custId = $this->Customer->id;
				$custUserId = $this->Customer->field('user_id', array(
                    'Customer.id' => $this->Customer->id
                ));

                $this->setCustomerOwnership();
                $this->createCatalogRoot($custId, $custUserId);

                //=============refresh permissions, if necessary
                if (!$this->rootOwner) {
                    $this->setNodeAccess($this->Auth->user('id'));
                }

				$this->Flash->success(__('The customer has been saved'));
                $this->redirect(array(
                    'controller' => 'users',
                    'action' => 'edit_userGrain/',
                    $custUserId,
                    $this->secureHash($custUserId)));
            } else {
                $this->Flash->error(__('The customer could not be saved. Please, try again.'));
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
     * Setup a vendor node on TRD for a new customer
     */
    private function setupNewCustomerVendor()
    {
        $this->request->data['Vendor'] = $this->request->data['Address'];
        $this->request->data['Vendor']['type'] = 'vendor';
    }

    /**
     * Setup the security token for a new customer
     */
    private function setupNewCustomerToken()
    {
        $s = $this->request->data['User']['username'] . time();
        $this->request->data['Customer']['token'] = $this->secureHash($s);
    }

    /**
     * Setup validation rules for adding customers
     *
     * Needed to ensure Billing Address is setup correctly
     */
    private function customerAddAddressValidationSetup()
    {
        $this->Address->validator()
            ->add('address', 'required', array(
                'rule' => 'notBlank',
                'message' => 'A billing address is required'
            ))
            ->add('city', 'required', array(
                'rule' => 'notBlank',
                'message' => 'A billing address is required'
            ))
            ->add('state', 'required', array(
                'rule' => 'notBlank',
                'message' => 'A billing address is required'
            ))
            ->add('zip', 'required', array(
                'rule' => 'notBlank',
                'message' => 'A billing address is required'
            ))
            ->add('email', 'required', array(
                'rule' => 'notBlank',
                'message' => 'A contact email is required'
            ))
            ->add('phone', 'required', array(
                'rule' => 'notBlank',
                'message' => 'A contact phone number is required'
            ));
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
        if ($this->putOrPost() && $this->validateRequestData('User.id')->isValid()) {
			if ($this->saveCustomerEdits() && $this->saveBrandingEdits()) {
				$this->Flash->success(__('The customer has been saved'));
                $this->redirect($this->referer());
            } else {
                $this->Flash->error(
						__('The customer could not be saved. Please, try again.'));
                $this->redirect($this->referer());
            }
        } else {
            $this->prepareEditData($id);
			$this->setBasicAddressSelects($this->request->data('Address.country'));
        }
        $tax_rate_id = $this->Customer->Address->TaxRate->getTaxJurisdictionList();
		$this->set('customer_type', $this->Customer->customer_type);
        $this->set(compact('tax_rate_id'));
    }
	
	/**
	 * Prepare trd to populate the customer-edit form
	 * 
	 * @param string $id
	 * @return void
	 */
	private function prepareEditData($id)
	{
		$options = ['conditions' => ['Customer.id' => $id]];
		$this->request->data = $this->Customer->find('first', $options);
		$this->request->data(
				'Preference.branding', 
				$this->Prefs->retreiveBrandingData(
						$this->request->data('Customer.user_id')
					)
			);
		$this->secureRequestData('User.id');
		$this->secureRequestData('User.role');
		$this->secureRequestData('User.parent_id');
		return;
	}
	
	/**
	 * Adjust customer edit data and attempt to save the changes
	 * 
	 * @return boolean
	 */
	private function saveCustomerEdits()
	{
		$this->request->data(
				'Address.name', 
				$this->request->data('User.username')
			);
		$this->request->data(
				'Address.company', 
				$this->request->data('User.username')
			);
		if ($this->request->data('Logo.img_file.name') !== null) {
			unset($this->request->data['Logo']);
		}
		return $this->Customer->saveAll($this->request->data);
	}
	
	/**
	 * Adjust the edited customer branding data and attempt to save
	 * 
	 * @return boolean
	 */
	private function saveBrandingEdits() {
		$logo_file = $this->request->data('Logo.img_file.name') !== null
				? $this->request->data('Logo.img_file.name') 
				: $this->request->data('Logo.img_file');
		
		$this->request->data('Preference.branding.logo_file', $logo_file);
		
		return $this->Prefs->saveBrandingData(
				$this->request->data('Preference.branding')
			);
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
            $this->Flash->set(__('Customer deleted'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Flash->set(__('Customer was not deleted'));
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
                $this->Flash->set('The permissions were not saved');
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
            $this->Flash->error('The catalog was not saved');
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
                $this->Flash->error('The catalog permissions were not saved');
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
            $this->Flash->error('The token did not save');
            $result = false;
        } else {
            //If the save happens, return true
            $this->Flash->success('The token was updated');
            $result = true;
        }
        $this->set('result', $result);
        $this->render('reset_customer_token');
    }

	public function testMe()
	{
	    $data = array(
            'Customer' => array(
                'customer_code' => '12354',
                'customer_type' => 'AMP',
                'id' => '',
                'allow_backorder' => '1',
                'allow_direct_pay' => '0',
                'release_hold' => '0'
            ),
            'User' => array(
                'username' => 'Super Customer',
                'id' => '',
                'folder' => '1',
                'role' => 'Clients Guest/196abfc5b8e57c1403f3d855dd3ce1072f565966',
                'parent_id' => '1/b7a815afce7add8d4c22aadb83638e301b0d396c'
            ),
            'Address' => array(
                'id' => '',
                'type' => 'shipping',
                'address' => '123 Main Street',
                'address2' => 'Ste 204',
                'city' => 'San Francisco',
                'state' => 'CA',
                'zip' => '939393',
                'country' => 'US',
                'first_name' => 'first',
                'last_name' => 'last',
                'email' => 'email@email.com',
                'phone' => '9255569000',
                'fedex_acct' => 'FedEx',
                'ups_acct' => 'UPS'
            ),
            'Preference' => array(
                'branding' => array(
                    'company' => 'Super Customer',
                    'address1' => '123 Main Street',
                    'address2' => '',
                    'address3' => 'San Francisco, CA 939393',
                    'customer_user_id' => '',
                    'logo_file' => ''
                )
            ),
            'Logo' => array(
                'img_file' => array(
                    'name' => '',
                    'type' => '',
                    'tmp_name' => '',
                    'error' => (int) 4,
                    'size' => (int) 0
                )
            )
        );
	    $this->Customer->SaveAll($data);
	    debug($this->Customer->validationErrors);die;
		$this->Flash->error('message 1');
		$this->Flash->error('message 2');
		$this->Flash->success('message 3');
	}
}
