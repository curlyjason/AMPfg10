<?php
/**
 * Robot Transaction Refinement Layer
 *
 * Class including all common Xml methods
 * for Xml variants of the Robot Template Pattern 
 * which manages automated input/output requests
 *
 * PHP 5
 *
 * @package       Robot.Xml
 * @author jasont
 */

App::uses('RobotIO', 'Controller/Template');

/**
 * Robot Transaction Refinement Layer
 *
 * Class including all common Xml methods
 * for Xml variants of the Robot Template Pattern 
 * which manages automated input/output requests
 *
 * PHP 5
 *
 * @package       Robot.Xml
 * @author jasont
 */
abstract class XmlRobotIO extends RobotIO {
	
	protected $xsd;

	/**
	 * Read data from the provide xml into an array that will be saved
	 * 
	 * Using xmlTemplates, filter and read in the intial
	 * values from the user's XML submission
	 * 
     * @return boolean
	 */
	public function migrateRequest() {
		libxml_use_internal_errors(true);
		if(!$this->input = Xml::build($this->request->data[0], array('return' => 'domdocument'))){
			$errors = libxml_get_errors();
			throw new RobotProcessException($this->xmlError($errors[0]->message));
		}
		if(!$this->input->schemaValidate(WWW_ROOT . '/files/' . $this->xsd)){
			$errors = libxml_get_errors();
			throw new RobotProcessException($this->xmlError($errors[0]->message));
		}
		$t = Xml::toArray($this->input);
		$this->{$this->model}->data = $t['Body'];

		//Setup RobotCredential object with proper company data
        $this->RobotCredential->setCredential($this->{$this->model}->data['Credentials']['company'], $this->{$this->model}->data['Credentials']['token'], $this->mode);
		return TRUE;
	}
	
}