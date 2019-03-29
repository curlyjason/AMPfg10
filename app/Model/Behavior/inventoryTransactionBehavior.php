<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * CakePHP Behavior
 * @author jasont
 */
class inventoryTransactionBehaviorBehavior extends ModelBehavior {

    public $settings = array();

    public function setup(&$model, $config = array()) {
        $this->settings[$model->alias] = $config;
    }

    public function cleanup(&$model) {
        parent::cleanup($model);
    }

    public function beforeFind(&$model, $query) {
        
    }

    public function afterFind(&$model, $results, $primary) {
        
    }

    public function beforeValidate(&$model) {
        
    }

    public function beforeSave(&$model) {
        
    }

    public function afterSave(&$model, $created, $options = []) {
        
    }

    public function beforeDelete(&$model, $cascade = true) {
        
    }

    public function afterDelete(&$model) {
        
    }

    public function onError(&$model, $error) {
        
    }

    private function adjust(&$model, $item, $amount) {
        
    }

    private function allocate(&$model, $item, $order) {
        
    }

    private function transfer(&$model, $item, $destination, $amount) {
        
    }

}
