<?php
App::uses('AppModel', 'Model');
/**
 * TaxRate Model
 *
 */
class TaxRate extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'tax_jurisdiction' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'tax_rate' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);
    
    public $displayField = 'tax_jurisdiction';


    /**
     *
     * @param type $id
     * @param type $table
     * @param type $ds
     */
    function __construct($id = false, $table = null, $ds = null) {
        parent::__construct($id, $table, $ds);
//        $this->virtualFields['name'] = $this->discoverName($id);
        $this->virtualFields['tax_percent'] = "{$this->alias}.tax_rate / 100";
    }
    
    public function getTaxJurisdictionList() {
        return $this->find('list');
    }
	
	public function getTaxRate($city, $state){
		if($state == 'CA'){
			//go fetch city-based tax rates for standard source
			return array(
				'tax_jurisdiction' => 'EX',
				'tax_rate' => 0.0875
			);
		}else{
			return array(
				'tax_jurisdiction' => 'OS',
				'tax_rate' => 0
			);
		}
	}
}