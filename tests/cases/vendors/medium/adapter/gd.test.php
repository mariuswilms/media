<?php
App::import('Vendor','Media.ImageMedium', array('file' => 'medium'.DS.'image.php'));
App::import('Vendor','GdMediumAdapter', array('file' => 'medium'.DS.'adapter'.DS.'gd.php'));
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';

class TestGdImageMedium extends ImageMedium {
	var $adapters = array('Gd');
}

class GdMediumAdapterTest extends CakeTestCase {
	function start() {
		parent::start();
		$this->TestData = new MediumTestData();
	}
	
	function end() {
		parent::end();
		$this->TestData->flushFiles();
	}
		
	function skip()
	{
		$this->skipUnless(extension_loaded('gd'), 'GD extension not loaded');
	}
	
	function showImage($string, $mimeType = null) {
		echo '<img src="data:'.$mimeType.';base64,'.base64_encode($string).'" />';
	}
	
	function testBasic() {
		$result = new TestGdImageMedium($this->TestData->getFile('image-jpg.jpg'));
		$this->assertIsA($result, 'object');
		
		$Medium = new TestGdImageMedium($this->TestData->getFile('image-jpg.jpg'));
		$result = $Medium->toString();
		$this->assertTrue(!empty($result));
	}
	
	function testInformation() {
		$Medium = new TestGdImageMedium($this->TestData->getFile('image-jpg.jpg'));
		
		$result = $Medium->width();
		$this->assertEqual($result, 70);
		
		$result = $Medium->height();
		$this->assertEqual($result, 47);		
	}
	
	function testManipulation() {
		$Medium = new TestGdImageMedium($this->TestData->getFile('image-jpg.jpg'));
		$Medium->fit(10,10);
		$this->assertTrue($Medium->width() <= 10);
		$this->assertTrue($Medium->height() <= 10);

		$Medium = new TestGdImageMedium($this->TestData->getFile('image-jpg.jpg'));
		$Medium->convert('image/png');
		$result = $Medium->mimeType;
		$this->assertTrue($result, 'image/png');
	}
	
	function testMake() {
		$instructions = array('convert' => 'image/png','zoomCrop' => array(10, 10));
		$Medium = TestGdImageMedium::make($this->TestData->getFile('image-jpg.jpg'), $instructions);
		$this->assertIsA($Medium, 'Medium');
//		$this->showImage($Medium->toString(),'image/jpg');
	}	
		
}
?>