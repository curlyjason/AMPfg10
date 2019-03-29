<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * CakePHP Component
 * @author jasont
 */
class inventoryComponentComponent extends Object {

    public $components = array();
    public $settings = array();

    function initialize(&$controller, $settings) {
        $this->controller = $controller;
        $this->settings = $settings;
    }

    function startup(&$controller) {
        
    }

    function beforeRender() {
        
    }

    function beforeRedirect() {
        
    }

    function shutDown(&$controller) {
        
    }

    function adjust(&$controller, $item, $amount) {
        
    }

    function allocate(&$controller, $item, $order) {
        
    }

    function transfer(&$controller, $item, $destination, $amount) {
        
    }

}
