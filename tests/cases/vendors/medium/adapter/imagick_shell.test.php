<?php
App::import('Vendor','Media.ImageMedium', array('file' => 'medium'.DS.'image.php'));
App::import('Vendor','Media.DocumentMedium', array('file' => 'medium'.DS.'document.php'));
App::import('Vendor','ImagickShellMediumAdapter', array('file' => 'medium'.DS.'adapter'.DS.'imagick_shell.php'));
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';

class TestImagickShellImageMedium extends ImageMedium {
	var $adapters = array('ImagickShell');
}

class TestImagickShellDocumentMedium extends DocumentMedium {
	var $adapters = array('ImagickShell');

}

class ImagickShellMediumAdapterTest extends CakeTestCase {
	function start() {
		parent::start();
		$this->TestData = new MediumTestData();
	}
	
	function end() {
		parent::end();
		$this->TestData->flushFiles();
	}
		
	function skip()	{
		exec('which convert', $output, $return);
		$this->skipUnless($return === 0, 'convert command not available');
	}
	
	function showImage($string, $mimeType = null) {
		echo '<img src="data:'.$mimeType.';base64,'.base64_encode($string).'" />';
	}
	
	function testBasic() {
		$result = new TestImagickShellImageMedium($this->TestData->getFile('image-jpg.jpg'));
		$this->assertIsA($result, 'object');
		
		$Medium = new TestImagickShellImageMedium($this->TestData->getFile('image-jpg.jpg'));
		$result = $Medium->toString();
		$this->assertTrue(!empty($result));
	}
	
	function testInformation() {
		$Medium = new TestImagickShellImageMedium($this->TestData->getFile('image-jpg.jpg'));
		
		$result = $Medium->width();
		$this->assertEqual($result, 70);
		
		$result = $Medium->height();
		$this->assertEqual($result, 47);		
	}
	
	function testManipulation() {
		$Medium = new TestImagickShellImageMedium($this->TestData->getFile('image-jpg.jpg'));
		$Medium->fit(10,10);
		$this->assertTrue($Medium->width() <= 10);
		$this->assertTrue($Medium->height() <= 10);

		$Medium = new TestImagickShellImageMedium($this->TestData->getFile('image-jpg.jpg'));
		$Medium->convert('image/png');
		$result = $Medium->mimeType;
		$this->assertTrue($result, 'image/png');
	}
	
	function testTransitions() {
		$Medium = new DocumentMedium($this->TestData->getFile('application-pdf.pdf'));
		$Medium->Adapters->detach(array_diff($Medium->adapters, array('ImagickShell')));

		$Medium = $Medium->convert('image/png');
		$Medium->Adapters->detach(array_diff($Medium->adapters, array('ImagickShell')));
		$this->assertIsA($Medium, 'ImageMedium');
		
		$tmpFile = $Medium->store(TMP . uniqid('test_suite_'));
		$this->assertEqual(MimeType::guessType($tmpFile), 'image/png');
		unlink($tmpFile);
		
		$Medium = new DocumentMedium($this->TestData->getFile('application-pdf.pdf'));
		$Medium->Adapters->detach(array_diff($Medium->adapters, array('ImagickShell')));
		$Medium = $Medium->convert('image/png');
		$Medium->Adapters->detach(array_diff($Medium->adapters, array('ImagickShell')));
		$result = $Medium->fit(10, 10);
		$this->assertTrue($result);
		$this->assertTrue($Medium->width() <= 10);
		$this->assertTrue($Medium->height() <= 10);		
	}	
	
	function testMake() {
		$instructions = array('convert' => 'image/png','zoomCrop' => array(10, 10));
		$Medium = TestImagickShellImageMedium::make($this->TestData->getFile('image-jpg.jpg'), $instructions);
		$this->assertIsA($Medium, 'Medium');
//		$this->showImage($Medium->toString(),'image/jpg');
	}	
		
}
?>