<?php

App::uses('AppController', 'Controller');

/**
 * Addresses Controller
 *
 * @property Address $Address
 */
class AddressesController extends AppController {

	
	function beforeFilter() {
		parent::beforeFilter();
		//establish access patterns
		$this->accessPattern['Manager'] = array ('all');
		$this->accessPattern['Buyer'] = array('addressAdd', 'addressEdit', 'addressDelete', 'getAddress');
		$this->accessPattern['Guest'] = array('addressAdd', 'addressEdit', 'addressDelete');
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
        $this->Address->recursive = 0;
        $this->set('addresses', $this->paginate());
    }

    /**
     * view method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function view($id = null) {
        if (!$this->Address->exists($id)) {
            throw new NotFoundException(__('Invalid address'));
        }
        $options = array('conditions' => array('Address.' . $this->Address->primaryKey => $id));
        $this->set('address', $this->Address->find('first', $options));
    }

    /**
     * add method
     *
     * @return void
     */
    public function add() {
        if ($this->request->is('post')) {
            $this->Address->create();
            if ($this->Address->save($this->request->data)) {
                $this->Flash->success(__('The address has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Flash->error(__('The address could not be saved. Please, try again.'));
            }
        }
        $users = $this->Address->User->find('list');
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
        if (!$this->Address->exists($id)) {
            throw new NotFoundException(__('Invalid address'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Address->save($this->request->data)) {
                $this->Flash->success(__('The address has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Flash->error(__('The address could not be saved. Please, try again.'));
            }
        } else {
            $options = array('conditions' => array('Address.' . $this->Address->primaryKey => $id));
            $this->request->data = $this->Address->find('first', $options);
        }
        $users = $this->Address->User->find('list');
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
        $this->Address->id = $id;
        if (!$this->Address->exists()) {
            throw new NotFoundException(__('Invalid Address'));
        }
        $this->request->allowMethod(['post', 'delete']);
        if ($this->Address->delete()) {
//            $this->Flash->set(__('Address deleted'));
			$result = true;
        } else {
//			$this->Flash->set(__('Address was not deleted'));
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

    public function manageVendors() {
        $this->layout = 'sidebar';
        $pageHeading = $title_for_layout = 'Manage Vendors';
        $this->set(compact('pageHeading', 'title_for_layout'));
        $this->set('vendorGrain', $this->Address->getVendors());
        $this->render('/Common/manage_tree_object');
//	debug($this->viewVars['editGrain']);
    }

    /**
     * GRAIN AJAX: address/vendor new/edit 
     * 
     * INCOMPLETE
     * 
     * @param type $id
     */
    public function addressEdit($id = NULL) {
        $this->layout = 'ajax';
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Address->save($this->request->data)) {
                $this->Flash->success('This address saved.');
            } else {
                $this->Flash->error('The address did not save. Please try again.');
            }
            $this->redirect($this->referer());
        }
        if (!empty($id)) {
            $id = str_replace('address', '', $id);
            $this->request->data = $this->Address->getAddress($id);
        }
        $tax_rate_id = $this->Address->TaxRate->getTaxJurisdictionList();
		$this->Customer = $this->User->Customer;
		$this->setBasicAddressSelects($this->request->data['Address']['country']);
		$this->render('/Elements/address_form');
    }

    public function addressAdd($id = NULL) {
        $this->layout = 'ajax';
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Address->save($this->request->data)) {
                $this->Flash->success('This address saved.');
            } else {
                $this->Flash->error('The address did not save. Please try again.');
            }
            $this->redirect($this->referer());
        }
        $this->request->data['Address']['user_id'] = $id;
        $this->request->data['Address']['type'] = 'shipping';
		$this->setBasicAddressSelects();
        $this->render('/Elements/address_form');
    }

    public function addressDelete($id = NULL) {
        debug('Hey, deleting a record');
        $this->delete($id);
    }

    public function vendorEdit($id = NULL) {
        $this->layout = 'ajax';
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Address->save($this->request->data)) {
                $this->Flash->success('This vendor saved.');
            } else {
                $this->Flash->error('The vendor did not save. Please try again.');
            }
            $this->redirect($this->referer());
        }
        if (!empty($id)) {
            $id = str_replace('address', '', $id);
            $this->request->data = $this->Address->getAddress($id);
        }
		$this->setBasicAddressSelects($this->request->data['Address']['country']);
        $this->render('/Elements/address_vendor_form');
    }

    public function vendorAdd($id = NULL) {
        $this->layout = 'ajax';
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Address->save($this->request->data)) {
                $this->Flash->success('This vendor saved.');
            } else {
                $this->Flash->error('The vendor did not save. Please try again.');
            }
            $this->redirect($this->referer());
        }
        $this->request->data['Address']['user_id'] = $id;
        $this->request->data['Address']['type'] = 'vendor';
		$this->setBasicAddressSelects();
        $this->render('/Elements/address_vendor_form');
    }

    public function vendorDelete($id = NULL) {
        debug('Hey, deleting a record');
        $this->delete($id);
    }

	// ERROR (a bit of a hack fix. disabled the 'probably' meaningless call) **
	/**
	 * 2014-08-29 12:19:53 Warning: Warning (2): Missing argument 2 for AddressesController::getAddress() in [/var/www/html/ampfg/app/Controller/AddressesController.php, line 220]
Trace:
AddressesController::getAddress() - APP/Controller/AddressesController.php, line 220
ReflectionMethod::invokeArgs() - [internal], line ??
Controller::invokeAction() - CORE/Cake/Controller/Controller.php, line 486
Dispatcher::_invoke() - CORE/Cake/Routing/Dispatcher.php, line 187
Dispatcher::dispatch() - CORE/Cake/Routing/Dispatcher.php, line 162
[main] - APP/webroot/index.php, line 111

this is called from ReplenishmentsController->createReplenishments() the line was
	 * $defaultShipping = $this->Address->getAddress($addressId);
	 * I'm not sure what the structure would be for a shipping preference and what a valid $customer would be
	 * I rem'd out the call which was meant to set a default shipping address if one existed

*/
	public function getAddress($id, $customer = null) {
		$prefs = array();
		if ($customer != null) {
			$prefs = array('Shipment' => $this->Session->read("Prefs.ship.$customer.$id"));
		}		
        $address = $this->Address->getAddress($id);
        $this->layout = 'ajax';
        $this->set(compact('address', 'prefs'));
    }
	
	public function testMe(){
        $this->Flash->success('success');
        $this->Flash->error('error');
        $this->Auth->flash('Auth message');
        $this->Flash->set('normal message');
        $this->Flash->success('new success');
        $this->Flash->error('new error');
		$this->ddd($this->accessPattern, 'Access Pattern');
	}
}
