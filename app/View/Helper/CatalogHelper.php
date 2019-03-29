<?php
App::uses('AppHelper', 'Helper');

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * CakePHP Helper
 * @author dondrake
 */
class CatalogHelper extends AppHelper {

    public $helpers = array();

    public function __construct(View $View, $settings = array()) {
	parent::__construct($View, $settings);
    }

    public function beforeRender($viewFile) {
	
    }

    public function afterRender($viewFile) {
	
    }

    public function beforeLayout($viewLayout) {
	
    }

    public function afterLayout($viewLayout) {
	
    }
    
    /**
     * 
     * @todo Will this actuall return links? or just labels? what would the links go to?
     * @return string
     */
    function membershipLinks(){
	return 'This will be a list of catalog nodes that \'hasOne\' this Item';
    }

}
