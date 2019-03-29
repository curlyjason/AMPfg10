<?php

App::uses('ClientsController', 'Controller');

/**
 * Warehouse Controller
 *
 * @property User $User
 */
class WarehousesController extends ClientsController {
    
    public function beforeFilter() {
        parent::beforeFilter();
		$this->accessPattern['StaffManager'] = array ('all');
		$this->accessPattern['Warehouses'] = array ('all');
    }
    
    public function isAuthorized($user) {
        return $this->authCheck($user, $this->accessPattern);
    }
     /**
     * Pull List for Warehouse user
     */
	public function status() {
        $pageHeading = $title_for_layout = 'Pull List';
        $this->set(compact('title_for_layout', 'pageHeading'));

        $pullList = $this->User->Order->fetchWarehouseOrders();
        $replenishmentList = $this->Replenishment->fetchReplenishmentList();
		
        $this->set(compact('title_for_layout', 'pageHeading', 'pullList', 'replenishmentList'));
    }
	
}
