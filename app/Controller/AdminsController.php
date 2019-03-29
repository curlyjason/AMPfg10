<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
App::uses('StaffController', 'Controller');

/**
 * Description of ClientController
 *
 * @author jasontempestini
 */
class AdminsController extends StaffController {

    public $MenuCondition = null;
    
    public function beforeFilter() {
        parent::beforeFilter();
        $this->MenuCondition = $this->makeMenuConditions();
		//establish access patterns
		$this->accessPattern['Manager'] = array ('all');
		$this->accessPattern['Buyer'] = array('index','shop','listOrders');
		$this->accessPattern['Guest'] = array('listOrders');
    }
    
    public function isAuthorized($user){
	return $this->authCheck($user, $this->accessPattern);
    }


    private function makeMenuConditions() {
        
    }

}

?>
