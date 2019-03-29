<?php

App::uses('AppModel', 'Model');

/**
 * CakePHP BaseDoc
 * @author jasont
 */
class BaseDoc extends AppModel {


	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Order' => array(
			'className' => 'Order',
			'foreignKey' => 'order_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
	
	public $useTable = 'documents';
	public $actsAs = array(
		'Upload.Upload' => array(
			'img_file' => array(
				'extensions' => array('pdf', 'doc', 'docx', 'indd', 'xls', 'txt', 'xlsx'),
				'fields' => array(
					'dir' => 'dir'
				),
				'path' => '{ROOT}webroot{DS}doc{DS}'
			)
		)
	);
	
	public function beforeSave($options = array()) {
		if(isset($this->data['Document']['img_file']) && $this->data['Document']['img_file'] != ''){
			$a = explode('.', $this->data['Document']['img_file']);
			$e = $a[(count($a)-1)];
			$n = preg_replace('/\W/', '_', $this->data['Document']['img_file']);
			$this->data['Document']['img_file'] = $n . '.' . $e;
		}
		parent::beforeSave($options);
	}

}
