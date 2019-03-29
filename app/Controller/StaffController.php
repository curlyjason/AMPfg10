<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
App::uses('ClientsController', 'Controller');

/**
 * Description of ClientController
 *
 * @author jasontempestini
 */
class StaffController extends ClientsController {

    public $MenuCondition = null;

    public function beforeFilter() {
        parent::beforeFilter();
        $this->MenuCondition = $this->makeMenuConditions();
		$this->accessPattern['Manager'] = array ('all');
		$this->accessPattern['Buyer'] = array ('status', 'manageShippingAddress');
		$this->accessPattern['Guest'] = array ('status');
		$this->accessPattern['Warehouses'] = array ('status');
    }
    
    public function isAuthorized($user) {
        return $this->authCheck($user, $this->accessPattern);
    }

    private function makeMenuConditions() {
        
    }

    /**
     * Assign client to be managed or viewed by specific staff members
     * @param type $user
     */
    public function assignClient($user) {
        
    }
    

}

?>
