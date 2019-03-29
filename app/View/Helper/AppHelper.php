<?php
/**
 * Application level View Helper
 *
 * This file is application-wide helper file. You can put all
 * application-wide helper-related methods here.
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
 * @package       app.View.Helper
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('Helper', 'View');
App::uses('Logger', 'Lib/Trait');
/**
 * Application helper
 *
 * Add your application-wide methods in the class below, your helpers
 * will inherit them.
 *
 * @package       app.View.Helper
 */
class AppHelper extends Helper {

	/**
     * The permission flag for backorder tool diplay
     *
     * @var boolean $backorderTool
     */
    public $backorderTool = FALSE;
	

    
    public $validationMessage = '';
    
    /**
     * Write the hash value to prevent id tampering
     * 
     * Also defined in AppController
     * Create a hash value from a record id and user/session specific
     * values. The id and hash can be sent to the client, then on
     * return to the server we can verify the id has not been altered
     * 
     * @param string $id The record id to secure
     * @return string The secure has to use as a verification value
     */
    public function secureHash($id){
	return sha1($id . AuthComponent::user('id') . AuthComponent::$sessionKey . Configure::read('Security.salt'));
    }
    
    public function ddd($dbg, $title = false){
	$ggr = Debugger::trace();
	$line = preg_split('/[\r*|\n*]/', $ggr);
	$togKey = sha1($line[1]);
	if ($title) {
	    echo $this->div('cake-debug-output', 
		    $this->tag('h6', $title, array('class' => 'cake-debug'))
		    . $this->para('toggle', $line[1], array('id' => $togKey))
		    . $this->tag('pre', $ggr, array('class' => "$togKey hide")));
	} else {
	    echo $this->div('cake-debug-output', 
		    $this->para('toggle', $line[1], array('id' => $togKey))
		    . $this->tag('pre', $ggr, array('class' => "$togKey hide")));
	}
	debug($dbg);
    }
    
    /**
     * Central decision point for determining if editing is allowed
     * 
     * Used as a check/filter for tool output
     * and input vs data rendering options
     * 
     * @param type $type
     * @return boolean
     */
    public function permitEditing($type) {
		$group = $this->Session->read('Auth.User.group');
		$access = $this->Session->read('Auth.User.access');
		$userId = $this->Session->read('Auth.User.id');
		$role = $this->Session->read('Auth.User.role');
		
		if ($access == 'Guest') {
			return FALSE;
		}
		
		$this->backorderTool = FALSE;
		
		switch ($type) {
			case 'WarehouseReplenishment':
				break;
			case 'WarehouseOrder':
				break;
			case 'ReplenishmentItem':
				if (in_array($role, array('Staff Manager', 'Admins Manager', 'Warehouses Manager')) && $this->status == 'Open'){
					return TRUE;
				}
				//default
				return FALSE;
			break;
			case 'OrderItem':
				//check status
				if (!in_array($this->status, array('Approved', 'Submitted', 'Backordered', 'Released'))) {
					return FALSE;
				}

				//admin & staff managers check
				if($group == 'Admins' || $role == 'Staff Manager'){
					return TRUE;
				}
				
				//client manager and submitted
				if(($this->status == 'Submitted' || $this->status == 'Backordered') && $role == 'Clients Manager'){
					return TRUE;
				}
				
				//buyer and your order
				if(($this->status == 'Submitted' || $this->status == 'Backordered') && $userId == $this->orderOwner){
					return TRUE;
				}
				
				//default
				return FALSE;
			break;
			case 'OrderTools':
				//no tools on Archived status
				if ($this->status == 'Archived') {
					return FALSE;
				}
				
				//establish backorder tool permissions
				if(in_array($this->status, array('Backordered', 'Submitted', 'Approved'))){
					$this->backorderTool = TRUE;
					if($access == 'Buyer'){
						$this->backorderTool = FALSE;
					} else if($this->status == 'Approved' && $role == 'Clients Manager') {
						$this->backorderTool = FALSE;						
					}
				}

				//admin check
				if($group == 'Admins'){
					return TRUE;
				}
				
				//staff manager and NOT released or pulled
				if(!in_array($this->status, array('Released', 'Pulled')) && $role == 'Staff Manager'){
					return TRUE;
				}
				
				//client manager and submitted
				if($this->status == 'Submitted' && $role == 'Clients Manager'){
					return TRUE;
				}

				//default
				return FALSE;
			break;
			case 'ReplenishmentTools':
				if (in_array($role, array('Staff Manager', 'Admins Manager', 'Warehouses Manager')) && in_array($this->status, array('Open', 'Completed'))){
					return TRUE;
				}
				//default
				return FALSE;
			break;
			default:
			break;
		}
    }

	/**
	 * 
	 * @param type $itemGroup
	 * @param type $itemAccess
	 * @param type $group
	 * @param type $access
	 * @return boolean
	 */
    function checkMenuAccess($itemGroup, $itemAccess, $group, $access) {
        //setup group and access as local variables
        //Allow all for Admins Manager
        if ($group == 'Admins') {
            return true;
        }
		//Restrict access to admin level menus
		if ($group != 'Admins' && $itemGroup == 'Admins'){
			return false;
		}
		//Restrict warehouse menus
		if($itemGroup == 'Warehouses' && $group != 'Warehouses'){
			return false;
		}
		//Allow Manager Access
		if($access == 'Manager' && $group == 'Staff'){
			return true;
		}
        //Access for Buyer either client or staff
        if ($access == 'Buyer' && $itemAccess != 'Manager') {
            return true;
        }
        //Access for Guest either client or staff
        if ($access == 'Guest' && $itemAccess == 'Guest') {
            return true;
        }
        //Access for Warehouse
        if (($group == 'Warehouses' && $itemAccess == 'Guest') || ($group == 'Warehouses' && $itemGroup == 'Warehouses')) {
            return true;
        }
        //Access for Client Managers
        if ($access == 'Manager' && $group == 'Clients' && $itemGroup == 'Clients') {
            return true;
        }
        //Default
        return false;
    }

    function populateController($group, $controller, $itemAccess) {
        if ($itemAccess == 'status') {
            return strtolower($group);
        }
        if ($itemAccess == '#') {
            return $controller;
        }
    }
	
}
