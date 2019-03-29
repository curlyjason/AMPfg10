<?php
/**
 * Created by PhpStorm.
 * User: jasont
 * Date: 2019-02-14
 * Time: 12:55
 */

App::uses('CakeException', 'Cake/Error');

class RobotProcessException extends CakeException
{
    public function __construct($message, $code = 510) {
//        $Context->set('response', $message);
//        $Context->render('/Common/output');
//        echo $message;
//        exit();

        parent::__construct($message, $code);
    }

}