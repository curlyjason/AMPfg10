<?php

/**
 * Description of FileExtensionTest
 *
 * @author dondrake
 */
class FileExtensionTest extends CakeTestCase
{
    public function setUp() {

    }

    public function testBar() {

    }
	
	/**
	 * @dataProvider hasExtTrueBooleans
	 */
	public function testHasExtension($haystack, $message)
	{
		$this->assertTrue(FileExtension::hasExtension($haystack), $message);
	}
	
	public function hasExtTrueBooleans()
	{
		return [
//			'someName', FALSE, 'did not correctly detect lack of extension',
			'someName.pdf', 'did not correctly detect .pdf',
			'someName.html', 'did not correctly detect .html',
		];
	}
	
	public function testStripExtension()
	{
		
	}
	
	public function testGetExtension()
	{
		
	}
	
	public function testIsPdf()
	{
		
	}
	
}

