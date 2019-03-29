<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * CakePHP UserHelper
 * @author dondrake
 */
class UserHelper extends AppHelper {

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
    
    public function membershipLinks(){
	return 'The Ancestor Path for this User';
    }

}
