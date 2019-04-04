<?php
App::uses('Controller', 'Controller');
App::uses('View', 'View');
App::uses('ProgressHelper', 'View/Helper');

/**
 * Description of DocumentsControllerTest
 *
 * @author dondrake
 */
class DocumentsControllerTest extends ControllerTestCase
{
    public function setUp() {
		
    }

    public function testBar() {

    }
	
	public function testSendFile()
	{
		$result = $this->testAction('/documents/sendFile/abcdef');
		debug($result);
	}
	
}

