<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

App::uses('AppModel', 'Model');

/**
 * CakePHP UserObservers
 * @author dondrake
 */
class WatchBase extends AppModel {
	
	public $useTable = 'users';

// <editor-fold defaultstate="collapsed" desc="Associations">

    /**
     * hasMany associations
     *
     * @var array
     */
    public $hasMany = array(
        'WatchPoints' => array(
            'className' => 'Observer',
            'foreignKey' => 'user_observer_id',
            'dependent' => false,
            'conditions' => '',
            'fields' => array('WatchPoints.id', 'WatchPoints.user_id AS point_id', 'WatchPoints.user_name AS name', 'WatchPoints.type'),
            'order' => '',
            'limit' => '',
            'offset' => '',
            'exclusive' => '',
            'finderQuery' => '',
            'counterQuery' => ''
        ),
        'Watcher' => array(
            'className' => 'Observer',
            'foreignKey' => 'user_id',
            'dependent' => false,
            'conditions' => '',
            'fields' => array('Watcher.id', 'Watcher.user_observer_id AS watcher_id', 'Watcher.observer_name AS name', 'Watcher.type', 'Watcher.location'),
            'order' => 'Watcher.type',
            'limit' => '',
            'offset' => '',
            'exclusive' => '',
            'finderQuery' => '',
            'counterQuery' => ''
 			)
    );
	 // </editor-fold>
	
	public function watchPointsFor($id){
		$result = $this->find('first', array(
			'conditions' => array('id' => $id),
			'contain' => 'WatchPoints'
		));
		return $result;
//		$this->ddd($result); //die;
	}
	
	public function watchersOf($id) {
		$result = $this->find('first', array(
			'conditions' => array('WatchBase.id' => $id),
			'contain' => array(
				'Watcher'
			)
		));
		return $result;
//		$this->ddd($result); //die;		
	}
	
	public function getUserData($user_id) {
		return $this->find('first', array('conditions' => array('id' => $user_id)));
	}
	
	}
