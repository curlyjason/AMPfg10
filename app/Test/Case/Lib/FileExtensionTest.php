<?php
App::uses('FileExtension', 'Lib');
/**
 * Description of FileExtensionTest
 *
 * @author dondrake
 */
class FileExtensionTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {

    }

    public function testBar() {

    }
	
	/**
	 * 
	 */
	public function testHasExtension()
	{
		$this->assertTrue(is_array(FileExtension::hasExtension('someName.pdf')),
				'did not correctly detect .pdf');
		
		$this->assertTrue(is_array(FileExtension::hasExtension('someName.html')),
				'did not correctly detect .html');
		
		$this->assertFalse(FileExtension::hasExtension('someName', 
				'did not correctly detect lack of extension'));
	}
	
	public function testStripExtension()
	{
		$this->assertTrue('someName' === FileExtension::stripExtension('someName.pdf'),
				'did not correctly remove .pdf');
		
		$this->assertTrue('someName' === FileExtension::stripExtension('someName.html'),
				'did not correctly remove .html');
		
		$this->assertTrue('someName' === FileExtension::stripExtension('someName', 
				'did not correctly pass through string that lacked an extension'));
	}
	
	public function testGetExtension()
	{
		$this->assertTrue('.pdf' === FileExtension::getExtension('someName.pdf'),
				'did not return \'.pdf\' with default dot=True' );
		
		$this->assertTrue('pdf' === FileExtension::getExtension('someName.pdf', FALSE),
				'did not return \'pdf\' on dot=false');
		
		$this->assertTrue('.html' === FileExtension::getExtension('someName.html'),
				'did not return .html extension');
		
		$this->assertTrue('' === FileExtension::getExtension('someName', 
				'did not empt as extention of string that lacked an extension'));
	}
	
	public function testIsPdf()
	{
		$this->assertTrue(FileExtension::isPdf('someName.pdf'),
				'did not correctly detect .pdf');
		
		$this->assertFalse(FileExtension::isPdf('someName.html'),
				'did not correctly detect lack of .pdf when another extension existed');
		
		$this->assertFalse(FileExtension::isPdf('someName', 
				'did not correctly detect lack of .pdf when string lacked extension'));
	}
	
}

