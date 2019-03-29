<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
App::uses('UsersController', 'Controller');

/**
 * Description of ClientController
 *
 * @author dondrake
 */
class ClientsController extends UsersController {

    public $MenuCondition = null;

    public $uses = array('Replenishment');

    public function beforeFilter() {
        parent::beforeFilter();
		//establish access patterns
		$this->accessPattern['Manager'] = array ('all');
		$this->accessPattern['Buyer'] = array('status', 'manageShippingAddress');
		$this->accessPattern['Guest'] = array('status');
    }

//    public function beforeRender() {
//        $this->MenuCondition = $this->makeMenuConditions();
//	debug($this->MenuCondition);
//        parent::beforeRender($this->MenuCondition);
//    }
//    private function makeMenuConditions() {
//        $group = $this->Session->read('Auth.User.group');
//        $access = $this->Session->read('Auth.User.access');
//        return $this->menuQueryCondition($group, $access);
//    }

    public function isAuthorized($user) {
        return $this->authCheck($user, $this->accessPattern);
    }

    /**
     * Status page for a user
     */
    public function status() {
        $pageHeading = $title_for_layout = 'Status Page';
        $this->set(compact('title_for_layout', 'pageHeading'));

		// make the list of users/customers I can approve for
		$approvable = $this->User->getObservationList('Approval');
		
        $in = $this->User->getAccessibleUserInList();
        // we now have the IN list
		$this->User->Order->access = $this->Auth->user('access');
        $myOrders = $this->User->Order->fetchOrders($this->Auth->user('id'));
        $this->User->Order->fetchOrders($in, true, true);
        $connectedOrders = $this->User->Order->unWatchedOrders;
        $watchedOrders = $this->User->Order->watchedOrders;
        $approvedOrders = $this->User->Order->approvedOrders;
        if($this->Session->read('Auth.User.group') == 'Clients'){
            $replenishments = array();
        } else {
            $replenishments = $this->Replenishment->fetchReplenishmentsForStatus();
        }
        $this->set(compact(
			'myOrders',
			'connectedOrders',
			'watchedOrders',
			'approvedOrders',
			'replenishments',
			'approvable'));
		$this->installComponent('Budget');
		$this->Budget->updateRemainingBudget();
//        $this->requestAction(array('controller' => 'budgets', 'action' => 'updateRemainingBudget'));
        $this->render('/Clients/status');
    }

    private function getTrack() {
        $this->Users->Orders->Shipments->getTrackingGrain();
    }

    public function replenishment() {
        $this->render('/Clients/replenishment');
    }

    public function order() {
//	$this->render('/Clients/order');
        $this->redirect(array('controller' => 'orders', 'action' => 'index'));
    }

    public function manageShippingAddress($user) {
        
    }

    public function manageEyesonList($user) {
        
    }

    /**
     * Assign catalogs to specific users
     * @param type $user
     */
    public function assignCatalog($user) {
        
    }
   
}

?>
