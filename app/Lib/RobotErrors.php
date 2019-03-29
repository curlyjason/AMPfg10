<?php
/**
 * Created by PhpStorm.
 * User: jasont
 * Date: 2019-02-14
 * Time: 12:30
 */

class RobotErrors
{

    public $errors =
        [
            1001 => 'malformed request',
            1002 => 'invalid XML structure',
            1003 => 'invalid user credentials',
            2001 => 'non-unique order reference',
            2002 => 'item not valid',
            2003 => 'item out of stock',
            2004 => 'order request must have an order reference',
            2005 => 'quantity not valid',
            //status errors
            3001 => 'order number or order reference not valid',
            //database failures
            5001 => 'save failed',
			// order = []
			9999 => 'request must include at least one order',
			// OrderItems = []
			9999 => 'request must include at least one order item',
        ];

    public $error_count = 0;


    /**
     * Return a properly formatted error message based upon provided code
     *
     * @param $error_code
     * @return string
     */
    public function message($error_code)
    {
        $this->error_count++;
        if(array_key_exists($error_code, $this->errors)){
            return $this->errors[$error_code];
        } else {
            return 'unknown error';
        }
    }

    /**
     * @return int
     */
    public function getErrorCount()
    {
        return $this->error_count;
    }

}